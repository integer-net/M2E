<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product_Variation');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getParentObject()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function getAmazonAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace
     */
    public function getAmazonMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getAmazonListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getAmazonSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getAmazonListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Synchronization
     */
    public function getAmazonSynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getOptions($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getOptions($asObjects,$filters);
    }

    // ########################################

    public function getSku()
    {
        $sku = '';

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Grouped product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku = $option->getChildObject()->getSku();
                break;
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku != '' && $sku .= '-';
                $sku .= $option->getChildObject()->getSku();
            }

        // Simple with options product
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku != '' && $sku .= '-';
                $tempSku = $option->getChildObject()->getSku();
                if ($tempSku == '') {
                    $sku .= Mage::helper('M2ePro')->convertStringToSku($option->getOption());
                } else {
                    $sku .= $tempSku;
                }
            }
        }

        if (strlen($sku) <= Ess_M2ePro_Model_Amazon_Listing_Product::SKU_MAX_LENGTH) {
            return $sku;
        }

        $parentSku = $this->getListingProduct()->getChildObject()->getAddingBaseSku();

        if (strlen($parentSku) < (Ess_M2ePro_Model_Amazon_Listing_Product::SKU_MAX_LENGTH - 5)) {
            return $this->getListingProduct()->getChildObject()->createRandomSku($parentSku);
        }

        return $this->getListingProduct()->getChildObject()->createRandomSku(
            'SKU_' . $this->getListingProduct()->getProductId() . '_' . $this->getListingProduct()->getId()
        );
    }

    public function getQty($magentoMode = false)
    {
        $qty = 0;

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Grouped, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $qty = $option->getChildObject()->getQty($magentoMode);
                break;
            }

        // Bundle product
        } else {

            $optionsQtyList = array();
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $optionsQtyList[] = $option->getChildObject()->getQty($magentoMode);
            }

            $qty = min($optionsQtyList);
        }

        if (!$magentoMode) {

            $src = $this->getAmazonSellingFormatTemplate()->getQtySource();

            if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE ||
                $src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
                $src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT) {

                if ($qty > 0 && $src['qty_percentage'] > 0 && $src['qty_percentage'] < 100) {

                    $roundingFunction = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                            ->getGroupValue('/qty/percentage/','rounding_greater') ? 'ceil' : 'floor';

                    $qty = (int)$roundingFunction(($qty/100)*$src['qty_percentage']);
                }

                if ($src['qty_max_posted_value_mode'] && $qty > $src['qty_max_posted_value']) {
                    $qty = $src['qty_max_posted_value'];
                }
            }
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    public function getPrice($returnSalePrice = false)
    {
        $price = 0;

        // Options Models
        $options = $this->getOptions(true);

        if ($returnSalePrice) {
            $src = $this->getAmazonSellingFormatTemplate()->getSalePriceSource();
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getPriceSource();
        }

        // Configurable, Bundle, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            if ($this->getAmazonSellingFormatTemplate()->isPriceVariationModeParent() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

                // Base Price of Main product.
                $price = $this->getAmazonListingProduct()->getBaseProductPrice(
                    $src['mode'],$src['attribute'],$returnSalePrice
                );

                if ($price <= 0 && $returnSalePrice &&
                    $src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL) {
                    $price = 0;
                } else {

                    foreach ($options as $option) {
                        /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                        $price += $option->getChildObject()->getPrice($returnSalePrice);
                    }

                }

            } else {

                $isBundle = $this->getListingProduct()->getMagentoProduct()->isBundleType();

                foreach ($options as $option) {

                    /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                    $tempPrice = $option->getChildObject()->getPrice($returnSalePrice);

                    if ($tempPrice <= 0 && $returnSalePrice &&
                        $src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL) {
                        $price = 0;
                        break;
                    }

                    if ($isBundle) {
                        $price += $tempPrice;
                        continue;
                    }

                    $price = $tempPrice;
                    break;
                }
            }

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $price = $option->getChildObject()->getPrice($returnSalePrice);
                break;
            }
        }

        $price < 0 && $price = 0;

        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    // ########################################
}