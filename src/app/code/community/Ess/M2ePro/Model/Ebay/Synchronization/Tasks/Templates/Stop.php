<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Stop
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 50;
    const PERCENTS_END = 60;
    const PERCENTS_INTERVAL = 10;

    private $_checkedListingsProductsIds = array();

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
        $this->_profiler->addTitle($componentName.'Stop Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Stop" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Stop" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $tasks = array(
            'immediatelyChangedProducts',
        );

        foreach ($tasks as $i => $task) {
            $this->$task();

            $this->_lockItem->setPercents(self::PERCENTS_START + ($i+1)*self::PERCENTS_INTERVAL/count($tasks));
            $this->_lockItem->activate();
        }
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was changed');

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$this->isMeetStopRequirements($listingProduct)) {
                continue;
            }

            $tempActionAndParams = $this->getActionAndParamsFromListingProduct($listingProduct);

            $this->_runnerActions->setProduct(
                $listingProduct,
                $tempActionAndParams['action'],
                $tempActionAndParams['params']
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedListingsProductsIds[] = $listingProduct->getId();
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

        $tempActionAndParams = $this->getActionAndParamsFromListingProduct($listingProduct);

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        $tempActionAndParams['action'],
                        $tempActionAndParams['params'])
        ) {
            return false;
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if ($listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isStopStatusDisabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() ==
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                return true;
            } else {

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

        if ($listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return true;
            } else {

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

        if ($listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQtyTotal(true);
            $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueMax();

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

    //####################################

    private function getActionAndParamsFromListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $actionAndParams = array();

        if ($listingProduct->getChildObject()->getSellingFormatTemplate()->getOutOfStockControl()) {
            $actionAndParams['action'] = Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE;
            $actionAndParams['params'] = array(
                'replaced_action' => Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_STOP,
                'only_data' => array('qty'=>true,'variations'=>true));
        } else {
            $actionAndParams = array(
                'action' => Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_STOP,
                'params' => array()
            );
        }

        return $actionAndParams;
    }

    //####################################
}