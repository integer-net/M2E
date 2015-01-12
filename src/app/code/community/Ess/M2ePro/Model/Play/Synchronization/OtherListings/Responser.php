<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_OtherListings_Responser
    extends Ess_M2ePro_Model_Connector_Play_Inventory_Get_ItemsResponser
{
    protected $logActionId = NULL;
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Play_Synchronization_OtherListings::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $tempObjects = array(
            $this->getAccount(),
            Mage::helper('M2ePro/Component_Play')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_play',
            Ess_M2ePro_Model_Play_Synchronization_OtherListings::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->deleteObjectLocks($lock, $this->hash);
            }
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_play_processed_inventory');
        $connWrite->delete($tempTable,array('`hash` = ?'=>(string)$this->hash));

        $fail && $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($message),
                                                            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    // ########################################

    protected function processResponseData($response)
    {
        $receivedItems = parent::processResponseData($response);
        $this->processSucceededResponseData($receivedItems['data'],$receivedItems['next_part']);
    }

    // -----------------------------------------

    private function processSucceededResponseData($receivedItems, $nextPart)
    {
        $this->updateFirstSynchronizationTime();

        //--------------------
        $tempItems = array();
        foreach ($receivedItems as $receivedItem) {
            if (empty($receivedItem['sku'])) {
                continue;
            }
            $tempItems[$receivedItem['sku']] = $receivedItem;
        }
        $receivedItems = $tempItems;
        unset($tempItems);
        //--------------------

        $receivedItems = $this->filterReceivedOnlyOtherListings($receivedItems);

        try {

            $this->updateReceivedOtherListings($receivedItems);
            $this->createNotExistedOtherListings($receivedItems);

            $this->updateNotReceivedOtherListings($receivedItems,$nextPart);

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
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'general_id' => (string)$receivedItem['general_id'],
                'general_id_type' => (string)$receivedItem['general_id_type'],
                'play_listing_id' => (int)$receivedItem['play_listing_id'],
                'title' => (string)$receivedItem['title'],
                'dispatch_to' => (string)$receivedItem['dispatch_to'],
                'dispatch_from' => (string)$receivedItem['dispatch_from'],
                'online_price_gbr' => (float)$receivedItem['price_gbr'],
                'online_price_euro' => (float)$receivedItem['price_euro'],
                'online_qty' => (int)$receivedItem['qty'],
                'condition' => (string)$receivedItem['condition'],
                'condition_note' => (string)$receivedItem['condition_note'],
                'start_date' => (string)date('c',strtotime($receivedItem['start_date']))
            );

            if ($newData['online_qty'] > 0) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            } else {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }

            $existingData = array(
                'general_id' => (string)$existingItem['general_id'],
                'general_id_type' => (string)$existingItem['general_id_type'],
                'play_listing_id' => (int)$existingItem['play_listing_id'],
                'title' => (string)$existingItem['title'],
                'dispatch_to' => (string)$existingItem['dispatch_to'],
                'dispatch_from' => (string)$existingItem['dispatch_from'],
                'online_price_gbr' => (float)$existingItem['online_price_gbr'],
                'online_price_euro' => (float)$existingItem['online_price_euro'],
                'online_qty' => (int)$existingItem['online_qty'],
                'condition' => (string)$existingItem['condition'],
                'condition_note' => (string)$existingItem['condition_note'],
                'start_date' => (string)date('c',strtotime($existingItem['start_date'])),
                'status' => (int)$existingItem['status'],
            );

            if ($newData == $existingData) {
                continue;
            }

            if ($newData['online_qty'] > 0) {
                $newData['end_date'] = NULL;
            } else {
                $newData['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
            }

            if ($newData['status'] != $existingData['status']) {

                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                $tempLogMessage = '';
                switch ($newData['status']) {
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

            $listingOtherObj = Mage::helper('M2ePro/Component_Play')
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
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

        /** @var $mappingModel Ess_M2ePro_Model_Play_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Play_Listing_Other_Mapping');

        /** @var $movingModel Ess_M2ePro_Model_Play_Listing_Other_Moving */
        $movingModel = Mage::getModel('M2ePro/Play_Listing_Other_Moving');

        foreach ($receivedItems as $receivedItem) {

            if (isset($receivedItem['founded'])) {
                continue;
            }

            $newData = array(
                'account_id' => (int)$this->params['account_id'],
                'marketplace_id' => Ess_M2ePro_Helper_Component_Play::MARKETPLACE_ID,
                'product_id' => NULL,

                'general_id' => (string)$receivedItem['general_id'],
                'general_id_type' => (string)$receivedItem['general_id_type'],
                'play_listing_id' => (int)$receivedItem['play_listing_id'],
                'sku' => (string)$receivedItem['sku'],

                'title' => (string)$receivedItem['title'],

                'dispatch_to' => (string)$receivedItem['dispatch_to'],
                'dispatch_from' => (string)$receivedItem['dispatch_from'],

                'online_price_gbr' => (float)$receivedItem['price_gbr'],
                'online_price_euro' => (float)$receivedItem['price_euro'],
                'online_qty' => (int)$receivedItem['qty'],

                'condition' => (string)$receivedItem['condition'],
                'condition_note' => (string)$receivedItem['condition_note'],

                'start_date' => (string)$receivedItem['start_date'],
                'end_date' => NULL,
            );

            if ((int)$newData['online_qty'] > 0) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                $newData['end_date'] = NULL;
            } else {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                $newData['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
            }

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            $listingOtherModel = Mage::helper('M2ePro/Component_Play')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

            $logModel->addProductMessage($listingOtherModel->getId(),
                                         Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                         NULL,
                                         Ess_M2ePro_Model_Listing_Other_Log::ACTION_ADD_LISTING,
                                         // M2ePro_TRANSLATIONS
                                         // Item was successfully added'
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
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_play_processed_inventory');

        //--------------------------
        foreach (array_chunk($receivedItems,1000) as $partReceivedItems) {

            $inserts = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $inserts[] = array(
                    'sku' => $partReceivedItem['sku'],
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
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Other');
        $collection->getSelect()->joinLeft(array('api' => $tempTable),
                 '`second_table`.sku = `api`.sku AND `api`.`hash` = \''.$this->hash.'\'', array('sku'));
        $collection->getSelect()->where('`main_table`.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);
        $collection->getSelect()->having('`api`.sku IS NULL');

        $tempColumns = array('main_table.id','main_table.status','api.sku');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

        $notReceivedIds = array();
        while ($notReceivedItem = $stmtTemp->fetch()) {

            if (!in_array((int)$notReceivedItem['id'],$notReceivedIds)) {

                $tempLog->addProductMessage(
                    (int)$notReceivedItem['id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    // M2ePro_TRANSLATIONS
                    // Item status was successfully changed to "Inactive".
                    'Item status was successfully changed to "Inactive".',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }

        $notReceivedIds = array_unique($notReceivedIds);

        foreach (array_chunk($notReceivedIds, 1000) as $partIds) {

            $connWrite->update(
                $listingOtherMainTable,
                array(
                    'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
                    'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
                ),
                '`id` IN ('.implode(',',$partIds).')'
            );
        }
    }

    // ########################################

    protected function filterReceivedOnlyOtherListings(array $receivedItems)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
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
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Other');
        $collection->getSelect()->where('`main_table`.`account_id` = ?',(int)$this->params['account_id']);

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array('main_table.status','second_table.sku','second_table.title',
                                 'second_table.general_id','second_table.general_id_type',
                                 'second_table.play_listing_id',
                                 'second_table.online_price_gbr','second_table.online_price_euro',
                                 'second_table.online_qty','second_table.dispatch_to','second_table.dispatch_from',
                                 'second_table.condition','second_table.condition_note','second_table.start_date',
                                 'second_table.listing_other_id');
        }

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        return $stmtTemp;
    }

    protected function updateFirstSynchronizationTime()
    {
        $childAccountObject = $this->getAccount()->getChildObject();

        $firstSynchronizationTime = $childAccountObject->getOtherListingsFirstSynchronization();

        if (!is_null($firstSynchronizationTime)) {
            return;
        }

        $childAccountObject->setData('other_listings_first_synchronization',
                                     Mage::helper('M2ePro')->getCurrentGmtDate())->save();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
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

        /** @var $logs Ess_M2ePro_Model_Synchronization_Log */
        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->synchronizationLog;
    }

    // ########################################
}