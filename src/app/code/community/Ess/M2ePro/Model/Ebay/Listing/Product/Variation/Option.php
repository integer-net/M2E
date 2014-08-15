<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation_Option getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Option extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product_Variation_Option');
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
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    public function getEbayListing()
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
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    public function getEbayListingProduct()
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
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Variation
     */
    public function getEbayListingProductVariation()
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
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
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
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    public function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getEbayListingProductVariation()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getEbayListingProductVariation()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getEbayListingProductVariation()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        return $this->getEbayListingProductVariation()->getPaymentTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        return $this->getEbayListingProductVariation()->getReturnTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        return $this->getEbayListingProductVariation()->getShippingTemplate();
    }

    // ########################################

    public function getSku()
    {
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

    public function getQty()
    {
        if (!$this->getMagentoProduct()->isStatusEnabled() || !$this->getMagentoProduct()->isStockAvailability()) {
            return 0;
        }

        $qty = 0;
        $src = $this->getEbaySellingFormatTemplate()->getQtySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT) {
            if (!$this->getListingProduct()->getMagentoProduct()->isStatusEnabled() ||
                !$this->getListingProduct()->getMagentoProduct()->isStockAvailability()) {
                return 0;
            }
        }

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $qty = (int)$this->getMagentoProduct()->getQty(false);
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ########################################

    public function getPrice($src)
    {
        $price = 0;

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        // Configurable product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType()) {

            if ($this->getEbaySellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getConfigurablePriceParent($src);
            } else {
                $price = $this->getBaseProductPrice($src);
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            if ($this->getEbaySellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getBundlePriceParent($src);
            } else {
                $price = $this->getBaseProductPrice($src);
            }

        // Simple with custom options
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            $price = $this->getSimpleWithCustomOptionsPrice($src);

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $price = $this->getBaseProductPrice($src);
        }

        return $price;
    }

    //-----------------------------------------

    protected function getConfigurablePriceParent($src)
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

                $tempOptionNames = array_map('strtolower', array_filter($tempOptionNames));
                foreach ($tempOptionNames as &$tempName) {
                    $tempName = Mage::helper('M2ePro')->reduceWordsInString(
                        $tempName, Ess_M2ePro_Helper_Component_Ebay::MAX_LENGTH_FOR_OPTION_VALUE
                    );
                }

                if (!in_array($optionName, $tempOptionNames)){
                    continue;
                }

                if ((bool)(int)$configurableOption['is_percent']) {
                    // Base Price of Main product.
                    $basePrice = $this->getEbayListingProduct()->getBaseProductPrice($src);
                    $price = ($basePrice * (float)$configurableOption['pricing_value']) / 100;
                } else {
                    $price = (float)$configurableOption['pricing_value'];
                    $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
                }

                break 2;
            }
        }

        return $price;
    }

    protected function getBundlePriceParent($src)
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
                    $basePrice = $this->getEbayListingProduct()->getBaseProductPrice($src);
                    $price = ($basePrice * (float)$tempOption->getData('selection_price_value')) / 100;
                } else {
                    $price = (float)$tempOption->getData('selection_price_value');
                    $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
                }

                break 2;
            }
        }

        return $price;
    }

    protected function getSimpleWithCustomOptionsPrice($src)
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

            $tempAttributeTitles = array($tempAttribute->getData('default_title'),
                                         $tempAttribute->getData('store_title'),
                                         $tempAttribute->getData('title'));

            $tempAttributeTitles = array_map('strtolower', array_filter($tempAttributeTitles));

            if (!in_array($attribute, $tempAttributeTitles)) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                $tempOptionTitles = array($tempOption->getData('default_title'),
                                          $tempOption->getData('store_title'),
                                          $tempOption->getData('title'));

                $tempOptionTitles = array_map('strtolower', array_filter($tempOptionTitles));
                foreach ($tempOptionTitles as &$tempTitle) {
                    $tempTitle = Mage::helper('M2ePro')->reduceWordsInString(
                        $tempTitle, Ess_M2ePro_Helper_Component_Ebay::MAX_LENGTH_FOR_OPTION_VALUE
                    );
                }

                if (!in_array($option, $tempOptionTitles )) {
                    continue;
                }

                if (!is_null($tempOption->getData('price_type')) &&
                    $tempOption->getData('price_type') !== false) {

                    switch ($tempOption->getData('price_type')) {
                        case 'percent':
                            $basePrice = $this->getEbayListingProduct()->getBaseProductPrice($src);
                            $price = ($basePrice * (float)$tempOption->getData('price')) / 100;
                            break;
                        case 'fixed':
                            $price = (float)$tempOption->getData('price');
                            $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
                            break;
                    }
                }

                break 2;
            }
        }

        return $price;
    }

    //-----------------------------------------

    protected function getBaseProductPrice($src)
    {
        $price = 0;

        switch ($src['mode']) {

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_SPECIAL:
                $price = $this->getMagentoProduct()->getSpecialPrice();
                $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT:
                $price = $this->getMagentoProduct()->getPrice();
                $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getDescriptionTemplate()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }
        if ($this->getDescriptionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getDescriptionTemplate()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        if (empty($imageLink)) {
            return $imageLink;
        }

        return $this->getDescriptionTemplate()->addWatermarkIfNeed($imageLink);
    }

    public function getImagesForEbay()
    {
        if ($this->getDescriptionTemplate()->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        return array($mainImage);
    }

    // ########################################
}