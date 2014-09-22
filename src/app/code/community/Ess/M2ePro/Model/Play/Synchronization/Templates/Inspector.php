<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Templates_Inspector
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
        $runner->setConnectorModel('Connector_Play_Product_Dispatcher');
        $runner->setMaxProductsPerStep(100);
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

        // Play available status
        //--------------------
        if (!$listingProduct->isNotListed()) {
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

        /* @var $playSynchronizationTemplate Ess_M2ePro_Model_Play_Template_Synchronization */
        $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if(!$playSynchronizationTemplate->isListMode()) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if($playSynchronizationTemplate->isListStatusEnabled()) {

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

        if($playSynchronizationTemplate->isListIsInStock()) {

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

        if($playSynchronizationTemplate->isListWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $typeQty = (int)$playSynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if($playSynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(false);

            $typeQty = (int)$playSynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_BETWEEN &&
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

        // Play available status
        //--------------------
        if (!$listingProduct->isStopped()) {
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

        /* @var $playSynchronizationTemplate Ess_M2ePro_Model_Play_Template_Synchronization */
        $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if(!$playSynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($playSynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if($playSynchronizationTemplate->isRelistStatusEnabled()) {

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

        if($playSynchronizationTemplate->isRelistIsInStock()) {

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

        if($playSynchronizationTemplate->isRelistWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $typeQty = (int)$playSynchronizationTemplate->getRelistWhenQtyMagentoHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getRelistWhenQtyMagentoHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getRelistWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if($playSynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(false);

            $typeQty = (int)$playSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_BETWEEN &&
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

        // Play available status
        //--------------------
        if (!$listingProduct->isListed()) {
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

        /* @var $playSynchronizationTemplate Ess_M2ePro_Model_Play_Template_Synchronization */
        $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if ($playSynchronizationTemplate->isStopStatusDisabled()) {

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

        if ($playSynchronizationTemplate->isStopOutOfStock()) {

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

        if ($playSynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $typeQty = (int)$playSynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($playSynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQty(false);

            $typeQty = (int)$playSynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_BETWEEN &&
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

        // Play available status
        //--------------------
        if (!$listingProduct->isListed()) {
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

        /* @var $playSynchronizationTemplate Ess_M2ePro_Model_Play_Template_Synchronization */
        $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$playSynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isMaxAppliedValueModeOn = $playSynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $playSynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

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

        // Play available status
        //--------------------
        if (!$listingProduct->isListed()) {
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

        /* @var $playSynchronizationTemplate Ess_M2ePro_Model_Play_Template_Synchronization */
        $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$playSynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        if($listingProduct->getChildObject()->isVariationProduct() &&
           !$listingProduct->getChildObject()->isVariationMatched()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $onlinePriceGbr = $listingProduct->getChildObject()->getOnlinePriceGbr();
        $currentPriceGbr = $listingProduct->getChildObject()->getPriceGbr(true);

        if ($currentPriceGbr != $onlinePriceGbr) {

            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }

        $onlinePriceEuro = $listingProduct->getChildObject()->getOnlinePriceEuro();
        $currentPriceEuro = $listingProduct->getChildObject()->getPriceEuro(true);

        if ($onlinePriceEuro != $currentPriceEuro) {

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