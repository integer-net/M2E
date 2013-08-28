<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Updating
{
    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    protected $logActionId = NULL;

    // ########################################

    public function initialize(Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
    }

    // ########################################

    public function processResponseData($responseData)
    {
        $this->updateToTimeLastSynchronization($responseData);

        if (!isset($responseData['items']) || !is_array($responseData['items']) ||
            count($responseData['items']) <= 0) {
            return;
        }

        $responseData['items'] = $this->filterReceivedOnlyOtherListings($responseData['items']);
        $responseData['items'] = $this->filterReceivedOnlyCurrentOtherListings($responseData['items']);

        /** @var $logModel Ess_M2ePro_Model_Listing_Other_Log */
        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');

        foreach ($responseData['items'] as $receivedItem) {

            $existObject = Mage::getModel('M2ePro/Ebay_Listing_Other')
                                        ->load($receivedItem['id'], 'item_id');

            $existsId = $existObject->getId();

            $newData = array(
                'title' => (string)$receivedItem['title'],
                'currency' => (string)$receivedItem['currency'],
                'online_price' => (float)$receivedItem['currentPrice'],
                'online_qty' => (int)$receivedItem['quantity'],
                'online_qty_sold' => (int)$receivedItem['quantitySold'],
                'online_bids' => (int)$receivedItem['bidCount'],
                'start_date' => (string)Mage::helper('M2ePro')->getDate($receivedItem['startTime']),
                'end_date' => (string)Mage::helper('M2ePro')->getDate($receivedItem['endTime'])
            );

            if (isset($receivedItem['sku'])) {

                if ($receivedItem['sku'] == '') {
                    $newData['sku'] = new Zend_Db_Expr("''");
                } else {
                    $newData['sku'] = (string)$receivedItem['sku'];
                }
            }

            if ($existsId) {
                $newData['id'] = $existsId;
            } else {
                $newData['item_id'] = (double)$receivedItem['id'];
                $newData['account_id'] = (int)$this->getAccount()->getId();
                $newData['marketplace_id'] = (int)Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Marketplace',
                                      $receivedItem['marketplace'],
                                      'code')
                    ->getId();
            }

            $tempListingType = Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_AUCTION;
            if ($receivedItem['listingType'] == $tempListingType) {
                $newData['online_qty'] = 1;
            }

            if (($receivedItem['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
                 $receivedItem['listingStatus'] == self::EBAY_STATUS_ENDED) &&
                 $newData['online_qty'] == $newData['online_qty_sold']) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_ENDED) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            }

            if ($existsId) {
                if ($newData['status'] != $existObject->getParentObject()->getStatus('status')) {

                    $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                    $tempLogMessage = '';
                    switch ($newData['status']) {
                        case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                            // Parser hack ->__('Item status was successfully changed to "Listed".');
                            $tempLogMessage = 'Item status was successfully changed to "Listed".';
                            break;
                        case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                            // Parser hack ->__('Item status was successfully changed to "Sold".');
                            $tempLogMessage = 'Item status was successfully changed to "Sold".';
                            break;
                        case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                            // Parser hack ->__('Item status was successfully changed to "Stopped".');
                            $tempLogMessage = 'Item status was successfully changed to "Stopped".';
                            break;
                        case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                            // Parser hack ->__('Item status was successfully changed to "Finished".');
                            $tempLogMessage = 'Item status was successfully changed to "Finished".';
                            break;
                    }

                    $logModel->addProductMessage(
                        (int)$newData['id'],
                        Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                        $this->getLogActionId(),
                        Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );
                }
            } else {
                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
            }

            $listingOtherModel = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

            if (!$existsId) {

                $logModel->addProductMessage($listingOtherModel->getId(),
                     Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                     NULL,
                     Ess_M2ePro_Model_Listing_Other_Log::ACTION_ADD_LISTING,
                     // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully added');
                     'Item was successfully added',
                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);

                if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
                    continue;
                }

                $mappingModel->initialize($this->getAccount());
                $mappingModel->autoMapOtherListingProduct($listingOtherModel);
            }
        }
        //---------------------------
    }

    // ########################################

    protected function updateToTimeLastSynchronization($responseData)
    {
        $tempToTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        if (isset($responseData['to_time'])) {
            if (is_array($responseData['to_time'])) {
                $tempToTime = array();
                foreach ($responseData['to_time'] as $tempToTime2) {
                    $tempToTime[] = strtotime($tempToTime2);
                }
                sort($tempToTime,SORT_NUMERIC);
                $tempToTime = array_pop($tempToTime);
                $tempToTime = date('Y-m-d H:i:s',$tempToTime);
            } else {
                $tempToTime = $responseData['to_time'];
            }
        }

        if (!is_string($tempToTime) || empty($tempToTime)) {
            $tempToTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        $childAccountObject = $this->getAccount()->getChildObject();
        $childAccountObject->setData('other_listings_last_synchronization', $tempToTime)->save();
    }

    //-----------------------------------------

    protected function filterReceivedOnlyOtherListings(array $receivedItems)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $resultItems = array();

        foreach (array_chunk($receivedItems,500) as $partReceivedItems) {

            if (count($partReceivedItems) <= 0) {
                continue;
            }

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::getModel('M2ePro/Ebay_Item')->getCollection();
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('item_id'));

            $method = 'where';
            $partReceivedItemsByItemId = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $collection->getSelect()->$method('item_id = ?',(string)$partReceivedItem['id']);
                $partReceivedItemsByItemId[(string)$partReceivedItem['id']] = $partReceivedItem;
                $method = 'orWhere';
            }

            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

            while ($existItem = $stmtTemp->fetch()) {
                unset($partReceivedItemsByItemId[(string)$existItem['item_id']]);
            }

            $resultItems = array_merge($resultItems,$partReceivedItemsByItemId);
        }

        return array_values($resultItems);
    }

    protected function filterReceivedOnlyCurrentOtherListings(array $receivedItems)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $resultItems = array();

        foreach (array_chunk($receivedItems,500) as $partReceivedItems) {

            if (count($partReceivedItems) <= 0) {
                continue;
            }

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::getModel('M2ePro/Ebay_Listing_Other')->getCollection();
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('old_items'));

            $method = 'where';
            $partReceivedItemsByItemId = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $collection->getSelect()->$method('old_items LIKE \'%'.(string)$partReceivedItem['id'].'%\'');
                $partReceivedItemsByItemId[(string)$partReceivedItem['id']] = $partReceivedItem;
                $method = 'orWhere';
            }

            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

            while ($existItem = $stmtTemp->fetch()) {
                $oldItems = trim(trim($existItem['old_items'],','));
                $oldItems = !empty($oldItems) ? explode(',',$oldItems) : array();
                foreach ($oldItems as $oldItem) {
                    unset($partReceivedItemsByItemId[(string)$oldItem]);
                }
            }

            $resultItems = array_merge($resultItems,$partReceivedItemsByItemId);
        }

        return array_values($resultItems);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    protected function getLogActionId()
    {
        if (!is_null($this->logActionId)) {
            return $this->logActionId;
        }

        return $this->logActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();
    }

    // ########################################
}