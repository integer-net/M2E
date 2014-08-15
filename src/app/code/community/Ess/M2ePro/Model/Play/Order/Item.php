<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Order_Item getParentObject()
 */
class Ess_M2ePro_Model_Play_Order_Item extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    // M2ePro_TRANSLATIONS
    // Product import is disabled in Play Account settings.
    // Product for Play Item "%id%" was created in Magento catalog.
    // Product for Play Item "%title%" was created in Magento catalog.

    // ########################################

    /** @var $channelItem Ess_M2ePro_Model_Play_Item */
    private $channelItem = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Order_Item');
    }

    // ########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Play_Order_Item_Proxy', $this);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Play_Order
     */
    public function getPlayOrder()
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    public function getPlayAccount()
    {
        return $this->getPlayOrder()->getPlayAccount();
    }

    // ########################################

    public function getChannelItem()
    {
        if (is_null($this->channelItem)) {
            $this->channelItem = Mage::getModel('M2ePro/Play_Item')->getCollection()
                ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
                ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
                ->addFieldToFilter('sku', $this->getSku())
                ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();
        }

        return !is_null($this->channelItem->getId()) ? $this->channelItem : NULL;
    }

    // ########################################

    public function getPlayOrderItemId()
    {
        return $this->getData('play_order_item_id');
    }

    public function getListingId()
    {
        return $this->getData('listing_id');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getPrice()
    {
        return (float)$this->getData('price');
    }

    public function getFee()
    {
        return (float)$this->getData('fee');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getQtyPurchased()
    {
        return (int)$this->getData('qty');
    }

    public function getRepairInput()
    {
        return array(
            'SKU' => trim($this->getSku())
        );
    }

    public function getVariation()
    {
        $playItem = $this->getChannelItem();

        if (is_null($playItem)) {
            return array();
        }

        return $playItem->getVariationOptions();
    }

    // ########################################

    public function getAssociatedStoreId()
    {
        // Item was listed by M2E
        // ----------------
        if (!is_null($this->getChannelItem())) {
            return $this->getPlayAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getPlayAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }
        // ----------------

        return $this->getPlayAccount()->getMagentoOrdersListingsOtherStoreId();
    }

    // ########################################

    public function getAssociatedProductId()
    {
        $this->validate();

        // Item was listed by M2E
        // ----------------
        if (!is_null($this->getChannelItem())) {
            return $this->getChannelItem()->getProductId();
        }
        // ----------------

        // 3rd party Item
        // ----------------
        $sku = $this->getSku();
        if ($sku != '' && strlen($sku) <= 64) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getPlayOrder()->getAssociatedStoreId())
                ->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('sku', $sku)
                    ->getFirstItem();

            if ($product->getId()) {
                Mage::dispatchEvent('m2epro_associate_play_order_item_to_product', array(
                    'product_id'     => $product->getId(),
                    'sku'            => $sku,
                    'account_id'     => $this->getParentObject()->getOrder()->getAccountId(),
                    'marketplace_id' => $this->getParentObject()->getOrder()->getMarketplaceId()
                ));

                return $product->getId();
            }
        }
        // ----------------

        $product = $this->createProduct();

        Mage::dispatchEvent('m2epro_associate_play_order_item_to_product', array(
            'product_id'     => $product->getId(),
            'sku'            => $sku,
            'account_id'     => $this->getParentObject()->getOrder()->getAccountId(),
            'marketplace_id' => $this->getParentObject()->getOrder()->getMarketplaceId()
        ));

        return $product->getId();
    }

    private function validate()
    {
        $channelItem = $this->getChannelItem();

        if (!is_null($channelItem) && !$this->getPlayAccount()->isMagentoOrdersListingsModeEnabled()) {
            throw new Exception(
                'Magento Order creation for items listed by M2E Pro is disabled in Account settings.'
            );
        }

        if (is_null($channelItem) && !$this->getPlayAccount()->isMagentoOrdersListingsOtherModeEnabled()) {
            throw new Exception(
                'Magento Order creation for items listed by 3rd party software is disabled in Account settings.'
            );
        }
    }

    private function createProduct()
    {
        if (!$this->getPlayAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new Exception('Product import is disabled in Play Account settings.');
        }

        $storeId = $this->getPlayAccount()->getMagentoOrdersListingsOtherStoreId();
        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        $sku = $this->getSku();
        if (strlen($sku) > 64) {
            $sku = substr($sku, strlen($sku) - 64, 64);

            // Try to find exist product with truncated sku
            // ----------------
            $product = Mage::getModel('catalog/product')
                ->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('sku', $sku)
                    ->getFirstItem();

            if ($product->getId()) {
                return $product;
            }
            // ----------------
        }

        $productData = array(
            'title'             => $this->getTitle(),
            'sku'               => $sku,
            'description'       => '',
            'short_description' => '',
            'qty'               => $this->getQtyForNewProduct(),
            'price'             => $this->getPrice(),
            'store_id'          => $storeId,
            'tax_class_id'      => $this->getPlayAccount()->getMagentoOrdersListingsOtherProductTaxClassId()
        );

        // Create product in magento
        // ----------------
        /** @var $productBuilder Ess_M2ePro_Model_Magento_Product_Builder */
        $productBuilder = Mage::getModel('M2ePro/Magento_Product_Builder')->setData($productData);
        $productBuilder->buildProduct();
        // ----------------

        $this->getParentObject()->getOrder()->addSuccessLog(
            'Product for Play Item "%title%" was created in Magento catalog.', array('!title' => $this->getTitle())
        );

        return $productBuilder->getProduct();
    }

    private function getQtyForNewProduct()
    {
        $otherListing = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Other')
            ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
            ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
            ->addFieldToFilter('sku', $this->getSku())
            ->getFirstItem();

        if ((int)$otherListing->getOnlineQty() > $this->getQtyPurchased()) {
            return $otherListing->getOnlineQty();
        }

        return $this->getQtyPurchased();
    }

    // ########################################

    public function updateShippingStatus(array $trackingDetails = array(), $creatorType)
    {
        $params = array(
            'play_order_id'   => $this->getPlayOrderItemId(),
            'tracking_number' => null,
            'carrier_name'    => null
        );

        if (!empty($trackingDetails['carrier_code'])
            && !empty($trackingDetails['carrier_title'])
            && !empty($trackingDetails['tracking_number'])
        ) {
            $carrierName = Mage::helper('M2ePro/Component_Play')->getCarrierTitle(
                $trackingDetails['carrier_code'], $trackingDetails['carrier_title']
            );

            if ($carrierName) {
                $params['tracking_number'] = $trackingDetails['tracking_number'];
                $params['carrier_name'] = $carrierName;
            }
        }

        $change = array(
            'component'    => Ess_M2ePro_Helper_Component_Play::NICK,
            'order_id'     => $this->getParentObject()->getOrderId(),
            'action'       => Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING,
            'creator_type' => $creatorType,
            'params'       => json_encode($params)
        );

        Mage::getModel('M2ePro/Order_Change')->setData($change)->save();

        return true;
    }

    // ########################################
}