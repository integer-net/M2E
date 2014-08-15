<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Play_Synchronization_Templates_Revise
    extends Ess_M2ePro_Model_Play_Synchronization_Templates_Abstract
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

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {
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

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {
            $this->getInspector()->inspectRevisePriceRequirements($listingProduct);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeNeedSynchronize()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute is need synchronize');

        $listingProductCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $listingProductCollection->addFieldToFilter('synch_status', Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED);

        $listingProductCollection->getSelect()->where(
            '`is_variation_product` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_PRODUCT_NO.
            ' OR ('.
                '`is_variation_product` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_PRODUCT_YES.
                ' AND `is_variation_matched` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_MATCHED_YES.
            ')'
        );

        $listingProductCollection->getSelect()->limit(100);

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingProductCollection->getItems() as $listingProduct) {

            $listingProduct->setData('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP)->save();

            /* @var $synchTemplate Ess_M2ePro_Model_Template_Synchronization */
            $synchTemplate = $listingProduct->getListing()->getChildObject()->getSynchronizationTemplate();

            $isRevise = false;
            foreach ($listingProduct->getSynchReasons() as $reason) {

                $method = 'isRevise' . ucfirst($reason);

                if (!method_exists($synchTemplate,$method)) {
                    continue;
                }

                if ($synchTemplate->$method()) {
                    $isRevise = true;
                    break;
                }
            }

            if (!$isRevise) {
                continue;
            }

            if ($this->getRunner()->isExistProduct($listingProduct,
                                                   Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                                   array('all_data'=>true))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action')) {
                continue;
            }

            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           array('all_data'=>true));
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

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
        $collection = Mage::helper('M2ePro/Component_Play')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('gt' => $lastListingProductProcessed))
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $collection->getSelect()->limit($itemsPerCycle);
        $collection->getSelect()->order('id ASC');

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($collection->getItems() as $listingProduct) {

            if ($this->getRunner()->isExistProduct($listingProduct,
                                                   Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                                   array('all_data'=>true))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action')) {
                continue;
            }

            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                                           array('all_data'=>true));
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