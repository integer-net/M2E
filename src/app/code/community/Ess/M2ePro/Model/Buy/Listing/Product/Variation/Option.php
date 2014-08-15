<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation_Option getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing_Product_Variation_Option extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Product_Variation_Option');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    //-----------------------------------------

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
     * @return Ess_M2ePro_Model_Listing_Product_Variation
     */
    public function getListingProductVariation()
    {
        return $this->getParentObject()->getListingProductVariation();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Variation
     */
    public function getBuyListingProductVariation()
    {
        return $this->getListingProductVariation()->getChildObject();
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
        return $this->getBuyListingProductVariation()->getSellingFormatTemplate();
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
        return $this->getBuyListingProductVariation()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Synchronization
     */
    public function getBuySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getSku()
    {
        $src = $this->getBuyListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_PRODUCT_ID) {
            return (string)$this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            return $this->getMagentoProduct()->getSku();
        }

        $tempSku = '';

        $simpleAttributes = $this->getListingProduct()->getMagentoProduct()->getProduct()->getOptions();

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            $attribute = strtolower($this->getParentObject()->getAttribute());

            if (strtolower($tempAttribute->getData('default_title')) != $attribute &&
                strtolower($tempAttribute->getData('store_title')) != $attribute &&
                strtolower($tempAttribute->getData('title')) != $attribute) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                $option = strtolower($this->getParentObject()->getOption());

                if (strtolower($tempOption->getData('default_title')) != $option &&
                    strtolower($tempOption->getData('store_title')) != $option &&
                    strtolower($tempOption->getData('title')) != $option) {
                    continue;
                }

                if (!is_null($tempOption->getData('sku')) &&
                    $tempOption->getData('sku') !== false) {
                    $tempSku = $tempOption->getData('sku');
                }

                break 2;
            }
        }

        return trim($tempSku);
    }

    public function getQty($magentoMode = false)
    {
        $qty = 0;
        $src = $this->getBuySellingFormatTemplate()->getQtySource();

        if ($magentoMode || $src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT) {
            if (!$this->getListingProduct()->getMagentoProduct()->isStatusEnabled() ||
                !$this->getListingProduct()->getMagentoProduct()->isStockAvailability()) {
                return 0;
            }
        }

        if ($magentoMode) {
            return (int)$this->getMagentoProduct()->getQty(true);
        }

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $qty = (int)$this->getMagentoProduct()->getQty(false);
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    public function getPrice()
    {
        $price = 0;

        $src = $this->getBuySellingFormatTemplate()->getPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        // Configurable product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType()) {

            if ($this->getBuySellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getConfigurablePriceParent();
            } else {
                $price = $this->getBaseProductPrice();
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            if ($this->getBuySellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getBundlePriceParent();
            } else {
                $price = $this->getBaseProductPrice();
            }

        // Simple with custom options
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            $price = $this->getSimpleWithCustomOptionsPrice();

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $price = $this->getBaseProductPrice();
        }

        return $price;
    }

    //-----------------------------------------

    protected function getConfigurablePriceParent()
    {
        $price = 0;

        $magentoProductParent = $this->getListingProduct()->getMagentoProduct();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $magentoProductParent->getTypeInstance();

        $attributeName = strtolower($this->getParentObject()->getAttribute());
        $optionName = strtolower($this->getParentObject()->getOption());

        foreach ($productTypeInstance->getConfigurableAttributes() as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($magentoProductParent->getStoreId());

            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = $configurableAttribute->getProductAttribute();
            $attribute->setStoreId($magentoProductParent->getStoreId());

            $tempAttributeNames = array_values($attribute->getStoreLabels());
            $tempAttributeNames[] = $configurableAttribute->getData('label');
            $tempAttributeNames[] = $attribute->getFrontendLabel();

            if (!in_array($attributeName,array_map('strtolower',array_filter($tempAttributeNames)))){
                continue;
            }

            $options = $attribute->getSource()->getAllOptions(false);

            foreach ((array)$configurableAttribute->getPrices() as $configurableOption) {

                $tempOptionNames = array();

                isset($configurableOption['label']) &&
                    $tempOptionNames[] = $configurableOption['label'];
                isset($configurableOption['default_label']) &&
                    $tempOptionNames[] = $configurableOption['default_label'];
                isset($configurableOption['store_label']) &&
                    $tempOptionNames[] = $configurableOption['store_label'];

                foreach ($options as $option) {
                    if ((int)$option['value'] == (int)$configurableOption['value_index']) {
                        $tempOptionNames[] = $option['label'];
                        break;
                    }
                }

                if (!in_array($optionName,array_map('strtolower',array_filter($tempOptionNames)))){
                    continue;
                }

                if ((bool)(int)$configurableOption['is_percent']) {

                    // Base Price of Main product.

                    $src = $this->getBuySellingFormatTemplate()->getPriceSource();

                    $basePrice = $this->getBuyListingProduct()->getBaseProductPrice($src['mode'],
                                                                                    $src['attribute']);
                    $price = ($basePrice * (float)$configurableOption['pricing_value']) / 100;

                } else {
                    $price = (float)$configurableOption['pricing_value'];
                    $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                }

                break 2;
            }
        }

        return $price;
    }

    protected function getBundlePriceParent()
    {
        $price = 0;

        $product = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $productTypeInstance = $this->getListingProduct()->getMagentoProduct()->getTypeInstance();
        $bundleAttributes = $productTypeInstance->getOptionsCollection($product);

        $attribute = strtolower($this->getParentObject()->getAttribute());

        foreach ($bundleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('required')) {
                continue;
            }

            if ((is_null($tempAttribute->getData('title')) ||
                 strtolower($tempAttribute->getData('title')) != $attribute) &&
                (is_null($tempAttribute->getData('default_title')) ||
                 strtolower($tempAttribute->getData('default_title')) != $attribute)) {
                continue;
            }

            $tempOptions = $productTypeInstance
                ->getSelectionsCollection(array(0 => $tempAttribute->getId()), $product)
                ->getItems();

            foreach ($tempOptions as $tempOption) {

                if ((int)$tempOption->getId() != $this->getParentObject()->getProductId()) {
                    continue;
                }

                if ((bool)(int)$tempOption->getData('selection_price_type')) {
                    // Base Price of Main product.

                    $src = $this->getBuySellingFormatTemplate()->getPriceSource();

                    $basePrice = $this->getBuyListingProduct()->getBaseProductPrice($src['mode'],
                                                                                    $src['attribute']);

                    $price = ($basePrice * (float)$tempOption->getData('selection_price_value')) / 100;
                } else {
                    $price = (float)$tempOption->getData('selection_price_value');
                    $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                }

                break 2;
            }
        }

        return $price;
    }

    protected function getSimpleWithCustomOptionsPrice()
    {
        $price = 0;

        $simpleAttributes = $this->getListingProduct()->getMagentoProduct()->getProduct()->getOptions();

        $attribute = strtolower($this->getParentObject()->getAttribute());
        $option = strtolower($this->getParentObject()->getOption());

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            $defaultTitle = $tempAttribute->getData('default_title');
            $storeTitle = $tempAttribute->getData('store_title');
            $title = $tempAttribute->getData('title');

            if ((is_null($defaultTitle) || strtolower($defaultTitle) != $attribute) &&
                (is_null($storeTitle) || strtolower($storeTitle) != $attribute) &&
                (is_null($title) || strtolower($title) != $attribute)) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                $defaultTitle = $tempOption->getData('default_title');
                $storeTitle = $tempOption->getData('store_title');
                $title = $tempOption->getData('title');

                if ((is_null($defaultTitle) || strtolower($defaultTitle) != $option) &&
                    (is_null($storeTitle) || strtolower($storeTitle) != $option) &&
                    (is_null($title) || strtolower($title) != $option)) {
                    continue;
                }

                if (!is_null($tempOption->getData('price_type')) &&
                    $tempOption->getData('price_type') !== false) {

                    switch ($tempOption->getData('price_type')) {
                        case 'percent':
                            $src = $this->getBuySellingFormatTemplate()->getPriceSource();
                            $basePrice = $this->getBuyListingProduct()->getBaseProductPrice($src['mode'],
                                                                                            $src['attribute']);
                            $price = ($basePrice * (float)$tempOption->getData('price')) / 100;
                            break;
                        case 'fixed':
                            $price = (float)$tempOption->getData('price');
                            $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                            break;
                    }
                }

                break 2;
            }
        }

        return $price;
    }

    //-----------------------------------------

    protected function getBaseProductPrice()
    {
        $price = 0;

        $src = $this->getBuySellingFormatTemplate()->getPriceSource();

        switch ($src['mode']) {

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_SPECIAL:
                $price = $this->getMagentoProduct()->getSpecialPrice();
                $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_PRODUCT:
                $price = $this->getMagentoProduct()->getPrice();
                $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################
}