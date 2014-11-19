<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Ebay_Order_Item
{
    //####################################

    public function associateItemWithProduct(Varien_Event_Observer $observer)
    {
        try {

            $itemId = (double)$observer->getEvent()->getData('item_id');
            $productId = (int)$observer->getEvent()->getData('product_id');

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

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################
}