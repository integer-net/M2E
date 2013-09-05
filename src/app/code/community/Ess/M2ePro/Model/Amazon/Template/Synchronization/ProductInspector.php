<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Synchronization_ProductInspector
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Synchronization_RunnerActions
     */
    protected $_runnerActions = NULL;

    private $_checkedRelistListingsProductsIds = array();
    private $_checkedListListingsProductsIds = array();
    private $_checkedStopListingsProductsIds = array();

    private $_checkedQtyListingsProductsIds = array();
    private $_checkedPriceListingsProductsIds = array();

    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        if (isset($params['runner_actions'])) {
            $this->_runnerActions = $params['runner_actions'];
        } else {
            $runnerActionsModel = Mage::getModel('M2ePro/Amazon_Template_Synchronization_RunnerActions');
            $runnerActionsModel->removeAllProducts();
            $this->_runnerActions = $runnerActionsModel;
        }
    }

    //####################################

    public function processProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->processProducts(array($listingProduct));
    }

    public function processProducts(array $listingsProducts = array())
    {
        $this->_runnerActions->removeAllProducts();

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $this->processItem($listingProduct);
        }

        $this->_runnerActions->execute();
        $this->_runnerActions->removeAllProducts();
    }

    //-----------------------------------

    private function processItem(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $synchGroup = '/templates/';
        $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                  ->getGroupValue($synchGroup,'mode');

        $amazonSynchGroup = '/amazon/templates/';
        $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                 ->getGroupValue($amazonSynchGroup,'mode');

        if (!$tempGlobalMode || !$tempLocalMode) {
            return;
        }

        if ($listingProduct->isNotListed()) {

            $amazonSynch = '/amazon/templates/list/';
            $listMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                ->getGroupValue($amazonSynch,'mode');
            if ($listMode) {
                $tempResult = $this->isMeetListRequirements($listingProduct);
                $tempResult && $this->_runnerActions
                                    ->setProduct($listingProduct,
                                                 Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_LIST,
                                                 array());
            }

        } else if ($listingProduct->isListed()) {

            $tempResult = false;

            // Check Stop Requirements
            //-------------------------------
            $amazonSynch = '/amazon/templates/stop/';
            $stopMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                ->getGroupValue($amazonSynch,'mode');
            if ($stopMode) {
                $tempResult = $this->isMeetStopRequirements($listingProduct);
                $tempResult && $this->_runnerActions
                                    ->setProduct($listingProduct,
                                                 Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_STOP,
                                                 array());
            }
            //-------------------------------

            // Check Revise Requirements
            //-------------------------------
            if (!$tempResult) {

                $amazonSynch = '/amazon/templates/revise/';
                $reviseMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($amazonSynch,'mode');

                if ($reviseMode) {
                    $this->inspectReviseQtyRequirements($listingProduct);
                    $this->inspectRevisePriceRequirements($listingProduct);
                }
            }
            //-------------------------------

        } else {

            $amazonSynch = '/amazon/templates/relist/';
            $relistMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                  ->getGroupValue($amazonSynch,'mode');

            if (!$relistMode) {
                return;
            }

            // Check Relist Requirements
            //-------------------------------
            $tempResult = $this->isMeetRelistRequirements($listingProduct);

            if ($tempResult) {

                $this->_runnerActions
                         ->setProduct($listingProduct,
                                      Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_RELIST,
                                      array());
            }
            //-------------------------------
        }
    }

    //####################################

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_STOP,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if ($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isStopStatusDisabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() ==
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                return true;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $statusesTemp = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);
                    if ((int)min($statusesTemp) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return true;
                    }
                }
            }
        }

        if ($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return true;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $stockAvailabilityTemp = $listingProduct->getVariationsStockAvailabilities(
                        $productsIdsForEachVariation
                    );
                    if (!(int)max($stockAvailabilityTemp)) {
                        return true;
                    }
                }
            }
        }

        if ($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQty(true);
            $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_RELIST,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if(!$listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isRelistMode()) {
            return false;
        }

        if ($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isRelistStatusEnabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() !=
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return false;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $statusesTemp = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);
                    if ((int)min($statusesTemp) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return false;
                    }
                }
            }
        }

        if($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return false;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $stockAvailabilityTemp = $listingProduct->getVariationsStockAvailabilities(
                        $productsIdsForEachVariation
                    );
                    if (!(int)max($stockAvailabilityTemp)) {
                        return false;
                    }
                }
            }
        }

        if($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isRelistWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_LIST,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if(!$listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isListMode()) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isListStatusEnabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() !=
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return false;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $statusesTemp = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);
                    if ((int)min($statusesTemp) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return false;
                    }
                }
            }
        }

        if($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return false;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $stockAvailabilityTemp = $listingProduct->getVariationsStockAvailabilities(
                        $productsIdsForEachVariation
                    );
                    if (!(int)max($stockAvailabilityTemp)) {
                        return false;
                    }
                }
            }
        }

        if($listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isListWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_REVISE,
                        $actionParams)
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isReviseWhenChangeQty()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isMaxAppliedValueModeOn = $listingProduct->getChildObject()
                                ->getAmazonSynchronizationTemplate()->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $listingProduct->getChildObject()
                                ->getAmazonSynchronizationTemplate()->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $listingProduct->getChildObject()->getQty();
        $channelQty = $listingProduct->getChildObject()->getOnlineQty();

        //-- Check ReviseUpdateQtyMaxAppliedValue
        if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
            return false;
        }

        if ($productQty > 0 && $productQty != $channelQty) {
            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_REVISE,
                        $actionParams
                 );
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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_REVISE,
                        $actionParams)
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getChildObject()->getAmazonSynchronizationTemplate()->isReviseWhenChangePrice()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $currentPrice = $listingProduct->getChildObject()->getPrice();
        $onlinePrice = $listingProduct->getChildObject()->getOnlinePrice();

        if ($currentPrice != $onlinePrice) {
            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_REVISE,
                        $actionParams
                 );
            return true;
        }

        $currentSalePrice = $listingProduct->getChildObject()->getSalePrice();
        $onlineSalePrice = $listingProduct->getChildObject()->getOnlineSalePrice();

        if ((is_null($currentSalePrice) && !is_null($onlineSalePrice)) ||
            (!is_null($currentSalePrice) && is_null($onlineSalePrice)) ||
            (float)$currentSalePrice != (float)$onlineSalePrice) {
            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Amazon_Product_Dispatcher::ACTION_REVISE,
                        $actionParams
                 );
            return true;
        }
        //--------------------

        return false;
    }

    //####################################
}