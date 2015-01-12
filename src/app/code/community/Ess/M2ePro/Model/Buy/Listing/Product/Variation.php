<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Product_Variation');
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
     * @return Ess_M2ePro_Model_Buy_Listing
     */
    public function getBuyListing()
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
     * @return Ess_M2ePro_Model_Buy_Listing_Product
     */
    public function getBuyListingProduct()
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
     * @return Ess_M2ePro_Model_Buy_Account
     */
    public function getBuyAccount()
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
     * @return Ess_M2ePro_Model_Buy_Marketplace
     */
    public function getBuyMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getBuyListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_SellingFormat
     */
    public function getBuySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getBuyListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Synchronization
     */
    public function getBuySynchronizationTemplate()
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

        if (strlen($sku) <= Ess_M2ePro_Model_Buy_Listing_Product::SKU_MAX_LENGTH) {
            return $sku;
        }

        $parentSku = $this->getListingProduct()->getChildObject()->getAddingBaseSku();

        if (strlen($parentSku) < (Ess_M2ePro_Model_Buy_Listing_Product::SKU_MAX_LENGTH - 5)) {
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

            $src = $this->getBuySellingFormatTemplate()->getQtySource();

            if ($src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE ||
                $src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
                $src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT) {

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

    // ########################################

    public function getPrice()
    {
        $price = 0;

        // Options Models
        $options = $this->getOptions(true);

        $src = $this->getBuySellingFormatTemplate()->getPriceSource();

        // Configurable, Bundle, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            if ($this->getBuySellingFormatTemplate()->isPriceVariationModeParent() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

                // Base Price of Main product.
                $price = $this->getBuyListingProduct()->getBaseProductPrice(
                    $src['mode'],$src['attribute']
                );

                foreach ($options as $option) {
                    /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                    $price += $option->getChildObject()->getPrice();
                }

            } else {

                $isBundle = $this->getListingProduct()->getMagentoProduct()->isBundleType();

                foreach ($options as $option) {

                    /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                    if ($isBundle) {
                        $price += $option->getChildObject()->getPrice();
                        continue;
                    }

                    $price = $option->getChildObject()->getPrice();
                    break;
                }
            }

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $price = $option->getChildObject()->getPrice();
                break;
            }
        }

        $price < 0 && $price = 0;

        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    // ########################################
}