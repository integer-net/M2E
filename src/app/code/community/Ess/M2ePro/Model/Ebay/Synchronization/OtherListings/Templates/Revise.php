<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/revise/';
    }

    protected function getTitle()
    {
        return 'Revise';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 50;
    }

    protected function getPercentsEnd()
    {
        return 60;
    }

    //####################################

    protected function performActions()
    {
        $this->executeQtyChanged();
        $this->executePriceChanged();

        $this->executeTitleChanged();
        $this->executeSubTitleChanged();
        $this->executeDescriptionChanged();
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Quantity');

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {
            $this->inspectReviseQtyRequirements($listingOther);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Price');

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {
            $this->inspectRevisePriceRequirements($listingOther);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Title');

        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');

        $attributesForProductChange = array();
        if ($tempModel->isTitleSourceProduct()) {
            $attributesForProductChange[] = 'name';
        } else if ($tempModel->isTitleSourceAttribute() && !is_null($tempModel->getTitleAttribute())) {
            $attributesForProductChange[] = $tempModel->getTitleAttribute();
        }

        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange, true
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
            $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

            if (!$listingOther->isListed()) {
                return false;
            }

            if (is_null($listingOther->getProductId())) {
                return false;
            }

            if ($this->getRunner()->isExistProduct($listingOther,
                                                   Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                                   array('only_data'=>array('title'=>true)))
            ) {
                return false;
            }

            if (!$ebaySynchronizationTemplate->isMode()) {
                return false;
            }
            if (!$ebaySynchronizationTemplate->isReviseWhenChangeTitle()) {
                return false;
            }

            if (!$listingOther->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           array('only_data'=>array('title'=>true)));
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeSubTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Subtitle');

        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');

        $attributesForProductChange = array();
        if ($tempModel->isSubTitleSourceAttribute() && !is_null($tempModel->getSubTitleAttribute())) {
            $attributesForProductChange[] = $tempModel->getSubTitleAttribute();
        }

        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange, true
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
            $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

            if (!$listingOther->isListed()) {
                return false;
            }

            if (is_null($listingOther->getProductId())) {
                return false;
            }

            if ($this->getRunner()->isExistProduct($listingOther,
                                                   Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                                   array('only_data'=>array('subtitle'=>true)))
            ) {
                return false;
            }

            if (!$ebaySynchronizationTemplate->isMode()) {
                return false;
            }

            if (!$ebaySynchronizationTemplate->isReviseWhenChangeSubTitle()) {
                return false;
            }

            if (!$listingOther->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           array('only_data'=>array('subtitle'=>true)));
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Description');

        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');

        $attributesForProductChange = array();
        if ($tempModel->isDescriptionSourceProductMain()) {
            $attributesForProductChange[] = 'description';
        } else if ($tempModel->isDescriptionSourceProductShort()) {
            $attributesForProductChange[] = 'short_description';
        } else if ($tempModel->isDescriptionSourceAttribute() && !is_null($tempModel->getDescriptionAttribute())) {
            $attributesForProductChange[] = $tempModel->getDescriptionAttribute();
        }

        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange, true
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
            $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

            if (!$listingOther->isListed()) {
                return false;
            }

            if (is_null($listingOther->getProductId())) {
                return false;
            }

            if ($this->getRunner()->isExistProduct($listingOther,
                                                   Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                                   array('only_data'=>array('description'=>true)))
            ) {
                return false;
            }

            if (!$ebaySynchronizationTemplate->isMode()) {
                return false;
            }

            if (!$ebaySynchronizationTemplate->isReviseWhenChangeDescription()) {
                return false;
            }

            if (!$listingOther->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           array('only_data'=>array('description'=>true)));
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function inspectReviseQtyRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('qty'=>true));
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingOther->isListed()) {
            return false;
        }

        if (!$listingOther->isRevisable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingOther,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                               $actionParams)
        ) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
        $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isMode()) {
            return false;
        }

        if (!$ebaySynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $ebayListingOther = $listingOther->getChildObject();

        $productQty = $ebayListingOther->getMappedQty();

        if (is_null($productQty)) {
            return false;
        }

        $channelQty = $ebayListingOther->getOnlineQty() - $ebayListingOther->getOnlineQtySold();

        if ($productQty > 0 && $productQty != $channelQty) {

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }
        //--------------------

        return false;
    }

    private function inspectRevisePriceRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('price'=>true));
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingOther->isListed()) {
            return false;
        }

        if (!$listingOther->isRevisable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        if ($this->getRunner()->isExistProduct($listingOther,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                               $actionParams)
        ) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization */
        $ebaySynchronizationTemplate = $listingOther->getChildObject()->getSynchronizationModel();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isMode()) {
            return false;
        }

        if (!$ebaySynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $ebayListingOther = $listingOther->getChildObject();

        $currentPrice = $ebayListingOther->getMappedPrice();

        if (is_null($currentPrice)) {
            return false;
        }

        $onlinePrice = $ebayListingOther->getOnlinePrice();

        if ($currentPrice != $onlinePrice) {

            $this->getRunner()->addProduct($listingOther,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           $actionParams);
            return true;
        }
        //--------------------

        return false;
    }

    //####################################
}