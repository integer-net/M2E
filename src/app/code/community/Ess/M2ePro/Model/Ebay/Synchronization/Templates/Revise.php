<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
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

        $this->executeTitleChanged();
        $this->executeSubTitleChanged();
        $this->executeDescriptionChanged();
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

            $actionParams = array('only_data'=>array('qty'=>true,'variations'=>true));

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

            $actionParams = array('only_data'=>array('price'=>true,'variations'=>true));

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

    private function executeTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Title');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange,$template->getTitleAttributes());
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $titleAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getTitleAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $titleAttributes)) {
                continue;
            }

            $actionParams = array('only_data'=>array('title'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseTitleRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByVariationOption(
            array('name'), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            $actionParams = array('only_data'=>array('variations'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseVariationTitleRequirements($listingProduct)) {
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

    private function executeSubTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Subtitle');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange, $template->getSubTitleAttributes());
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $subTitleAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getSubTitleAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $subTitleAttributes)) {
                continue;
            }

            $actionParams = array('only_data'=>array('subtitle'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseSubTitleRequirements($listingProduct)) {
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

    private function executeDescriptionChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Description');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getDescriptionAttributes()
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $descriptionAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getDescriptionAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $descriptionAttributes)) {
                continue;
            }

            $actionParams = array('only_data'=>array('description'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseDescriptionRequirements($listingProduct)) {
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
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getImageMainAttributes(),
                $template->getGalleryImagesAttributes()
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $imagesAttributes = array_merge(
                $ebayListingProduct->getEbayDescriptionTemplate()->getImageMainAttributes(),
                $ebayListingProduct->getEbayDescriptionTemplate()->getGalleryImagesAttributes()
            );

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

        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $listingProductCollection->addFieldToFilter('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED);

        $listingProductCollection->getSelect()->limit(100);

        foreach ($listingProductCollection->getItems() as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

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
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute Revise all');

        $lastListingProductProcessed = $this->getConfigValue(
            $this->getFullSettingsPath().'total/','last_listing_product_id'
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
}