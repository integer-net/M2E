<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsResponser
{
    protected $logActionId = NULL;
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
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
            Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX
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

    //--------------------------------------------

    private function processSucceededResponseData($receivedItems, $nextPart)
    {
        //----------------------
        $tempItems = array();
        foreach ($receivedItems as $receivedItem) {
            if (empty($receivedItem['identifiers']['sku'])) {
                continue;
            }
            $tempItems[$receivedItem['identifiers']['sku']] = $receivedItem;
        }
        $receivedItems = $tempItems;
        unset($tempItems);
        //----------------------

        try {

            $this->updateReceivedListingsProducts($receivedItems);
            $this->updateNotReceivedListingsProducts($receivedItems,$nextPart);

            is_null($nextPart) && $this->resetIgnoreNextInventorySynch();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    // ########################################

    protected function updateReceivedListingsProducts($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            if ((int)$existingItem['ignore_next_inventory_synch'] == 1) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'general_id' => (string)$receivedItem['identifiers']['general_id'],
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

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            if ($newData['status'] != $existingItem['status'] ||
                $newData['online_qty'] != $existingItem['online_qty']) {
                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $existingItem['product_id'], Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
                );
            }

            if ($newData['status'] != $existingItem['status']) {

                $tempLogMessage = '';
                switch ($newData['status']) {
                    case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
                        // Parser hack ->__('Item status was successfully changed to "Unknown".');
                        $tempLogMessage = 'Item status was successfully changed to "Unknown".';
                        break;
                    case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                        // Parser hack ->__('Item status was successfully changed to "Active".');
                        $tempLogMessage = 'Item status was successfully changed to "Active".';
                        break;
                    case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                        // Parser hack ->__('Item status was successfully changed to "Inactive".');
                        $tempLogMessage = 'Item status was successfully changed to "Inactive".';
                        break;
                }

                $tempLog->addProductMessage(
                    $existingItem['listing_id'],
                    $existingItem['product_id'],
                    $existingItem['listing_product_id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $listingProductObj = Mage::helper('M2ePro/Component_Amazon')
                                    ->getObject('Listing_Product',(int)$existingItem['listing_product_id']);

            $listingProductObj->addData($newData)->save();
        }
    }

    protected function resetIgnoreNextInventorySynch()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $dbSelect = $connWrite->select();
        $dbSelect->from(array('lp' => $listingProductTable), array());
        $dbSelect->join(array('l' => $listingTable), 'lp.listing_id = l.id', array());
        $dbSelect->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $dbSelect->where('lp.component_mode = ?',Ess_M2ePro_Helper_Component_Amazon::NICK);

        $dbSelect->reset(Zend_Db_Select::COLUMNS)->columns(array('lp.id'));

        $listingProductTable = Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable();

        $bind = array(
            'ignore_next_inventory_synch' => 0
        );
        $where = new Zend_Db_Expr('`listing_product_id` IN ('.$dbSelect->__toString().')');

        $connWrite->update($listingProductTable,$bind,$where);
    }

    protected function updateNotReceivedListingsProducts($receivedItems,$nextPart)
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

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductMainTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->joinLeft(array('api' => $tempTable),
                    '`second_table`.sku = `api`.sku AND `api`.`hash` = \''.$this->hash.'\'', array('sku'));
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('second_table.ignore_next_inventory_synch != ?',1);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);
        $collection->getSelect()->having('`api`.sku IS NULL');

        $tempColumns = array('main_table.id','main_table.status','main_table.listing_id',
                             'main_table.product_id','api.sku');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $notReceivedIds = array();
        while ($notReceivedItem = $stmtTemp->fetch()) {

            if (!in_array((int)$notReceivedItem['id'],$notReceivedIds)) {
                $tempLog->addProductMessage(
                    $notReceivedItem['listing_id'],
                    $notReceivedItem['product_id'],
                    $notReceivedItem['id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    // Parser hack ->__('Item status was successfully changed to "Inactive (Blocked)".');
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
            $connWrite->update($listingProductMainTable,$bind,$where);
        }
    }

    // ########################################

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where("`second_table`.`sku` is not null and `second_table`.`sku` != ''");

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array('main_table.listing_id',
                                 'main_table.product_id','main_table.status',
                                 'second_table.sku','second_table.general_id',
                                 'second_table.online_price','second_table.online_qty',
                                 'second_table.start_date','second_table.is_afn_channel',
                                 'second_table.is_isbn_general_id','second_table.listing_product_id',
                                 'second_table.ignore_next_inventory_synch');
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

        return $this->logActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
    }

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS);

        return $this->synchronizationLog;
    }

    // ########################################
}