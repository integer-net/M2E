<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Defaults_RemoveDuplicates
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    //####################################

    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const MAX_ALLOWED_BLOCKED_PRODUCTS = 100;
    const BLOCKED_PRODUCTS_PER_SYNCH = 10;

    const MIN_MINUTES_FROM_FAILED_REQUEST = 5;

    //####################################

    private $duplicatedItems = array();

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Remove Duplicated Products');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Remove Duplicated Products" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Remove Duplicated Products" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->checkTooManyBlockedListingProducts();
        $this->checkTooManyBlockedListingOthers();

        $this->processListingProducts();
        $this->processListingOther();

        $this->stopDuplicatedItems();
    }

    //####################################

    private function checkTooManyBlockedListingProducts()
    {
        $collection = $this->getBlockedListingProductCollection();
        $blockedCount = $collection->getSize();
        if ($blockedCount <= self::MAX_ALLOWED_BLOCKED_PRODUCTS) {
            return;
        }

        $collection->getSelect()->limit($blockedCount - self::MAX_ALLOWED_BLOCKED_PRODUCTS);

        foreach ($collection->getItems() as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            $additionalData = $product->getAdditionalData();
            if (!empty($additionalData['last_failed_action_data']['previous_status'])) {
                $productStatus = $additionalData['last_failed_action_data']['previous_status'];
            }

            $this->modifyAndLogListingProduct($product, $productStatus);
        }
    }

    private function checkTooManyBlockedListingOthers()
    {
        $collection = $this->getBlockedListingOtherCollection();
        $blockedCount = $collection->getSize();
        if ($blockedCount <= self::MAX_ALLOWED_BLOCKED_PRODUCTS) {
            return;
        }

        $collection->getSelect()->limit($blockedCount - self::MAX_ALLOWED_BLOCKED_PRODUCTS);

        foreach ($collection->getItems() as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Other */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            $additionalData = $product->getAdditionalData();
            if (!empty($additionalData['last_failed_action_data']['previous_status'])) {
                $productStatus = $additionalData['last_failed_action_data']['previous_status'];
            }

            $this->modifyAndLogListingOther($product, $productStatus);
        }
    }

    // ------------------------------------

    private function processListingProducts()
    {
        $collection = $this->getBlockedListingProductCollection();
        $collection->getSelect()->limit(self::BLOCKED_PRODUCTS_PER_SYNCH);

        $products = $collection->getItems();
        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            try {

                $additionalData = $product->getAdditionalData();
                if (empty($additionalData['last_failed_action_data'])) {
                    throw new Exception();
                }

                $lastFailedActionData = $additionalData['last_failed_action_data'];

                $requestTime = new DateTime($lastFailedActionData['request_time']);
                $currentTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate());
                $interval = $requestTime->diff($currentTime);

                if ($interval->format('%i') < self::MIN_MINUTES_FROM_FAILED_REQUEST) {
                    continue;
                }

                $productStatus = (int)$lastFailedActionData['previous_status'];
                $action = (int)$lastFailedActionData['action'];
                $accountId = (int)$product->getData('account_id');
                $marketplaceId = (int)$product->getData('marketplace_id');

                if (!isset($this->duplicatedItems[$accountId])) {
                    $this->duplicatedItems[$accountId] = array();
                }
                if (!isset($this->duplicatedItems[$accountId][$marketplaceId])) {
                    $this->duplicatedItems[$accountId][$marketplaceId] = array();
                }

                if ($action == Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_RELIST) {

                    $itemInfo = $this->getEbayItemInfo(
                        $lastFailedActionData['native_request_data']['item_id'],
                        $accountId
                    );

                    if (empty($itemInfo['relisted_item_id'])) {
                        throw new Exception();
                    }

                    $this->duplicatedItems[$accountId][$marketplaceId][] = $itemInfo['relisted_item_id'];
                    $this->modifyAndLogListingProduct($product, $productStatus, $itemInfo['relisted_item_id']);

                    continue;
                }

                $timeFrom = new DateTime($lastFailedActionData['request_time']);
                $timeTo = new DateTime($lastFailedActionData['request_time']);

                $timeFrom->modify('-1 minute');
                $timeTo->modify('+1 minute');

                $possibleDuplicates = $this->getEbayItemsByStartTimeInterval($timeFrom, $timeTo, $accountId);

                $marketplaceCode = Mage::getModel('M2ePro/Marketplace')
                    ->loadInstance($marketplaceId)
                    ->getCode();
                $duplicatedItem = $this->getDuplicateItemFromPossible($possibleDuplicates, array(
                        'title' => $lastFailedActionData['native_request_data']['title'],
                        'sku' => $lastFailedActionData['native_request_data']['sku'],
                        'marketplace' => $marketplaceCode,
                ));

                if (empty($duplicatedItem)) {
                    throw new Exception();
                }

                $this->duplicatedItems[$accountId][$marketplaceId][] = $duplicatedItem['id'];
                $this->modifyAndLogListingProduct($product, $productStatus, $duplicatedItem['id']);

            } catch(Exception $e) {
                $this->modifyAndLogListingProduct($product, $productStatus);
            }
        }
    }

    private function processListingOther()
    {
        $collection = $this->getBlockedListingOtherCollection();
        $collection->getSelect()->limit(self::BLOCKED_PRODUCTS_PER_SYNCH);

        $products = $collection->getItems();
        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Other */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            try {

                $additionalData = $product->getAdditionalData();
                if (empty($additionalData['last_failed_action_data'])) {
                    throw new Exception();
                }

                $lastFailedActionData = $additionalData['last_failed_action_data'];

                $requestTime = new DateTime($lastFailedActionData['request_time']);
                $currentTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate());
                $interval = $requestTime->diff($currentTime);

                if ($interval->format('%i') < self::MIN_MINUTES_FROM_FAILED_REQUEST) {
                    continue;
                }

                $productStatus = (int)$lastFailedActionData['previous_status'];
                $accountId = (int)$product->getData('account_id');
                $marketplaceId = (int)$product->getData('marketplace_id');

                if (!isset($this->duplicatedItems[$accountId])) {
                    $this->duplicatedItems[$accountId] = array();
                }
                if (!isset($this->duplicatedItems[$accountId][$marketplaceId])) {
                    $this->duplicatedItems[$accountId][$marketplaceId] = array();
                }

                $itemInfo = $this->getEbayItemInfo(
                    $lastFailedActionData['native_request_data']['item_id'],
                    $accountId
                );

                if (empty($itemInfo['relisted_item_id'])) {
                    throw new Exception();
                }

                $this->duplicatedItems[$accountId][$marketplaceId][] = $itemInfo['relisted_item_id'];
                $this->modifyAndLogListingOther($product, $productStatus, $itemInfo['relisted_item_id']);

            } catch(Exception $e) {
                $this->modifyAndLogListingOther($product, $productStatus);
            }
        }
    }

    // ------------------------------------

    private function stopDuplicatedItems()
    {
        if (empty($this->duplicatedItems)) {
            return;
        }

        foreach ($this->duplicatedItems as $accountId => $marketplaceItems) {

            foreach ($marketplaceItems as $marketplaceId => $itemIds) {

                if (empty($itemIds)) {
                    continue;
                }

                $itemsParts = array_chunk(array_unique($itemIds), 10);

                foreach ($itemsParts as $itemsPart) {
                    try {

                        Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                            ->processVirtual('item','update','ends',
                                             array('items'=>$itemsPart),NULL,
                                             $marketplaceId,$accountId,NULL);

                    } catch (Exception $e) {}
                }
            }
        }
    }

    //####################################

    private function getBlockedListingProductCollection()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)
            ->join(
                'Listing', 'Listing.id=main_table.listing_id',
                array('Listing.account_id', 'Listing.marketplace_id')
            );

        return $collection;
    }

    private function getBlockedListingOtherCollection()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Other')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);

        return $collection;
    }

    //####################################

    private function getEbayItemInfo($itemId, $accountId)
    {
        $responseData = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                    ->processVirtual('item','get','info',
                                     array('item_id'=>$itemId),NULL,
                                     NULL,$accountId,NULL);

        return isset($responseData['result']) ? $responseData['result'] : array();
    }

    private function getEbayItemsByStartTimeInterval($timeFrom, $timeTo, $accountId)
    {
        if (is_object($timeFrom)) {
            $timeFrom = $timeFrom->format('Y-m-d H:i:s');
        }
        if (is_object($timeTo)) {
            $timeTo = $timeTo->format('Y-m-d H:i:s');
        }

        $responseData = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                    ->processVirtual('item','get','all',
                                     array('since_time'=>$timeFrom, 'to_time'=>$timeTo),NULL,
                                     NULL,$accountId,NULL);

        return isset($responseData['items']) ? $responseData['items'] : array();
    }

    // ------------------------------------

    private function getDuplicateItemFromPossible(array $possibleDuplicates, array $searchParams)
    {
        if (empty($possibleDuplicates)) {
            return array();
        }

        foreach ($possibleDuplicates as $item) {
            $isFound = true;
            foreach ($searchParams as $key => $value) {
                if (trim($item[$key]) == trim($value)) {
                    continue;
                }

                $isFound = false;
                break;
            }

            if (!$isFound) {
                continue;
            }

            return $item;
        }

        return array();
    }

    //####################################

    private function modifyAndLogListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                $status, $duplicateItemId = null)
    {
        $additionalData = $listingProduct->getAdditionalData();
        unset($additionalData['last_failed_action_data']);

        $listingProduct->addData(array(
            'status' => $status,
            'additional_data' => json_encode($additionalData),
        ))->save();

        /** @var Ess_M2ePro_Model_Listing_Log $logModel */
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logActionId = $logModel->getNextActionId();

        $statusLogMessage = $this->getStatusLogMessage($status);

        Mage::log(print_r($listingProduct, true));

        $logModel->addProductMessage(
            $listingProduct->getData('listing_id'),
            $listingProduct->getData('product_id'),
            $listingProduct->getId(),
            Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
            $logActionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
            $statusLogMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );

        if (is_null($duplicateItemId)) {
            return;
        }

        // ->__('Duplicated item %s was found and stopped on eBay.');
        $duplicateDeletedMessage = sprintf('Duplicated item %s was found and stopped on eBay.', $duplicateItemId);

        $logModel->addProductMessage(
            $listingProduct->getData('listing_id'),
            $listingProduct->getData('product_id'),
            $listingProduct->getId(),
            Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
            $logActionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
            $duplicateDeletedMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    private function modifyAndLogListingOther(Ess_M2ePro_Model_Listing_Other $listingOther,
                                              $status, $duplicateItemId = null)
    {
        $additionalData = $listingOther->getAdditionalData();
        unset($additionalData['last_failed_action_data']);

        $listingOther->addData(array(
            'status' => $status,
            'additional_data' => json_encode($additionalData),
        ))->save();

        /** @var Ess_M2ePro_Model_Listing_Other_Log $logModel */
        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logActionId = $logModel->getNextActionId();

        $statusLogMessage = $this->getStatusLogMessage($status);

        $logModel->addProductMessage(
            $listingOther->getId(),
            Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
            $logActionId,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
            $statusLogMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );

        if (is_null($duplicateItemId)) {
            return;
        }

        // ->__('Duplicated item %s was found and stopped on eBay.');
        $duplicateDeletedMessage = sprintf('Duplicated item %s was found and stopped on eBay.', $duplicateItemId);

        $logModel->addProductMessage(
            $listingOther->getId(),
            Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
            $logActionId,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
            $duplicateDeletedMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    // -----------------------------------

    private function getStatusLogMessage($status)
    {
        $message = '';
        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Not Listed".');
                $message = 'Item status was successfully changed to "Not Listed".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Listed".');
                $message = 'Item status was successfully changed to "Listed".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Hidden".');
                $message = 'Item status was successfully changed to "Listed (Hidden)".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Sold".');
                $message = 'Item status was successfully changed to "Sold".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Stopped".');
                $message = 'Item status was successfully changed to "Stopped".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Finished".');
                $message = 'Item status was successfully changed to "Finished".';
                break;
        }

        return $message;
    }

    //####################################
}