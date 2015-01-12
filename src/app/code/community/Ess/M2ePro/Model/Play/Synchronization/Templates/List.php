<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Play_Synchronization_Templates_List
    extends Ess_M2ePro_Model_Play_Synchronization_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/list/';
    }

    protected function getTitle()
    {
        return 'List';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 5;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
        $this->immediatelyNotCheckedProducts();
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was changed');

        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                array()
            );
        }
        //------------------------------------

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function immediatelyNotCheckedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was not checked');

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $collection->addFieldToFilter('tried_to_list',0);

        $collection->getSelect()->where(
            '`is_variation_product` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_PRODUCT_NO.
            ' OR ('.
                '`is_variation_product` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_PRODUCT_YES.
                ' AND `is_variation_matched` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_MATCHED_YES.
            ')'
        );

        $collection->getSelect()->limit(100);

        $listingsProducts = $collection->getItems();

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $listingProduct) {

            $listingProduct->enableCache();
            $listingProduct->setData('tried_to_list',1)->save();

            if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                array()
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}