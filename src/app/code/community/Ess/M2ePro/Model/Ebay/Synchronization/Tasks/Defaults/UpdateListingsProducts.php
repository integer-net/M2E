<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Defaults_UpdateListingsProducts
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    private $tempToTime = NULL;
    protected $logActionId = NULL;

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
        $this->_profiler->addTitle($componentName.'Update Listings Products');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Update Listings Products" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Update Listings Products" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare since time for first time
        $this->checkAndPrepareSinceTime();

        // Get all changed listings products items
        $changedListingsProducts = $this->getChangedListingsProducts();

        $this->_profiler->addTimePoint(__METHOD__,'Update listings products');

        // Update listings products
        $this->updateListingsProducts($changedListingsProducts);

        // Update listings products variations
        $this->updateListingsProductsVariations($changedListingsProducts);

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function getEbayCheckSinceTime()
    {
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/ebay/defaults/update_listings_products/','since_time');
    }

    private function setEbayCheckSinceTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->setGroupValue('/ebay/defaults/update_listings_products/','since_time',$time);
    }

    private function checkAndPrepareSinceTime()
    {
        // Get last since time
        //------------------------
        $lastSinceTime = $this->getEbayCheckSinceTime();

        if (is_null($lastSinceTime) || empty($lastSinceTime)) {
            $lastSinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastSinceTime->modify("-1 year");
        } else {
            $lastSinceTime = new DateTime($lastSinceTime, new DateTimeZone('UTC'));
        }
        //------------------------

        // Get min shold for synch
        //------------------------
        $minSholdTime = new DateTime('now', new DateTimeZone('UTC'));
        $minSholdTime->modify("-1 month");
        //------------------------

        // Prepare last since time
        //------------------------
        if ((int)$lastSinceTime->format('U') < (int)$minSholdTime->format('U')) {
            $lastSinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastSinceTime->modify("-10 days");
            $this->setEbayCheckSinceTime($lastSinceTime);
        }
        //------------------------
    }

    //####################################

    private function getChangedListingsProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get & prepared all changes from eBay');

        // Get Time last update From. For all account same
        //---------------------------
        $sinceTime = $this->getEbayCheckSinceTime();
        //---------------------------

        // For each account get item that changed into eBay
        //---------------------------
        $ebayAccounts = Mage::helper('M2ePro/Component_Ebay')->getModel('Account')
                                ->getCollection()->toArray();
        if ((int)$ebayAccounts['totalRecords'] == 0) {
            return array();
        }
        //---------------------------

        // Get changes for each account
        //---------------------------
        $changedListingsProducts = array();

        $accountIteration = 1;
        $percentsForAccount = (5*(self::PERCENTS_INTERVAL/6))/(int)$ebayAccounts['totalRecords'];

        foreach ($ebayAccounts['items'] as $account) {

            $changedListingsProductsForAccount = $this->getChangedListingsProductsForAccount($account,$sinceTime);
            $changedListingsProducts = array_merge($changedListingsProducts,$changedListingsProductsForAccount);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }
        //---------------------------

        // Update since time for next times
        //---------------------------
        if (!is_string($this->tempToTime) || empty($this->tempToTime)) {
            $this->tempToTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        }
        $this->setEbayCheckSinceTime($this->tempToTime);
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);

        return $changedListingsProducts;
    }

    private function getChangedListingsProductsForAccount($account, $sinceTime)
    {
        $this->_profiler->addTitle('Starting account "'.$account['title'].'"');

        $this->_profiler->addTimePoint(__METHOD__.'get'.$account['id'],'Get changes from eBay');

        // ->__('Task "Update Listings Products" for eBay account: "%s" is started. Please wait...')
        $status = 'Task "Update Listings Products" for eBay account: "%s" is started. Please wait...';
        $tempString = Mage::helper('M2ePro')->__($status, $account['title']);
        $this->_lockItem->setStatus($tempString);

        // Get all changes on eBay for account
        //---------------------------
        $responseData = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                                ->processVirtual('item','get','changes',
                                                         array('since_time'=>$sinceTime),NULL,
                                                         NULL,$account['id'],NULL);

        $changedItems = array();

        if (isset($responseData['items']) && isset($responseData['to_time'])) {
            $changedItems = (array)$responseData['items'];
            $this->tempToTime = (string)$responseData['to_time'];
        } else {

            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime->modify("-1 day");
            $sinceTime = $sinceTime->format('Y-m-d H:i:s');

            $responseData = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                                ->processVirtual('item','get','changes',
                                                         array('since_time'=>$sinceTime),NULL,
                                                         NULL,$account['id'],NULL);

            if (isset($responseData['items']) && isset($responseData['to_time'])) {
                $changedItems = (array)$responseData['items'];
                $this->tempToTime = (string)$responseData['to_time'];
            } else {

                $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
                $sinceTime = $sinceTime->format('Y-m-d H:i:s');

                $responseData = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                                    ->processVirtual('item','get','changes',
                                                             array('since_time'=>$sinceTime),NULL,
                                                             NULL,$account['id'],NULL);

                if (isset($responseData['items']) && isset($responseData['to_time'])) {
                    $changedItems = (array)$responseData['items'];
                    $this->tempToTime = (string)$responseData['to_time'];
                } else {
                    is_null($this->tempToTime) && $this->tempToTime = (string)$sinceTime;
                }
            }
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'get'.$account['id']);

        $this->_profiler->addTitle('Total count changes from eBay: '.count($changedItems));

        $this->_profiler->addTimePoint(__METHOD__.'prepare'.$account['id'],'Processing received changes from eBay');

        $status = <<<STATUS
Task "Update Listings Products" for eBay account: "%s" is in data processing state. Please wait...
STATUS;
        $tempString = Mage::helper('M2ePro')->__($status, $account['title']);
        $this->_lockItem->setStatus($tempString);

        // Save changed listings products
        //---------------------------
        $changedListingsProducts = array();
        foreach ($changedItems as $changeItem) {

            // Check exist listing product
            //--------------------------
            /* @var $tempListingProductModel Ess_M2ePro_Model_Listing_Product */
            $tempListingProductModel = Mage::helper('M2ePro/Component_Ebay')->getListingProductByEbayItem(
                $changeItem['id'], $account['id']
            );

            if (is_null($tempListingProductModel)) {
                continue;
            }

            // Listing product don't listed
            if ($tempListingProductModel->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                continue;
            }
            //--------------------------

            // Get prepared listings products
            //--------------------------
            $changedListingsProducts[] = $this->prepareChangedListingsProducts($tempListingProductModel,$changeItem);
            //--------------------------
        }
        //---------------------------

        $this->_profiler->addTitle('Count related with M2ePro changes: '.count($changedListingsProducts));

        $this->_profiler->saveTimePoint(__METHOD__.'prepare'.$account['id']);
        $this->_profiler->addEol();

        return $changedListingsProducts;
    }

    private function prepareChangedListingsProducts(Ess_M2ePro_Model_Listing_Product $tempListingProductModel,
                                                    $ebayChange)
    {
        /** @var $tempEbayListingProductModel Ess_M2ePro_Model_Ebay_Listing_Product */
        $tempEbayListingProductModel = $tempListingProductModel->getChildObject();

        // Prepare eBay changes values
        //--------------------------
        $tempEbayChanges = array();

        if ($tempEbayListingProductModel->isListingTypeAuction()) {
            $tempEbayChanges['online_start_price'] = (float)$ebayChange['currentPrice'] < 0
                ? 0 : (float)$ebayChange['currentPrice'];
        }
        if ($tempEbayListingProductModel->isListingTypeFixed()) {
            $tempEbayChanges['online_buyitnow_price'] = (float)$ebayChange['currentPrice'] < 0
                ? 0 : (float)$ebayChange['currentPrice'];
        }

        $tempEbayChanges['online_qty'] = (int)$ebayChange['quantity'] < 0
            ? 0 : (int)$ebayChange['quantity'];
        $tempEbayChanges['online_qty_sold'] = (int)$ebayChange['quantitySold'] < 0
            ? 0 : (int)$ebayChange['quantitySold'];

        if ($tempEbayListingProductModel->isListingTypeAuction()) {
            $tempEbayChanges['online_qty'] = 1;
            $tempEbayChanges['online_bids'] = (int)$ebayChange['bidCount'] < 0 ? 0 : (int)$ebayChange['bidCount'];
        }

        $tempEbayChanges['start_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
            $ebayChange['startTime']
        );
        $tempEbayChanges['end_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
            $ebayChange['endTime']
        );

        if ($tempEbayChanges['online_qty'] == $tempEbayChanges['online_qty_sold'] &&
            ($ebayChange['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
             $ebayChange['listingStatus'] == self::EBAY_STATUS_ENDED)) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;

        } else if ($ebayChange['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;

        } else if ($ebayChange['listingStatus'] == self::EBAY_STATUS_ENDED) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;

        } else if ($ebayChange['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

            $tempEbayChanges['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;

        }

        if ($tempListingProductModel->getStatus() != $tempEbayChanges['status'] ||
            $tempListingProductModel->getChildObject()->getOnlineQty() != $tempEbayChanges['online_qty'] ||
            $tempListingProductModel->getChildObject()->getOnlineQtySold() != $tempEbayChanges['online_qty_sold']) {
            Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                $tempListingProductModel->getProductId(), Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
            );
        }

        if ($tempEbayChanges['status'] != $tempListingProductModel->getStatus()) {

            $tempEbayChanges['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            $tempLogMessage = '';
            switch ($tempEbayChanges['status']) {
                case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                    // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Listed".');
                    $tempLogMessage = 'Item status was successfully changed to "Listed".';
                    break;
                case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                    // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Sold".');
                    $tempLogMessage = 'Item status was successfully changed to "Sold".';
                    break;
                case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                    // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Stopped".');
                    $tempLogMessage = 'Item status was successfully changed to "Stopped".';
                    break;
                case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                    // Parser hack -> Mage::helper('M2ePro')->__('Item status was successfully changed to "Finished".');
                    $tempLogMessage = 'Item status was successfully changed to "Finished".';
                    break;
            }

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
            $tempLog->addProductMessage(
                $tempListingProductModel->getListingId(),
                $tempListingProductModel->getProductId(),
                $tempListingProductModel->getId(),
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                $this->getLogActionId(),
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                $tempLogMessage,
                Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }
        //--------------------------

        // Create changed listings products
        //--------------------------
        $changedListingsProducts = array(
            'ebay_item_id' => $ebayChange['id'],
            'listing_product' => array(
                'instance' => $tempListingProductModel,
                'changes' => $tempEbayChanges
            ),
            'listings_products_variations' => array()
        );
        //--------------------------

        // Cancel when have not eBay variations
        //--------------------------
        if (!isset($ebayChange['variations']) || is_null($ebayChange['variations'])) {
            return $changedListingsProducts;
        }
        //--------------------------

        // Get listings products variations
        //-----------------------
        $tempVariations = $tempListingProductModel->getVariations(true);
        if (count($tempVariations) == 0) {
            return $changedListingsProducts;
        }
        //-----------------------

        // Get listings products variations with options
        //-----------------------
        $tempVariationsWithOptions = array();

        foreach ($tempVariations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $options = $variation->getOptions(true);

            if (count($options) == 0) {
                continue;
            }

            $tempVariationsWithOptions[] = array(
                'variation' => $variation,
                'options' => $options
            );
        }

        if (count($tempVariationsWithOptions) == 0) {
            return $changedListingsProducts;
        }
        //-----------------------

        // Search our variations for eBay variations
        //--------------------------
        foreach ($ebayChange['variations'] as $ebayVariation) {

            // Find our variation
            //--------------------------
            foreach ($tempVariationsWithOptions as $M2eProVariation) {

                $equalVariation = true;

                foreach ($M2eProVariation['options'] as $M2eProOptionValue) {

                    $haveOption = false;

                    foreach ($ebayVariation['specifics'] as $ebayOptionKey=>$ebayOptionValue) {

                        if ($M2eProOptionValue->getData('attribute') == $ebayOptionKey &&
                            $M2eProOptionValue->getData('option') == $ebayOptionValue) {
                            $haveOption = true;
                            break;
                        }
                    }

                    if ($haveOption === false) {
                        $equalVariation = false;
                        break;
                    }
                }

                if ($equalVariation === true &&
                    count($M2eProVariation['options']) == count($ebayVariation['specifics'])) {

                    // Prepare eBay changes values
                    //--------------------------
                    $tempEbayChanges = array();

                    $tempEbayChanges['online_price'] = (float)$ebayVariation['price'] < 0
                        ? 0 : (float)$ebayVariation['price'];
                    $tempEbayChanges['online_qty'] = (int)$ebayVariation['quantity'] < 0
                        ? 0 : (int)$ebayVariation['quantity'];
                    $tempEbayChanges['online_qty_sold'] = (int)$ebayVariation['quantitySold'] < 0
                        ? 0 : (int)$ebayVariation['quantitySold'];

                    if ($tempEbayChanges['online_qty'] <= $tempEbayChanges['online_qty_sold']) {
                        $tempEbayChanges['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
                    }
                    if ($tempEbayChanges['online_qty'] <= 0) {
                        $tempEbayChanges['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
                    }
                    //--------------------------

                    // Add changed variation
                    //--------------------------
                    $changedListingsProducts['listings_products_variations'][] = array(
                        'instance' => $M2eProVariation,
                        'changes' => $tempEbayChanges
                    );
                    //--------------------------

                    break;
                }
            }
            //--------------------------
        }

        return $changedListingsProducts;
    }

    //####################################

    private function updateListingsProducts(&$changedListingsProducts)
    {
        foreach ($changedListingsProducts as $listingProduct) {

            // Get separate data
            //--------------------------
            /** @var $listingProductModel Ess_M2ePro_Model_Listing_Product */
            $listingProductModel = $listingProduct['listing_product']['instance'];
            $listingProductChange = $listingProduct['listing_product']['changes'];
            //--------------------------

            // Save updated data
            //--------------------------
            $listingProductModel->addData($listingProductChange)->save();
            //--------------------------

            // Update variations status
            //--------------------------
            $tempVariations = $listingProductModel->getVariations(true);
            foreach ($tempVariations as $variation) {

                if ($listingProductModel->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
                    $listingProductModel->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED) {
                    $dataForUpdate['status'] = $listingProductModel->getData('status');
                    $variation->addData($dataForUpdate)->save();
                }

                if ($listingProductModel->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED &&
                    $variation->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
                    $variation->addData($dataForUpdate)->save();
                }
            }
            //--------------------------
        }
    }

    private function updateListingsProductsVariations(&$changedListingsProducts)
    {
        foreach ($changedListingsProducts as $listingProduct) {

            foreach ($listingProduct['listings_products_variations'] as $listingProductVariation) {

                // Get separate data
                //--------------------------
                /** @var $listingProductVariationModel Ess_M2ePro_Model_Listing_Product_Variation */
                $listingProductVariationModel = $listingProductVariation['instance']['variation'];
                $listingProductVariationChanges = $listingProductVariation['changes'];
                //--------------------------

                // Save updated data
                //--------------------------
                $listingProductVariationModel->addData($listingProductVariationChanges)->save();
                //--------------------------
            }
        }
    }

    //####################################

    protected function getLogActionId()
    {
        if (!is_null($this->logActionId)) {
            return $this->logActionId;
        }

        return $this->logActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
    }

    //####################################
}