<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector
    extends Ess_M2ePro_Model_Synchronization_Templates_Inspector
{
    private $_checkedListListingsProductsIds = array();
    private $_checkedRelistListingsProductsIds = array();
    private $_checkedStopListingsProductsIds = array();

    private $_checkedQtyListingsProductsIds = array();
    private $_checkedPriceListingsProductsIds = array();

    //####################################

    public function makeRunner()
    {
        $runner = Mage::getModel('M2ePro/Synchronization_Templates_Runner');
        $runner->setConnectorModel('Connector_Amazon_Product_Dispatcher');
        $runner->setMaxProductsPerStep(1000);
        return $runner;
    }

    //####################################

    public function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedListListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedListListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isNotListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isListable()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingProduct,
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

        /* @var $amazonSynchronizationTemplate Ess_M2ePro_Model_Amazon_Template_Synchronization */
        $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if(!$amazonSynchronizationTemplate->isListMode()) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($amazonSynchronizationTemplate->isListStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStatuses = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);

                    // all variations are disabled
                    if ((int)min($tempStatuses) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return false;
                    }
                }
            }
        }

        if($amazonSynchronizationTemplate->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStocks = $listingProduct->getVariationsStockAvailabilities($productsIdsForEachVariation);

                    // all variations are out of stock
                    if (!(int)max($tempStocks)) {
                        return false;
                    }
                }
            }
        }

        if($amazonSynchronizationTemplate->isListWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $typeQty = (int)$amazonSynchronizationTemplate->getListWhenQtyHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getListWhenQtyHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getListWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_BETWEEN &&
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
        if (in_array($listingProduct->getId(),$this->_checkedRelistListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedRelistListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isStopped() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRelistable()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingProduct,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                                               array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $amazonSynchronizationTemplate Ess_M2ePro_Model_Amazon_Template_Synchronization */
        $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if(!$amazonSynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($amazonSynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($amazonSynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStatuses = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);

                    // all variations are disabled
                    if ((int)min($tempStatuses) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return false;
                    }
                }
            }
        }

        if($amazonSynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStocks = $listingProduct->getVariationsStockAvailabilities($productsIdsForEachVariation);

                    // all variations are out of stock
                    if (!(int)max($tempStocks)) {
                        return false;
                    }
                }
            }
        }

        if($amazonSynchronizationTemplate->isRelistWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $typeQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_BETWEEN &&
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
        if (in_array($listingProduct->getId(),$this->_checkedStopListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedStopListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingProduct,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                                               array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
            return false;
        }
        //--------------------

        /* @var $amazonSynchronizationTemplate Ess_M2ePro_Model_Amazon_Template_Synchronization */
        $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if ($amazonSynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStatuses = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);

                    // all variations are disabled
                    if ((int)min($tempStatuses) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return true;
                    }
                }
            }
        }

        if ($amazonSynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($listingProduct->getChildObject()->isVariationsReady()) {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStocks = $listingProduct->getVariationsStockAvailabilities($productsIdsForEachVariation);

                    // all variations are out of stock
                    if (!(int)max($tempStocks)) {
                        return true;
                    }
                }
            }
        }

        if ($amazonSynchronizationTemplate->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $typeQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_BETWEEN &&
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
        if (in_array($listingProduct->getId(),$this->_checkedQtyListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedQtyListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('qty'=>true));
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingProduct,
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

        /* @var $amazonSynchronizationTemplate Ess_M2ePro_Model_Amazon_Template_Synchronization */
        $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$amazonSynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isMaxAppliedValueModeOn = $amazonSynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $amazonSynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $listingProduct->getChildObject()->getQty();
        $channelQty = $listingProduct->getChildObject()->getOnlineQty();

        //-- Check ReviseUpdateQtyMaxAppliedValue
        if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
            return false;
        }

        if ($productQty > 0 && $productQty != $channelQty) {
            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }
        //--------------------

        return false;
    }

    public function inspectRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedPriceListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedPriceListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('price'=>true));
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingProduct,
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

        /* @var $amazonSynchronizationTemplate Ess_M2ePro_Model_Amazon_Template_Synchronization */
        $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$amazonSynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $currentPrice = $listingProduct->getChildObject()->getPrice();
        $onlinePrice = $listingProduct->getChildObject()->getOnlinePrice();

        if ($currentPrice != $onlinePrice) {
            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }

        $currentSalePriceInfo = $listingProduct->getChildObject()->getSalePriceInfo();

        $currentSalePrice = $currentSalePriceInfo['price'];
        $onlineSalePrice = $listingProduct->getChildObject()->getOnlineSalePrice();

        if ((is_null($currentSalePrice) && !is_null($onlineSalePrice)) ||
            (!is_null($currentSalePrice) && is_null($onlineSalePrice)) ||
            (float)$currentSalePrice != (float)$onlineSalePrice) {

            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }
        //--------------------

        //--------------------
        $currentSalePriceStartDate = $currentSalePriceInfo['start_date'];
        $onlineSalePriceStartDate = $listingProduct->getChildObject()->getOnlineSalePriceStartDate();

        $currentSalePriceEndDate = $currentSalePriceInfo['end_date'];
        $onlineSalePriceEndDate = $listingProduct->getChildObject()->getOnlineSalePriceEndDate();

        if ($currentSalePriceStartDate != $onlineSalePriceStartDate ||
            $currentSalePriceEndDate   != $onlineSalePriceEndDate) {

            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }
        //--------------------

        return false;
    }

    //####################################
}