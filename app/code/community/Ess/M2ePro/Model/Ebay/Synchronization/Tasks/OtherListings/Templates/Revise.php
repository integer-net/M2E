<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Abstract
{
    const PERCENTS_START = 50;
    const PERCENTS_END = 60;
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
        $this->_profiler->addTitle($componentName.'Revise Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Revise" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Revise" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->executeQtyChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/5);
        $this->_lockItem->activate();

        $this->executePriceChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 2*self::PERCENTS_INTERVAL/5);
        $this->_lockItem->activate();

        //-------------------------

        $this->executeTitleChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 3*self::PERCENTS_INTERVAL/5);
        $this->_lockItem->activate();

        $this->executeSubTitleChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 4*self::PERCENTS_INTERVAL/5);
        $this->_lockItem->activate();

        $this->executeDescriptionChanged();
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update quantity');

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
            $this->inspectReviseQtyRequirements($listingOther);
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update price');

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
            $this->inspectRevisePriceRequirements($listingOther);
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeTitleChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update title');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();
        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');
        if ($tempModel->isTitleSourceProduct()) {
            $attributesForProductChange[] = 'name';
        } else if ($tempModel->isTitleSourceAttribute() && !is_null($tempModel->getTitleAttribute())) {
            $attributesForProductChange[] = $tempModel->getTitleAttribute();
        }
        //------------------------------------

        // Get changed listings others
        //------------------------------------
        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange,
            true
        );
        //------------------------------------

        // Filter only needed listings others
        //------------------------------------
        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            if (!$listingOther->isListed()) {
                return false;
            }

            if (is_null($listingOther->getProductId())) {
                return false;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('title'=>true)))
            ) {
                return false;
            }

            if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
                return false;
            }
            if (!$listingOther->getChildObject()->getSynchronizationModel()->isReviseWhenChangeTitle()) {
                return false;
            }

            if (!$listingOther->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('title'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeSubTitleChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update subtitle');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();
        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');
        if ($tempModel->isSubTitleSourceAttribute() && !is_null($tempModel->getSubTitleAttribute())) {
            $attributesForProductChange[] = $tempModel->getSubTitleAttribute();
        }
        //------------------------------------

        // Get changed listings others
        //------------------------------------
        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange,
            true
        );
        //------------------------------------

        // Filter only needed listings others
        //------------------------------------
        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            if (!$listingOther->isListed()) {
                return false;
            }

            if (is_null($listingOther->getProductId())) {
                return false;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true)))
            ) {
                return false;
            }

            if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
                return false;
            }
            if (!$listingOther->getChildObject()->getSynchronizationModel()->isReviseWhenChangeSubTitle()) {
                return false;
            }

            if (!$listingOther->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update description');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();
        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');
        if ($tempModel->isDescriptionSourceProductMain()) {
            $attributesForProductChange[] = 'description';
        } else if ($tempModel->isDescriptionSourceProductShort()) {
            $attributesForProductChange[] = 'short_description';
        } else if ($tempModel->isDescriptionSourceAttribute() && !is_null($tempModel->getDescriptionAttribute())) {
            $attributesForProductChange[] = $tempModel->getDescriptionAttribute();
        }
        //------------------------------------

        // Get changed listings others
        //------------------------------------
        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange,
            true
        );
        //------------------------------------

        // Filter only needed listings others
        //------------------------------------
        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            if (!$listingOther->isListed()) {
                return false;
            }

            if (is_null($listingOther->getProductId())) {
                return false;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('description'=>true)))
            ) {
                return false;
            }

            if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
                return false;
            }
            if (!$listingOther->getChildObject()->getSynchronizationModel()->isReviseWhenChangeDescription()) {
                return false;
            }

            if (!$listingOther->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('description'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
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

        if ($this->_runnerActions->isExistProductAction(
            $listingOther,
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
            return false;
        }
        if (!$listingOther->getChildObject()->getSynchronizationModel()->isReviseWhenChangeQty()) {
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
            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                $actionParams
            );
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

        if ($this->_runnerActions->isExistProductAction(
            $listingOther,
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingOther->getAccount()->getChildObject()->isOtherListingsMappedSynchronizationEnabled()) {
            return false;
        }
        if (!$listingOther->getChildObject()->getSynchronizationModel()->isReviseWhenChangePrice()) {
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
            $this->_runnerActions->setProduct(
                $listingOther,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                $actionParams
            );
            return true;
        }
        //--------------------

        return false;
    }

    //####################################
}