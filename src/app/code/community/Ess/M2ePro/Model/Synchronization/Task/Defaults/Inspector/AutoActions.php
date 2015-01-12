<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_Inspector_AutoActions
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/inspector/auto_actions/';
    }

    protected function getTitle()
    {
        return 'Auto Actions';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 80;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
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

            $this->processCategoriesActions($magentoProduct);
            $this->processEbayActions($magentoProduct);

            if ((++$tempIndex)%20 == 0) {
                $percentsPerOneItem = $this->getPercentsInterval()/$totalItems;
                $this->getActualLockItem()->setPercents($percentsPerOneItem*$tempIndex);
                $this->getActualLockItem()->activate();
            }
        }

        $lastMagentoProduct = array_pop($magentoProducts);
        $this->setLastProcessedMagentoProductId((int)$lastMagentoProduct->getId());
    }

    //####################################

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

    private function getLastMagentoProductId()
    {
        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->order('entity_id DESC')->limit(1);
        return (int)$collection->getLastItem()->getId();
    }

    private function getMagentoProducts()
    {
        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addFieldToFilter('entity_id', array('gt' => (int)$this->getLastProcessedMagentoProductId()));
        $collection->setOrder('entity_id','asc');
        $collection->getSelect()->limit(100);

        return $collection->getItems();
    }

    // ------------------------------------

    private function getLastProcessedMagentoProductId()
    {
        return $this->getConfigValue($this->getFullSettingsPath(),'last_magento_product_id');
    }

    private function setLastProcessedMagentoProductId($magentoProductId)
    {
        $this->setConfigValue($this->getFullSettingsPath(),'last_magento_product_id',(int)$magentoProductId);
    }

    //####################################
}