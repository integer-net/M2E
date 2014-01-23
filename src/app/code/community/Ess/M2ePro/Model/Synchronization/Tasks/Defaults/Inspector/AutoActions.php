<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_Inspector_AutoActions
    extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 85;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 15;

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

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Auto Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Auto Actions" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Auto Actions" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        if (is_null($this->getLastProcessedMagentoProductId())) {
            $this->setLastProcessedMagentoProductId($this->getLastMagentoProductId());
        }

        if (count($magentoProducts = $this->getMagentoProducts()) <= 0) {
            return;
        }

        $tempIndex = 0;
        $totalItems = count($magentoProducts);

        foreach ($magentoProducts as $magentoProduct) {

            $this->processMagentoProduct($magentoProduct);

            if ((++$tempIndex)%20 == 0) {
                $percentsPerOneItem = self::PERCENTS_INTERVAL/$totalItems;
                $this->_lockItem->setPercents($percentsPerOneItem*$tempIndex);
                $this->_lockItem->activate();
            }
        }

        $lastMagentoProduct = array_pop($magentoProducts);
        $this->setLastProcessedMagentoProductId((int)$lastMagentoProduct->getId());
    }

    //####################################

    private function getLastMagentoProductId()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->order('entity_id DESC')->limit(1);
        return (int)$collection->getLastItem()->getId();
    }

    private function getMagentoProducts()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addFieldToFilter('entity_id', array('gt' => (int)$this->getLastProcessedMagentoProductId()));
        $collection->setOrder('entity_id','asc');
        $collection->getSelect()->limit(100);

        return $collection->getItems();
    }

    // ------------------------------------

    private function getLastProcessedMagentoProductId()
    {
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/auto_actions/', 'last_magento_product_id'
        );
    }

    private function setLastProcessedMagentoProductId($magentoProductId)
    {
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/defaults/inspector/auto_actions/', 'last_magento_product_id', (int)$magentoProductId
        );
    }

    //####################################

    private function processMagentoProduct(Mage_Catalog_Model_Product $magentoProduct)
    {
        $this->processCategoriesActions($magentoProduct);
        $this->processEbayActions($magentoProduct);
    }

    //-----------------------------------

    private function processCategoriesActions(Mage_Catalog_Model_Product $magentoProduct)
    {
        $productCategories = $magentoProduct->getCategoryIds();

        $categoriesByWebsite = array(
            0 => $productCategories // website for default store view
        );

        foreach ($magentoProduct->getWebsiteIds() as $websiteId) {
            $categoriesByWebsite[$websiteId] = $productCategories;
        }

        /** @var Ess_M2ePro_Model_Observer_Category $categoryObserverModel */
        $categoryObserverModel = Mage::getModel('M2ePro/Observer_Category');

        /** @var Ess_M2ePro_Model_Observer_Ebay_Category $ebayCategoryObserver */
        $ebayCategoryObserver = Mage::getModel('M2ePro/Observer_Ebay_Category');

        foreach ($categoriesByWebsite as $websiteId => $categoriesIds) {
            foreach ($categoriesIds as $categoryId) {
                $categoryObserverModel->synchProductWithAddedCategoryId($magentoProduct,$categoryId,$websiteId);
                $ebayCategoryObserver->synchProductWithAddedCategoryId($magentoProduct,$categoryId,$websiteId);
            }
        }
    }

    private function processEbayActions(Mage_Catalog_Model_Product $magentoProduct)
    {
        /** @var Ess_M2ePro_Model_Observer_Ebay_Product $ebayObserver */
        $ebayObserver = Mage::getModel('M2ePro/Observer_Ebay_Product');

        $ebayObserver->tryToPerformGlobalProductActions($magentoProduct);
        $ebayObserver->tryToPerformWebsiteProductActions($magentoProduct, true, array());
    }

    //####################################
}