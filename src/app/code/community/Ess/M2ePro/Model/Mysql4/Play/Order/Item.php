<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Play_Order_Item
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    protected $_isPkAutoIncrement = false;

    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Play_Order_Item', 'order_item_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################

    public function getShippingPriceFromListingProduct(Ess_M2ePro_Model_Play_Order_Item $item)
    {
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $collection->addFieldToFilter('sku', $item->getSku());
        $collection->getSelect()->join(
            array('ml' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.listing_id = ml.id'
                . ' AND ml.account_id = ' . (int)$item->getParentObject()->getOrder()->getAccountId()
                . ' AND ml.marketplace_id = ' . (int)$item->getParentObject()->getOrder()->getMarketplaceId(),
            ''
        );

        $listingProduct = $collection->getFirstItem();
        $shippingPrice = 0;

        if (!$listingProduct->getId()) {
            return $shippingPrice;
        }

        switch ($item->getCurrency()) {
            case Ess_M2ePro_Helper_Component_Play::CURRENCY_GBP:
                $shippingPrice = $listingProduct->getChildObject()->getOnlineShippingPriceGbr();
                break;
            case Ess_M2ePro_Helper_Component_Play::CURRENCY_EUR:
                $shippingPrice = $listingProduct->getChildObject()->getOnlineShippingPriceEuro();
                break;
        }

        return $shippingPrice;
    }

    // ########################################
}