<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 20;
    const PERCENTS_END = 35;
    const PERCENTS_INTERVAL = 15;

    private $_synchronizations = array();

    private $_checkedQtyListingsProductsIds = array();
    private $_checkedPriceListingsProductsIds = array();

    //####################################

    public function __construct()
    {
        parent::__construct();
        $this->_synchronizations = Mage::helper('M2ePro')->getGlobalValue('synchTemplatesArray');
    }

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

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        $this->executePriceChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 2*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        //-------------------------

        $this->executeTitleChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 3*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        $this->executeSubTitleChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 4*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        $this->executeDescriptionChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 5*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        //-------------------------

        $this->executeSellingFormatTemplatesChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 6*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        $this->executeDescriptionsTemplatesChanged();

        $this->_lockItem->setPercents(self::PERCENTS_START + 7*self::PERCENTS_INTERVAL/8);
        $this->_lockItem->activate();

        $this->executeGeneralsTemplatesChanged();
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update quantity');

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
            $this->inspectReviseQtyRequirements($listingProduct);
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update price');

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
            $this->inspectRevisePriceRequirements($listingProduct);
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

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->getChildObject()->isReviseWhenChangeTitle()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $attributesForProductChange = array_merge(
                    $attributesForProductChange,
                    $listing->getDescriptionTemplate()->getChildObject()->getTitleAttributes()
                );
            }
        }

        $attributesForProductChange = array_unique($attributesForProductChange);
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstancesByListingProduct(
            $attributesForProductChange,
            true
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('title'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!in_array($listingProduct->getData('changed_attribute'),
                $listingProduct->getDescriptionTemplate()->getChildObject()->getTitleAttributes())) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangeTitle()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('title'=>true))
            );
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstancesByVariationOption(
            array('name'),
            true
        );
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('variations'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            $synchronizationTemplate = $listingProduct->getSynchronizationTemplate();
            if (!$synchronizationTemplate->getChildObject()->isReviseWhenChangeTitle()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('variations'=>true))
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

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->getChildObject()->isReviseWhenChangeSubTitle()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $attributesForProductChange = array_merge(
                    $attributesForProductChange,
                    $listing->getDescriptionTemplate()->getChildObject()->getSubTitleAttributes()
                );
            }
        }

        $attributesForProductChange = array_unique($attributesForProductChange);
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstancesByListingProduct(
            $attributesForProductChange,
            true
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!in_array($listingProduct->getData('changed_attribute'),
                $listingProduct->getDescriptionTemplate()->getChildObject()->getSubTitleAttributes())) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangeSubTitle()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
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

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->getChildObject()->isReviseWhenChangeDescription()) {
                continue;
            }

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $attributesForProductChange = array_merge(
                    $attributesForProductChange,
                    $listing->getDescriptionTemplate()->getChildObject()->getDescriptionAttributes()
                );
            }
        }

        $attributesForProductChange = array_unique($attributesForProductChange);
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstancesByListingProduct(
            $attributesForProductChange,
            true
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$listingProduct->isListed()) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('description'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
                continue;
            }

            if (!in_array($listingProduct->getData('changed_attribute'),
                $listingProduct->getDescriptionTemplate()->getChildObject()->getDescriptionAttributes())) {
                continue;
            }

            if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangeDescription()) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('description'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeSellingFormatTemplatesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update Selling Format Template');

        // Get changed templates
        //------------------------------------
        $templatesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_SellingFormat');
        $templatesCollection->getSelect()->where('`main_table`.`update_date` != `main_table`.`synch_date`');
        $templatesCollection->getSelect()->orWhere('`main_table`.`synch_date` IS NULL');
        $templatesArray = $templatesCollection->toArray();
        //------------------------------------

        // Set eBay actions for listed products
        //------------------------------------
        foreach ($templatesArray['items'] as $templateArray) {

            /** @var $template Ess_M2ePro_Model_Template_SellingFormat */
            $template = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Template_SellingFormat', $templateArray['id'], NULL, array('template')
            );

            $listings = $template->getListings(true);

            foreach ($listings as $listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $listing->setSellingFormatTemplate($template);

                if (!$listing->getSynchronizationTemplate()->isReviseSellingFormatTemplate()) {
                    continue;
                }

                $filters = array('status'=>array('in'=>array(Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)));
                $listingsProducts = $listing->getProducts(true,$filters);

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

                    if (!$listingProduct->isListed()) {
                        continue;
                    }

                    if ($this->_runnerActions->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true))
                    ) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isRevisable()) {
                        continue;
                    }

                    $this->_runnerActions->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                    );
                }
            }

            $template->addData(array('synch_date'=>$template->getData('update_date')))->save();
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionsTemplatesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update description template');

        // Get changed templates
        //------------------------------------
        $templatesCollection = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_Description')->getCollection();
        $templatesCollection->getSelect()->where('`main_table`.`update_date` != `main_table`.`synch_date`');
        $templatesCollection->getSelect()->orWhere('`main_table`.`synch_date` IS NULL');
        $templatesArray = $templatesCollection->toArray();
        //------------------------------------

        // Set eBay actions for listed products
        //------------------------------------
        foreach ($templatesArray['items'] as $templateArray) {

            /** @var $template Ess_M2ePro_Model_Template_Description */
            $template = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Template_Description', $templateArray['id'], NULL, array('template')
            );

            $listings = $template->getListings(true);

            foreach ($listings as $listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $listing->setDescriptionTemplate($template);

                if (!$listing->getSynchronizationTemplate()->isReviseDescriptionTemplate()) {
                    continue;
                }

                $filters = array('status'=>array('in'=>array(Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)));
                $listingsProducts = $listing->getProducts(true,$filters);

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

                    if (!$listingProduct->isListed()) {
                        continue;
                    }

                    if ($this->_runnerActions->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true))
                    ) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isRevisable()) {
                        continue;
                    }

                    $this->_runnerActions->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                    );
                }
            }

            $template->addData(array('synch_date'=>$template->getData('update_date')))->save();
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeGeneralsTemplatesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update general template');

        // Get changed templates
        //------------------------------------
        $templatesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_General');
        $templatesCollection->getSelect()->where('`main_table`.`update_date` != `main_table`.`synch_date`');
        $templatesCollection->getSelect()->orWhere('`main_table`.`synch_date` IS NULL');
        $templatesArray = $templatesCollection->toArray();
        //------------------------------------

        // Set eBay actions for listed products
        //------------------------------------
        foreach ($templatesArray['items'] as $templateArray) {

            /** @var $template Ess_M2ePro_Model_Template_General */
            $template = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Template_General', $templateArray['id'], NULL, array('template')
            );

            $listings = $template->getListings(true);

            foreach ($listings as $listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if (!$listing->isSynchronizationNowRun()) {
                    continue;
                }

                $listing->setGeneralTemplate($template);

                if (!$listing->getSynchronizationTemplate()->isReviseGeneralTemplate()) {
                    continue;
                }

                $filters = array('status'=>array('in'=>array(Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)));
                $listingsProducts = $listing->getProducts(true,$filters);

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

                    if (!$listingProduct->isListed()) {
                        continue;
                    }

                    if ($this->_runnerActions->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true))
                    ) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isRevisable()) {
                        continue;
                    }

                    $this->_runnerActions->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                    );
                }
            }

            $template->addData(array('synch_date'=>$template->getData('update_date')))->save();
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function inspectReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
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
        $actionParams = array('only_data'=>array('qty'=>true,'variations'=>true));
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->_runnerActions->isExistProductAction(
            $listingProduct,
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }
        if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangeQty()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isVariationProduct = $listingProduct->getMagentoProduct()->isProductWithVariations() &&
                              (bool)count($listingProduct->getVariations());
        $maxAppliedValue = $listingProduct->getSynchronizationTemplate()
                                                    ->getChildObject()->getReviseUpdateQtyMaxAppliedValue();

        if (!$isVariationProduct) {

            $ebayListingProduct = $listingProduct->getChildObject();

            $productQty = $ebayListingProduct->getQty();
            $channelQty = $ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold();

            //-- Check ReviseUpdateQtyMaxAppliedValue
            if ($maxAppliedValue > 0 && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
                return false;
            }

            if ($productQty > 0 && $productQty != $channelQty) {
                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                    $actionParams
                );
                return true;
            }

        } else {

            $totalQty = 0;
            $hasChange = false;

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

                $ebayVariation = $variation->getChildObject();

                $productQty = $ebayVariation->getQty();
                $channelQty = $ebayVariation->getOnlineQty() - $ebayVariation->getOnlineQtySold();

                if ($productQty != $channelQty) {
                    //-- Check ReviseUpdateQtyMaxAppliedValue
                    ($maxAppliedValue <= 0 || $productQty <= $maxAppliedValue || $channelQty <= $maxAppliedValue) &&
                        $hasChange = true;
                }

                $totalQty += $productQty;
            }

            if ($totalQty > 0 && $hasChange) {
                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                    $actionParams
                );
                return true;
            }
        }
        //--------------------

        return false;
    }

    private function inspectRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
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
        $actionParams = array('only_data'=>array('price'=>true,'variations'=>true));
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->_runnerActions->isExistProductAction(
            $listingProduct,
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }
        if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangePrice()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isVariationProduct = $listingProduct->getMagentoProduct()->isProductWithVariations() &&
                              (bool)count($listingProduct->getVariations());

        if (!$isVariationProduct) {

            $hasChange = false;

            //---------
            $currentPrice = $listingProduct->getChildObject()->getBuyItNowPrice();
            $onlinePrice = $listingProduct->getChildObject()->getOnlineBuyItNowPrice();

            if ($currentPrice != $onlinePrice) {
                $hasChange = true;
            }

            if ($hasChange) {
                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                    $actionParams
                );
                return true;
            }
            //---------

            if ($listingProduct->getChildObject()->isListingTypeAuction()) {

                //---------
                $currentPrice = $listingProduct->getChildObject()->getStartPrice();
                $onlinePrice = $listingProduct->getChildObject()->getOnlineStartPrice();

                if ($currentPrice != $onlinePrice) {
                    $hasChange = true;
                }

                if ($hasChange) {
                    $this->_runnerActions->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        $actionParams
                    );
                    return true;
                }
                //---------
                $currentPrice = $listingProduct->getChildObject()->getReservePrice();
                $onlinePrice = $listingProduct->getChildObject()->getOnlineReservePrice();

                if ($currentPrice != $onlinePrice) {
                    $hasChange = true;
                }

                if ($hasChange) {
                    $this->_runnerActions->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        $actionParams
                    );
                    return true;
                }
                //---------
            }

        } else {

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                $currentPrice = $variation->getChildObject()->getPrice();
                $onlinePrice = $variation->getChildObject()->getOnlinePrice();

                if ($currentPrice != $onlinePrice) {
                    $this->_runnerActions->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,
                        $actionParams
                    );
                    return true;
                }
            }
        }
        //--------------------

        return false;
    }

    //####################################
}