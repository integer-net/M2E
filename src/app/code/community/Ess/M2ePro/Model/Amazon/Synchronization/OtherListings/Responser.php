<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_OtherListings_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsResponser
{
    protected $logActionId = NULL;
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_OtherListings::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $tempObjects = array(
            $this->getAccount(), $this->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_amazon',
            Ess_M2ePro_Model_Amazon_Synchronization_OtherListings::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->deleteObjectLocks($lock, $this->hash);
            }
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_processed_inventory');
        $connWrite->delete($tempTable,array('`hash` = ?'=>(string)$this->hash));

        $fail && $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($message),
                                                            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    // ########################################

    protected function processResponseData($response)
    {
        $receivedItems = parent::processResponseData($response);
        $this->processSucceededResponseData($receivedItems['data'], $receivedItems['next_part']);
    }

    // ---------------------------------------

    private function processSucceededResponseData($receivedItems, $nextPart)
    {
        //--------------------
        $tempItems = array();
        foreach ($receivedItems as $receivedItem) {
            if (empty($receivedItem['identifiers']['sku'])) {
                continue;
            }
            $tempItems[$receivedItem['identifiers']['sku']] = $receivedItem;
        }
        $receivedItems = $tempItems;
        unset($tempItems);
        //--------------------

        $receivedItems = $this->filterReceivedOnlyOtherListings($receivedItems);

        try {

            $this->updateReceivedOtherListings($receivedItems);
            $this->createNotExistedOtherListings($receivedItems);

            $this->updateNotReceivedOtherListings($receivedItems,$this->hash,$nextPart);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    // ########################################

    protected function updateReceivedOtherListings($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'general_id' => (string)$receivedItem['identifiers']['general_id'],
                'title' => (string)$receivedItem['title'],
                'online_price' => (float)$receivedItem['price'],
                'online_qty' => (int)$receivedItem['qty'],
                'start_date' => (string)date('c',strtotime($receivedItem['start_date'])),
                'is_afn_channel' => (bool)$receivedItem['channel']['is_afn'],
                'is_isbn_general_id' => (bool)$receivedItem['identifiers']['is_isbn']
            );

            if ($newData['is_afn_channel']) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
            } else {
                if ($newData['online_qty'] > 0) {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                } else {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                }
            }

            $existingData = array(
                'general_id' => (string)$existingItem['general_id'],
                'title' => (string)$existingItem['title'],
                'online_price' => (float)$existingItem['online_price'],
                'online_qty' => (int)$existingItem['online_qty'],
                'start_date' => (string)date('c',strtotime($existingItem['start_date'])),
                'is_afn_channel' => (bool)$existingItem['is_afn_channel'],
                'is_isbn_general_id' => (bool)$existingItem['is_isbn_general_id'],
                'status' => (int)$existingItem['status']
            );

            if ($newData == $existingData) {
                continue;
            }

            if (!$newData['is_afn_channel']) {
                if ($newData['online_qty'] > 0) {
                    $newData['end_date'] = NULL;
                } else {
                    $newData['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                }
            }

            if ($newData['status'] != $existingData['status']) {

                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                $tempLogMessage = '';
                switch ($newData['status']) {
                    case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
                        // M2ePro_TRANSLATIONS
                        // Item status was successfully changed to "Unknown".
                        $tempLogMessage = 'Item status was successfully changed to "Unknown".';
                        break;
                    case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                        // M2ePro_TRANSLATIONS
                        // Item status was successfully changed to "Active".
                        $tempLogMessage = 'Item status was successfully changed to "Active".';
                        break;
                    case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                        // M2ePro_TRANSLATIONS
                        // Item status was successfully changed to "Inactive".
                        $tempLogMessage = 'Item status was successfully changed to "Inactive".';
                        break;
                }

                $tempLog->addProductMessage(
                    (int)$existingItem['listing_other_id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $listingOtherObj = Mage::helper('M2ePro/Component_Amazon')
                                ->getObject('Listing_Other',(int)$existingItem['listing_other_id']);

            $listingOtherObj->addData($newData)->save();
        }
    }

    protected function createNotExistedOtherListings($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(false);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItems[$existingItem['sku']]['founded'] = true;
        }

        /** @var $logModel Ess_M2ePro_Model_Listing_Other_Log */
        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        /** @var $mappingModel Ess_M2ePro_Model_Amazon_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Amazon_Listing_Other_Mapping');

        /** @var $movingModel Ess_M2ePro_Model_Amazon_Listing_Other_Moving */
        $movingModel = Mage::getModel('M2ePro/Amazon_Listing_Other_Moving');

        foreach ($receivedItems as $receivedItem) {

            if (isset($receivedItem['founded'])) {
                continue;
            }

            $newData = array(
                'account_id' => $this->getAccount()->getId(),
                'marketplace_id' => $this->getMarketplace()->getId(),
                'product_id' => NULL,

                'general_id' => (string)$receivedItem['identifiers']['general_id'],

                'sku' => (string)$receivedItem['identifiers']['sku'],
                'title' => (string)$receivedItem['title'],

                'online_price' => (float)$receivedItem['price'],
                'online_qty' => (int)$receivedItem['qty'],

                'is_afn_channel' => (bool)$receivedItem['channel']['is_afn'],
                'is_isbn_general_id' => (bool)$receivedItem['identifiers']['is_isbn'],

                'start_date' => (string)$receivedItem['start_date'],
                'end_date' => NULL,
            );

            if ((bool)$newData['is_afn_channel']) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
            } else {
                if ((int)$newData['online_qty'] > 0) {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                    $newData['end_date'] = NULL;
                } else {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                    $newData['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                }
            }

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            $listingOtherModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

            $logModel->addProductMessage($listingOtherModel->getId(),
                                         Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                         NULL,
                                         Ess_M2ePro_Model_Listing_Other_Log::ACTION_ADD_LISTING,
                                         // M2ePro_TRANSLATIONS
                                         // Item was successfully added
                                         'Item was successfully added',
                                         Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);

            if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
                continue;
            }

            $mappingModel->initialize($this->getAccount());
            $mappingResult = $mappingModel->autoMapOtherListingProduct($listingOtherModel);

            if ($mappingResult) {

                if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
                    continue;
                }

                $movingModel->initialize($this->getAccount());
                $movingModel->autoMoveOtherListingProduct($listingOtherModel);
            }
        }
    }

    //-----------------------------------------

    protected function updateNotReceivedOtherListings($receivedItems,$nextPart)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_processed_inventory');

        //--------------------------
        $chunckedArray = array_chunk($receivedItems,1000);

        foreach ($chunckedArray as $partReceivedItems) {

            $inserts = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $inserts[] = array(
                    'sku' => $partReceivedItem['identifiers']['sku'],
                    'hash' => $this->hash
                );
            }

            $connWrite->insertMultiple($tempTable, $inserts);
        }
        //--------------------------

        if (!is_null($nextPart)) {
            return;
        }

        $listingOtherMainTable = Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable();

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $collection->getSelect()->joinLeft(array('api' => $tempTable),
                 '`second_table`.sku = `api`.sku AND `api`.`hash` = \''.$this->hash.'\'', array('sku'));
        $collection->getSelect()->where('`main_table`.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);
        $collection->getSelect()->having('`api`.sku IS NULL');

        $tempColumns = array('main_table.id','main_table.status','api.sku');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $notReceivedIds = array();
        while ($notReceivedItem = $stmtTemp->fetch()) {

            if (!in_array((int)$notReceivedItem['id'],$notReceivedIds)) {
                $tempLog->addProductMessage(
                    (int)$notReceivedItem['id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    // M2ePro_TRANSLATIONS
                    // Item status was successfully changed to "Inactive (Blocked)".
                    'Item status was successfully changed to "Inactive (Blocked)".',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }
        $notReceivedIds = array_unique($notReceivedIds);

        $bind = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
        );

        $chunckedIds = array_chunk($notReceivedIds,1000);
        foreach ($chunckedIds as $partIds) {
            $where = '`id` IN ('.implode(',',$partIds).')';
            $connWrite->update($listingOtherMainTable,$bind,$where);
        }
    }

    // ########################################

    protected function filterReceivedOnlyOtherListings(array $receivedItems)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('second_table.sku'));

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        while ($existListingProduct = $stmtTemp->fetch()) {

            if (empty($existListingProduct['sku'])) {
                continue;
            }

            if (isset($receivedItems[$existListingProduct['sku']])) {
                unset($receivedItems[$existListingProduct['sku']]);
            }
        }

        return $receivedItems;
    }

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $collection->getSelect()->where('`main_table`.`account_id` = ?',(int)$this->params['account_id']);

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array('main_table.status',
                                 'second_table.sku','second_table.general_id','second_table.title',
                                 'second_table.online_price','second_table.online_qty',
                                 'second_table.start_date','second_table.is_afn_channel',
                                 'second_table.is_isbn_general_id','second_table.listing_other_id');
        }

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        return $stmtTemp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    //-----------------------------------------

    protected function getLogActionId()
    {
        if (!is_null($this->logActionId)) {
            return $this->logActionId;
        }

        return $this->logActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();
    }

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->synchronizationLog;
    }

    // ########################################
}