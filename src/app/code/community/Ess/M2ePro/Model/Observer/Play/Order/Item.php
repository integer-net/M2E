<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Play_Order_Item
{
    //####################################

    public function associateItemWithProduct(Varien_Event_Observer $observer)
    {
        try {

            $sku = $observer->getEvent()->getData('sku');
            $accountId = (int)$observer->getEvent()->getData('account_id');
            $marketplaceId = (int)$observer->getEvent()->getData('marketplace_id');
            $productId = (int)$observer->getEvent()->getData('product_id');

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Other');
            $collection->addFieldToFilter('main_table.account_id',$accountId);
            $collection->addFieldToFilter('main_table.marketplace_id',$marketplaceId);
            $collection->addFieldToFilter('second_table.sku',$sku);

            if ($collection->getSize() > 0 && is_null($collection->getFirstItem()->getData('product_id'))) {
                /** @var $productOtherInstance Ess_M2ePro_Model_Listing_Other */
                $productOtherInstance = $collection->getFirstItem();
                $productOtherInstance->mapProduct($productId, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################
}