<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Stop
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/stop/';
    }

    protected function getTitle()
    {
        return 'Stop';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 70;
    }

    protected function getPercentsEnd()
    {
        return 75;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when Product was changed');

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            if (!$this->isMeetStopRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                                           array());
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
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

        if ($this->getRunner()->isExistProduct($listingOther,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                                               array())
        ) {
            return false;
        }
        //--------------------

        /** @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
        $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isMode()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if ($ebaySynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingOther->getMagentoProduct()->isStatusEnabled()) {
                return true;
            }
        }

        if ($ebaySynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingOther->getMagentoProduct()->isStockAvailability()) {
                return true;
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyHasValue()) {

            $productQty = $listingOther->getChildObject()->getMappedQty();

            if (!is_null($productQty)) {

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