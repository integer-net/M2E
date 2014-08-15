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
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update quantity');

        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $this->getInspector()->inspectReviseQtyRequirements($listingProduct);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update price');

        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $this->getInspector()->inspectRevisePriceRequirements($listingProduct);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update title');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange,$template->getTitleAttributes());
        }

        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

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

            if ($this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('title'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('title'=>true))
            );
        }

        $changedListingsProducts = $this->getChangesHelper()->getInstancesByVariationOption(
            array('name'), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isListed()) {
                continue;
            }

            if (!$listingProduct->getChildObject()->getEbaySynchronizationTemplate()->isReviseWhenChangeTitle()) {
                continue;
            }

            if ($this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
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

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('variations'=>true))
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeSubTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update subtitle');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange, $template->getSubTitleAttributes());
        }

        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

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

            if ($this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('subtitle'=>true))
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update description');

        $attributesForProductChange = array();
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange,
                                                      $template->getDescriptionAttributes());
        }

        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

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

            if ($this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('description'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('description'=>true))
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

        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

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

            if ($this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('images'=>true)))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                array('only_data'=>array('images'=>true))
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

            /* @var $synchTemplate Ess_M2ePro_Model_Template_Synchronization */
            $synchTemplate = $listingProduct->getChildObject()->getSynchronizationTemplate();

            $isRevise = false;
            foreach ($listingProduct->getSynchReasons() as $reason) {

                $neededSynchTemplate = $synchTemplate;

                if (in_array($reason, array(
                    'categoryTemplate',
                    'otherCategoryTemplate',
                    'descriptionTemplate',
                    'paymentTemplate',
                    'returnTemplate',
                    'shippingTemplate'
                ))) {
                    $neededSynchTemplate = $synchTemplate->getChildObject();
                }

                if ($reason == 'otherCategoryTemplate') {
                    $methodSuffix = 'categoryTemplate';
                } else {
                    $methodSuffix = $reason;
                }

                $method = 'isRevise' . ucfirst($methodSuffix);

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

            if ($this->getRunner()
                     ->isExistProduct(
                            $listingProduct,
                            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
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

            $this->getRunner()
                 ->addProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                        array('all_data'=>true)
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
        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('gt' => $lastListingProductProcessed))
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $collection->getSelect()->limit($itemsPerCycle);
        $collection->getSelect()->order('id ASC');

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($collection->getItems() as $listingProduct) {

            if ($this->getRunner()
                     ->isExistProduct(
                            $listingProduct,
                            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
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

            $this->getRunner()->addProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                    array('all_data'=>true)
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