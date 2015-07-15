<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Revise
    extends Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
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
        return 5;
    }

    protected function getPercentsEnd()
    {
        return 20;
    }

    //####################################

    protected function performActions()
    {
        $this->executeQtyChanged();
        $this->executePriceChanged();

        $this->executeDetailsChanged();
        $this->executeImagesChanged();

        $this->executeNeedSynchronize();
        $this->executeTotal();
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Quantity');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            $actionParams = array('only_data'=>array('qty'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseQtyRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Price');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {
            $actionParams = array('only_data'=>array('price'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRevisePriceRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeDetailsChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update details');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Amazon_Template_Description')->getCollection() as $template) {

            /** @var Ess_M2ePro_Model_Amazon_Template_Description $template */

            $attributes = $template->getDefinitionTemplate()->getUsedDetailsAttributes();

            $specifics = $template->getSpecifics(true);
            foreach ($specifics as $specific) {
                $attributes = array_merge($attributes,$specific->getUsedAttributes());
            }

            $attributesForProductChange = array_merge($attributesForProductChange,$attributes);
        }

        foreach (Mage::getModel('M2ePro/Amazon_Listing')->getCollection() as $listing) {

            /** @var Ess_M2ePro_Model_Amazon_Listing $listing */

            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $listing->getConditionNoteAttributes(),
                $listing->getGiftWrapAttributes(),
                $listing->getGiftMessageAttributes()
            );
        }

        foreach ($this->getChangedListingsProducts($attributesForProductChange) as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $detailsAttributes = array_merge(
                $amazonListingProduct->getAmazonListing()->getConditionNoteAttributes(),
                $amazonListingProduct->getAmazonListing()->getGiftWrapAttributes(),
                $amazonListingProduct->getAmazonListing()->getGiftMessageAttributes()
            );

            if ($amazonListingProduct->isExistDescriptionTemplate()) {
                $descriptionTemplateDetailsAttributes = $amazonListingProduct->getAmazonDescriptionTemplate()
                    ->getDefinitionTemplate()
                    ->getUsedDetailsAttributes();

                $specifics = $amazonListingProduct->getAmazonDescriptionTemplate()->getSpecifics(true);
                foreach ($specifics as $specific) {
                    $descriptionTemplateDetailsAttributes = array_merge(
                        $descriptionTemplateDetailsAttributes, $specific->getUsedAttributes()
                    );
                }

                $detailsAttributes = array_merge(
                    $detailsAttributes,
                    $descriptionTemplateDetailsAttributes
                );
            }

            if (!in_array($listingProduct->getData('changed_attribute'), $detailsAttributes)) {
                continue;
            }

            $actionParams = array('only_data'=>array('details'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseDetailsRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeImagesChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update images');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Amazon_Template_Description')->getCollection() as $template) {

            /** @var Ess_M2ePro_Model_Amazon_Template_Description $template */

            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getDefinitionTemplate()->getUsedImagesAttributes()
            );
        }

        foreach (Mage::getModel('M2ePro/Amazon_Listing')->getCollection() as $listing) {

            /** @var Ess_M2ePro_Model_Amazon_Listing $listing */

            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $listing->getImageMainAttributes(),
                $listing->getGalleryImagesAttributes()
            );
        }

        foreach ($this->getChangedListingsProducts($attributesForProductChange) as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $amazonListing = $amazonListingProduct->getAmazonListing();

            $imagesAttributes = array_merge(
                $amazonListing->getImageMainAttributes(),
                $amazonListing->getGalleryImagesAttributes()
            );

            if ($amazonListingProduct->isExistDescriptionTemplate()) {
                $amazonDescriptionTemplate = $amazonListingProduct->getAmazonDescriptionTemplate();
                $imagesAttributes = array_merge(
                    $imagesAttributes,
                    $amazonDescriptionTemplate->getDefinitionTemplate()->getUsedImagesAttributes()
                );
            }

            if (!in_array($listingProduct->getData('changed_attribute'), $imagesAttributes)) {
                continue;
            }

            $actionParams = array('only_data'=>array('images'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseImagesRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeNeedSynchronize()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute is need synchronize');

        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $listingProductCollection->addFieldToFilter('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED);
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);

        $listingProductCollection->getSelect()->limit(100);

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingProductCollection->getItems() as $listingProduct) {

            $listingProduct->setData('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP)->save();

            $actionParams = array('all_data'=>true);

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseSynchReasonsRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeTotal()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute revise all');

        $lastListingProductProcessed = $this->getConfigValue(
            $this->getFullSettingsPath().'total/','last_listing_product_id'
        );

        if (is_null($lastListingProductProcessed)) {
            return;
        }

        $itemsPerCycle = 100;

        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('gt' => $lastListingProductProcessed))
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)
            ->addFieldToFilter('is_variation_parent', 0);

        $collection->getSelect()->limit($itemsPerCycle);
        $collection->getSelect()->order('id ASC');

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($collection->getItems() as $listingProduct) {

            $actionParams = array('all_data'=>true);

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseGeneralRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        $lastListingProduct = $collection->getLastItem()->getId();

        if ($collection->count() < $itemsPerCycle) {

            $this->setConfigValue(
                $this->getFullSettingsPath().'total/', 'end_date',
                Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $lastListingProduct = NULL;
        }

        $this->setConfigValue(
            $this->getFullSettingsPath().'total/', 'last_listing_product_id',
            $lastListingProduct
        );

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    /**
     * @param array $trackingAttributes
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    private function getChangedListingsProducts(array $trackingAttributes)
    {
        $filteredChangedListingsProducts = array();

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($trackingAttributes), true
        );

        foreach ($changedListingsProducts as $changedListingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $changedListingProduct->getChildObject();

            if ($amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $magentoProduct = $changedListingProduct->getMagentoProduct();

            if ($magentoProduct->isConfigurableType() || $magentoProduct->isGroupedType()) {
                continue;
            }

            $filteredChangedListingsProducts[$changedListingProduct->getId()] = $changedListingProduct;
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByVariationOption(
            array_unique($trackingAttributes), true
        );

        foreach ($changedListingsProducts as $changedListingProduct) {
            $magentoProduct = $changedListingProduct->getMagentoProduct();

            if ($magentoProduct->isSimpleTypeWithCustomOptions() || $magentoProduct->isBundleType()) {
                continue;
            }

            $filteredChangedListingsProducts[$changedListingProduct->getId()] = $changedListingProduct;
        }

        return $filteredChangedListingsProducts;
    }

    //####################################
}