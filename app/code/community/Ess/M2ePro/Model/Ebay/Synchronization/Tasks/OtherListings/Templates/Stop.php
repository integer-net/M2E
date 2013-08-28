<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Stop
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Abstract
{
    const PERCENTS_START = 70;
    const PERCENTS_END = 80;
    const PERCENTS_INTERVAL = 10;

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
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was changed');

        // Get changed listings others
        //------------------------------------
        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );
        //------------------------------------

        // Filter only needed listings others
        //------------------------------------
        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            if (!$this->isMeetStopRequirements($listingOther)) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP,
                array()
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        // eBay available status
        //--------------------
        if (!$listingOther->isListed()) {
            return false;
        }

        if (!$listingOther->isStoppable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        if ($this->_runnerActions->isExistProductAction(
            $listingOther,
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP,
            array())
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if ($listingOther->getChildObject()->getSynchronizationModel()->isStopStatusDisabled()) {

            if ($listingOther->getMagentoProduct()->getStatus() ==
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                return true;
            }
        }

        if ($listingOther->getChildObject()->getSynchronizationModel()->isStopOutOfStock()) {

            if (!$listingOther->getMagentoProduct()->getStockAvailability()) {
                return true;
            }
        }

        if ($listingOther->getChildObject()->getSynchronizationModel()->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingOther->getChildObject()->getMappedQty();

            if (!is_null($productQty)) {

                /** @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
                $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

                $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueType();
                $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueMin();
                $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueMax();

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_LESS &&
                    $productQty <= $minQty) {
                    return true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_MORE &&
                    $productQty >= $minQty) {
                    return true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_BETWEEN &&
                    $productQty >= $minQty && $productQty <= $maxQty) {
                    return true;
                }
            }
        }
        //--------------------

        return false;
    }

    //####################################
}