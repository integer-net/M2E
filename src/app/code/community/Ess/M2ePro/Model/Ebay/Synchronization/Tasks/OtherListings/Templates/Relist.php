<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Relist
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Abstract
{
    const PERCENTS_START = 60;
    const PERCENTS_END = 70;
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
        $this->_profiler->addTitle($componentName.'Relist Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Relist" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Relist" action is finished. Please wait...'));

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

            if (!$this->isMeetRelistRequirements($listingOther)) {
                continue;
            }

            $tempParams = array();
            if ($listingOther->getChildObject()->getSynchronizationModel()->isRelistSendData()) {
                $tempParams = array('all_data'=>true);
            } else {
                $tempParams = array('only_data'=>array('base'=>true));
            }

            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,
                $tempParams
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        // eBay available status
        //--------------------
        if ($listingOther->isListed()) {
            return false;
        }

        if (!$listingOther->isRelistable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        if ($this->_runnerActions->isExistProductAction(
            $listingOther,
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,
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

        if(!$listingOther->getChildObject()->getSynchronizationModel()->isRelistMode()) {
            return false;
        }

        if ($listingOther->getChildObject()->getSynchronizationModel()->isRelistFilterUserLock() &&
            $listingOther->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if($listingOther->getChildObject()->getSynchronizationModel()->isRelistStatusEnabled()) {

            if ($listingOther->getMagentoProduct()->getStatus() !=
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return false;
            }
        }

        if($listingOther->getChildObject()->getSynchronizationModel()->isRelistIsInStock()) {

            if (!$listingOther->getMagentoProduct()->getStockAvailability()) {
                return false;
            }
        }

        if($listingOther->getChildObject()->getSynchronizationModel()->isRelistWhenQtyHasValue()) {

            $productQty = (int)$listingOther->getChildObject()->getMappedQty();

            if (!is_null($productQty)) {

                $result = false;

                /** @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
                $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

                $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueType();
                $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueMin();
                $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueMax();

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_LESS &&
                    $productQty <= $minQty) {
                    $result = true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_MORE &&
                    $productQty >= $minQty) {
                    $result = true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_BETWEEN &&
                    $productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                }

                if (!$result) {
                    return false;
                }
            }
        }
        //--------------------

        return true;
    }

    //####################################
}