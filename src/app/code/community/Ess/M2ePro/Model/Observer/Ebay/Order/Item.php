<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Ebay_Order_Item extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        $itemId = (double)$this->getEvent()->getData('item_id');
        $productId = (int)$this->getEvent()->getData('product_id');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $collection->addFieldToFilter('second_table.item_id',$itemId);

        if ($collection->getSize() > 0 && is_null($collection->getFirstItem()->getData('product_id'))) {

            /** @var $productOtherInstance Ess_M2ePro_Model_Listing_Other */
            $productOtherInstance = $collection->getFirstItem();

            if (!$productOtherInstance->getAccount()->getChildObject()->isOtherListingsSynchronizationEnabled() ||
                !$productOtherInstance->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
                return;
            }

            $productOtherInstance->mapProduct($productId, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        }
    }

    //####################################
}