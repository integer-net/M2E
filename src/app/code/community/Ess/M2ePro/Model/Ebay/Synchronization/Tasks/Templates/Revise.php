<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 20;
    const PERCENTS_END = 35;
    const PERCENTS_INTERVAL = 15;

    private $_checkedQtyListingsProductsIds = array();
    private $_checkedPriceListingsProductsIds = array();

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
        $tasks = array(
            'executeQtyChanged',
            'executePriceChanged',
            'executeTitleChanged',
            'executeSubTitleChanged',
            'executeDescriptionChanged',
            'executeImagesChanged',
            'executeNeedSynchronize',
            'executeTotal'
        );

        foreach ($tasks as $i => $task) {
            $this->$task();

            $this->_lockItem->setPercents(self::PERCENTS_START + ($i+1)*self::PERCENTS_INTERVAL/count($tasks));
            $this->_lockItem->activate();
        }
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

        $collection = Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection();
        $templates = $collection->getItems();

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();

        foreach ($templates as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getTitleAttributes()
            );
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

            if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeTitle()) {
                continue;
            }

            if (!in_array($listingProduct->getData('changed_attribute'),
                $listingProduct->getChildObject()->getDescriptionTemplate()->getTitleAttributes())) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('title'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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

            if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeTitle()) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('variations'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if (!$listingProduct->getMagentoProduct()->isBundleType() &&
                !$listingProduct->getMagentoProduct()->isGroupedType()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('variations'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeSubTitleChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update subtitle');

        $collection = Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection();
        $templates = $collection->getItems();

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();

        foreach ($templates as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getSubTitleAttributes()
            );
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

            if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeSubTitle()) {
                continue;
            }

            if (!in_array($listingProduct->getData('changed_attribute'),
                $listingProduct->getChildObject()->getDescriptionTemplate()->getSubTitleAttributes())) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update description');

        $collection = Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection();
        $templates = $collection->getItems();

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();

        foreach ($templates as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getDescriptionAttributes()
            );
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

            if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeDescription()) {
                continue;
            }

            if (!in_array($listingProduct->getData('changed_attribute'),
                $listingProduct->getChildObject()->getDescriptionTemplate()->getDescriptionAttributes())) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('description'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('description'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeImagesChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update images');

        $collection = Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection();
        $templates = $collection->getItems();

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();

        foreach ($templates as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getImageMainAttributes(),
                $template->getGalleryImagesAttributes()
            );
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

            if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeImages()) {
                continue;
            }

            $listingProductImageAttributes = array_merge(
                $listingProduct->getChildObject()->getDescriptionTemplate()->getImageMainAttributes(),
                $listingProduct->getChildObject()->getDescriptionTemplate()->getGalleryImagesAttributes()
            );

            if (!in_array($listingProduct->getData('changed_attribute'), $listingProductImageAttributes)) {
                continue;
            }

            if ($this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('images'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                array('only_data'=>array('images'=>true))
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeNeedSynchronize()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Execute is need synchronize');

        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $listingProductCollection->addFieldToFilter('synch_status', Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED);

        $listingProductCollection->getSelect()->limit(100);

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingProductCollection->getItems() as $listingProduct) {

            $listingProduct->setData('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP)->save();

            /* @var $synchTemplate Ess_M2ePro_Model_Template_Synchronization */
            $synchTemplate = $listingProduct->getChildObject()->getSynchronizationTemplate();

            $isRevise = false;
            foreach ($listingProduct->getSynchReasons() as $reason) {

                $neededSynchTemplate = $synchTemplate;

                if (in_array($reason, array(
                    'categoryTemplate',
                    'paymentTemplate',
                    'returnTemplate',
                    'shippingTemplate',
                    'descriptionTemplate'
                ))) {
                    $neededSynchTemplate = $synchTemplate->getChildObject();
                }

                $method = 'isRevise' . ucfirst($reason);

                if (!method_exists($neededSynchTemplate,$method)) {
                    continue;
                }

                if ($neededSynchTemplate->$method()) {
                    $isRevise = true;
                    break;
                }
            }

            if (!$isRevise) {
                continue;
            }

            if ($this->_runnerActions
                     ->isExistProductAction(
                            $listingProduct,
                            Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                            array('all_data'=>true))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action')) {
                continue;
            }

            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                 );
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeTotal()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Execute revise all');

        $lastListingProductProcessed = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/ebay/templates/revise/total/','last_listing_product_id'
        );
        if (is_null($lastListingProductProcessed)) {
            return;
        }

        $itemsPerCycle = 100;

        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('gt' => $lastListingProductProcessed))
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $collection->getSelect()->limit($itemsPerCycle);
        $collection->getSelect()->order('id ASC');

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($collection->getItems() as $listingProduct) {

            if ($this->_runnerActions
                     ->isExistProductAction(
                            $listingProduct,
                            Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                            array('all_data'=>true))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action')) {
                continue;
            }

            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                 );
        }

        $lastListingProduct = $collection->getLastItem()->getId();
        if ($collection->count() < $itemsPerCycle) {
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                '/ebay/templates/revise/total/','end_date', Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $lastListingProduct = NULL;
        }

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/ebay/templates/revise/total/','last_listing_product_id', $lastListingProduct
        );

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

        if (!$listingProduct->isRevisable() || $listingProduct->isHidden()) {
            return false;
        }

        if ($this->_runnerActions->isExistProductAction(
            $listingProduct,
            Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if(!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeQty()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isVariationProduct = $listingProduct->getChildObject()->isVariationMode() &&
                              count($listingProduct->getVariations()) > 0;

        $isMaxAppliedValueModeOn = $listingProduct->getChildObject()->getEbaySynchronizationTemplate()
                                                    ->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $listingProduct->getChildObject()->getEbaySynchronizationTemplate()
                                                    ->getReviseUpdateQtyMaxAppliedValue();

        if (!$isVariationProduct) {

            $ebayListingProduct = $listingProduct->getChildObject();

            $productQty = $ebayListingProduct->getQty();
            $channelQty = $ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold();

            //-- Check ReviseUpdateQtyMaxAppliedValue
            if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
                return false;
            }

            if ($productQty > 0 && $productQty != $channelQty) {
                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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
                    (!$isMaxAppliedValueModeOn || $productQty <= $maxAppliedValue || $channelQty <= $maxAppliedValue) &&
                        $hasChange = true;
                }

                $totalQty += $productQty;
            }

            if ($totalQty > 0 && $hasChange) {
                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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
            Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
            $actionParams)
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if(!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangePrice()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isVariationProduct = $listingProduct->getChildObject()->isVariationMode() &&
                              count($listingProduct->getVariations()) > 0;

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
                    Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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
                        Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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
                        Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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
                        Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE,
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