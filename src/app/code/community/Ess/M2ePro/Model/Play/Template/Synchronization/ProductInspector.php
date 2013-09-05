<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Template_Synchronization_ProductInspector
{
    /**
     * @var Ess_M2ePro_Model_Play_Template_Synchronization_RunnerActions
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
            $runnerActionsModel = Mage::getModel('M2ePro/Play_Template_Synchronization_RunnerActions');
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

        $playSynchGroup = '/play/templates/';
        $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                 ->getGroupValue($playSynchGroup,'mode');

        if (!$tempGlobalMode || !$tempLocalMode) {
            return;
        }

        if ($listingProduct->isNotListed()) {

            $playSynch = '/play/templates/list/';
            $listMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                ->getGroupValue($playSynch,'mode');
            if ($listMode) {
                $tempResult = $this->isMeetListRequirements($listingProduct);
                $tempResult && $this->_runnerActions
                                    ->setProduct($listingProduct,
                                                 Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_LIST,
                                                 array());
            }

        } else if ($listingProduct->isListed()) {

            $tempResult = false;

            // Check Stop Requirements
            //-------------------------------
            $playSynch = '/play/templates/stop/';
            $stopMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                ->getGroupValue($playSynch,'mode');
            if ($stopMode) {
                $tempResult = $this->isMeetStopRequirements($listingProduct);
                $tempResult && $this->_runnerActions
                                    ->setProduct($listingProduct,
                                                 Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_STOP,
                                                 array());
            }
            //-------------------------------

            // Check Revise Requirements
            //-------------------------------
            if (!$tempResult) {

                $playSynch = '/play/templates/revise/';
                $reviseMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($playSynch,'mode');

                if ($reviseMode) {
                    $this->inspectReviseQtyRequirements($listingProduct);
                    $this->inspectRevisePriceRequirements($listingProduct);
                }
            }
            //-------------------------------

        } else {

            $playSynch = '/play/templates/relist/';
            $relistMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                  ->getGroupValue($playSynch,'mode');

            if (!$relistMode) {
                return;
            }

            // Check Relist Requirements
            //-------------------------------
            $tempResult = $this->isMeetRelistRequirements($listingProduct);

            if ($tempResult) {

                $this->_runnerActions
                         ->setProduct($listingProduct,
                                      Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_RELIST,
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

        // Play available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_STOP,
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
        if ($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isStopStatusDisabled()) {

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

        if ($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isStopOutOfStock()) {

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

        if ($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQty(true);
            $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

            $typeQty = (int)$playSynchronizationTemplate->getStopWhenQtyHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getStopWhenQtyHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getStopWhenQtyHasValueMax();

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_RELIST,
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
        if(!$listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isRelistMode()) {
            return false;
        }

        if ($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isRelistStatusEnabled()) {

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

        if($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isRelistIsInStock()) {

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

        if($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isRelistWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

            $typeQty = (int)$playSynchronizationTemplate->getRelistWhenQtyHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getRelistWhenQtyHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getRelistWhenQtyHasValueMax();

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_LIST,
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
        if(!$listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isListMode()) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isListStatusEnabled()) {

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

        if($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isListIsInStock()) {

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

        if($listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isListWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $playSynchronizationTemplate = $listingProduct->getChildObject()->getPlaySynchronizationTemplate();

            $typeQty = (int)$playSynchronizationTemplate->getListWhenQtyHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getListWhenQtyHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getListWhenQtyHasValueMax();

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

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_REVISE,
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
        if (!$listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isReviseWhenChangeQty()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isMaxAppliedValueModeOn = $listingProduct->getChildObject()
                                    ->getPlaySynchronizationTemplate()->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $listingProduct->getChildObject()
                                    ->getPlaySynchronizationTemplate()->getReviseUpdateQtyMaxAppliedValue();

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
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_REVISE,
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

        // Play available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_REVISE,
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
        if (!$listingProduct->getChildObject()->getPlaySynchronizationTemplate()->isReviseWhenChangePrice()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $onlinePriceGbr = $listingProduct->getChildObject()->getOnlinePriceGbr();
        $currentPriceGbr = $listingProduct->getChildObject()->getPriceGbr(true);

        if ($currentPriceGbr != $onlinePriceGbr) {

            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_REVISE,
                        $actionParams
                 );
            return true;
        }

        $onlinePriceEuro = $listingProduct->getChildObject()->getOnlinePriceEuro();
        $currentPriceEuro = $listingProduct->getChildObject()->getPriceEuro(true);

        if ($onlinePriceEuro != $currentPriceEuro) {

            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher::ACTION_REVISE,
                        $actionParams
                 );
            return true;
        }
        //--------------------

        return false;
    }

    //####################################
}