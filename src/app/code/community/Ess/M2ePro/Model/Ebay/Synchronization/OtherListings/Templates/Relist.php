<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Relist
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/relist/';
    }

    protected function getTitle()
    {
        return 'Relist';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 60;
    }

    protected function getPercentsEnd()
    {
        return 70;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was changed');

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            if (!$this->isMeetRelistRequirements($listingOther)) {
                continue;
            }

            if ($listingOther->getChildObject()->getSynchronizationModel()->isRelistSendData()) {
                $tempParams = array('all_data'=>true);
            } else {
                $tempParams = array('only_data'=>array('base'=>true));
            }

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                                           $tempParams);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
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

        if ($this->getRunner()->isExistProduct($listingOther,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                                               array())
        ) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
        $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

        // Correct synchronization
        //--------------------
        if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
            return false;
        }

        if(!$ebaySynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isRelistFilterUserLock() &&
            $listingOther->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        if($ebaySynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingOther->getMagentoProduct()->isStatusEnabled()) {
                return false;
            }
        }

        if($ebaySynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingOther->getMagentoProduct()->isStockAvailability()) {
                return false;
            }
        }

        if($ebaySynchronizationTemplate->isRelistWhenQtyHasValue()) {

            $productQty = $listingOther->getChildObject()->getMappedQty();

            if (!is_null($productQty)) {

                $result = false;

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