<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Amazon_Order_Item extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        $sku = $this->getEvent()->getData('sku');

        $accountId = (int)$this->getEvent()->getData('account_id');
        $marketplaceId = (int)$this->getEvent()->getData('marketplace_id');
        $productId = (int)$this->getEvent()->getData('product_id');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->addFieldToFilter('second_table.sku',$sku);
        $collection->addFieldToFilter('main_table.account_id',$accountId);
        $collection->addFieldToFilter('main_table.marketplace_id',$marketplaceId);

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