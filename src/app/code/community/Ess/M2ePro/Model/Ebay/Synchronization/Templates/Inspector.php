<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Inspector
    extends Ess_M2ePro_Model_Synchronization_Templates_Inspector
{
    private $checkedListListingsProductsIds = array();
    private $checkedRelistListingsProductsIds = array();
    private $checkedStopListingsProductsIds = array();

    private $checkedQtyListingsProductsIds = array();
    private $checkedPriceListingsProductsIds = array();

    //####################################

    public function makeRunner()
    {
        $runner = Mage::getModel('M2ePro/Synchronization_Templates_Runner');
        $runner->setConnectorModel('Connector_Ebay_Item_Dispatcher');
        $runner->setMaxProductsPerStep(10);
        return $runner;
    }

    //####################################

    public function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->checkedListListingsProductsIds)) {
            return false;
        } else {
            $this->checkedListListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isNotListed()) {
            return false;
        }

        if (!$listingProduct->isListable()) {
            return false;
        }

        if ($this->getRunner()
                 ->isExistProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
        $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isListMode()) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isScheduleEnabled()) {
            if (!$ebaySynchronizationTemplate->isScheduleIntervalNow() ||
                !$ebaySynchronizationTemplate->isScheduleWeekNow()) {
                return false;
            }
        }

        if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if($ebaySynchronizationTemplate->isListStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if($ebaySynchronizationTemplate->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if($ebaySynchronizationTemplate->isListWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if($ebaySynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQtyTotal();

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }
        //--------------------

        return true;
    }

    public function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->checkedRelistListingsProductsIds)) {
            return false;
        } else {
            $this->checkedRelistListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // eBay available status
        //--------------------
        if ($listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRelistable() && !$listingProduct->isHidden()) {
            return false;
        }

        $tempActionAndParams = $this->getRunnerRelistDataByListingProduct($listingProduct);

        if ($this->getRunner()
                 ->isExistProduct(
                        $listingProduct,
                        $tempActionAndParams['action'],
                        $listingProduct->isHidden() ? $tempActionAndParams['params'] : array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
        $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($listingProduct->isStopped() &&
            $ebaySynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isScheduleEnabled()) {
            if (!$ebaySynchronizationTemplate->isScheduleIntervalNow() ||
                !$ebaySynchronizationTemplate->isScheduleWeekNow()) {
                return false;
            }
        }

        if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if($ebaySynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if($ebaySynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if($ebaySynchronizationTemplate->isRelistWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if($ebaySynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQtyTotal();

            $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }
        //--------------------

        return true;
    }

    public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->checkedStopListingsProductsIds)) {
            return false;
        } else {
            $this->checkedStopListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isStoppable() || $listingProduct->isHidden()) {
            return false;
        }

        $tempActionAndParams = $this->getRunnerStopDataByListingProduct($listingProduct);

        if ($this->getRunner()
                 ->isExistProduct(
                        $listingProduct,
                        $tempActionAndParams['action'],
                        $tempActionAndParams['params'])
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
        $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if ($ebaySynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {

            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQtyTotal();

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }
        //--------------------

        return false;
    }

    //------------------------------------

    public function inspectReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->checkedQtyListingsProductsIds)) {
            return false;
        } else {
            $this->checkedQtyListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('qty'=>true,'variations'=>true));
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable() || $listingProduct->isHidden()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct(
            $listingProduct,
            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
        $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        if(!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isMaxAppliedValueModeOn = $ebaySynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $ebaySynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        if (!$listingProduct->getChildObject()->isVariationsReady()) {

            $ebayListingProduct = $listingProduct->getChildObject();

            $productQty = $ebayListingProduct->getQty();
            $channelQty = $ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold();

            //-- Check ReviseUpdateQtyMaxAppliedValue
            if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
                return false;
            }

            if ($productQty > 0 && $productQty != $channelQty) {
                $this->getRunner()->addProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                    $actionParams
                );
                return true;
            }

        } else {

            $totalQty = 0;
            $hasChange = false;

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

                $ebayVariation = $variation->getChildObject();

                $productQty = $ebayVariation->getQty();
                $channelQty = $ebayVariation->getOnlineQty() - $ebayVariation->getOnlineQtySold();

                if ($productQty != $channelQty) {
                    //-- Check ReviseUpdateQtyMaxAppliedValue
                    (!$isMaxAppliedValueModeOn || $productQty <= $maxAppliedValue || $channelQty <= $maxAppliedValue) &&
                        $hasChange = true;
                }

                $totalQty += $productQty;
            }

            if ($totalQty > 0 && $hasChange) {
                $this->getRunner()->addProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                    $actionParams
                );
                return true;
            }
        }
        //--------------------

        return false;
    }

    public function inspectRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->checkedPriceListingsProductsIds)) {
            return false;
        } else {
            $this->checkedPriceListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('price'=>true,'variations'=>true));
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct(
            $listingProduct,
            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
        $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if (!$listingProduct->getChildObject()->isVariationsReady()) {

            $hasChange = false;

            //---------
            $currentPrice = $listingProduct->getChildObject()->getBuyItNowPrice();
            $onlinePrice = $listingProduct->getChildObject()->getOnlineBuyItNowPrice();

            if ($currentPrice != $onlinePrice) {
                $hasChange = true;
            }

            if ($hasChange) {
                $this->getRunner()->addProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                    $actionParams
                );
                return true;
            }
            //---------

            if ($listingProduct->getChildObject()->isListingTypeAuction()) {

                //---------
                $currentPrice = $listingProduct->getChildObject()->getStartPrice();
                $onlinePrice = $listingProduct->getChildObject()->getOnlineStartPrice();

                if ($currentPrice != $onlinePrice) {
                    $hasChange = true;
                }

                if ($hasChange) {
                    $this->getRunner()->addProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                        $actionParams
                    );
                    return true;
                }
                //---------
                $currentPrice = $listingProduct->getChildObject()->getReservePrice();
                $onlinePrice = $listingProduct->getChildObject()->getOnlineReservePrice();

                if ($currentPrice != $onlinePrice) {
                    $hasChange = true;
                }

                if ($hasChange) {
                    $this->getRunner()->addProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                        $actionParams
                    );
                    return true;
                }
                //---------
            }

        } else {

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                $currentPrice = $variation->getChildObject()->getPrice();
                $onlinePrice = $variation->getChildObject()->getOnlinePrice();

                if ($currentPrice != $onlinePrice) {
                    $this->getRunner()->addProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                        $actionParams
                    );
                    return true;
                }
            }
        }
        //--------------------

        return false;
    }

    //####################################

    public function getRunnerRelistDataByListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $data = array();

        if ($listingProduct->isHidden()) {

            $data['action'] = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
            $data['params'] = array(
                'replaced_action' => Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                'all_data' => true
            );

        } else {

            if ($listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isRelistSendData()) {
                $tempParams = array('all_data'=>true);
            } else {
                $tempParams = array('only_data'=>array('base'=>true));
            }

            $data['action'] = Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
            $data['params'] = $tempParams;
        }

        return $data;
    }

    public function getRunnerStopDataByListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $data = array();

        if ($listingProduct->getChildObject()->getSellingFormatTemplate()->getOutOfStockControl()) {

            $data['action'] = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
            $data['params'] = array(
                'replaced_action' => Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                'only_data' => array('qty'=>true,'variations'=>true)
            );

        } else {

            $data = array(
                'action' => Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                'params' => array()
            );
        }

        return $data;
    }

    //####################################
}