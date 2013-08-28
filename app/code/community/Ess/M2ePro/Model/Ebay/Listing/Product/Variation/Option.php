<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
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
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getParentObject()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product_Variation
     */
    public function getListingProductVariation()
    {
        return $this->getParentObject()->getListingProductVariation();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getParentObject()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getParentObject()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getParentObject()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getParentObject()->getSynchronizationTemplate();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    public function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    public function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
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
     * @return Ess_M2ePro_Model_Ebay_Template_General
     */
    public function getEbayGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getSku()
    {
        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            return $this->getMagentoProduct()->getSku();
        }

        $tempSku = '';

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $simpleAttributes = $mainProduct->getOptions();

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

        return $tempSku;
    }

    public function getQty()
    {
        $qty = 0;

        $src = $this->getEbaySellingFormatTemplate()->getQtySource();

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

            default:
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty();
                break;
        }

        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            if (!$this->getMagentoProduct()->getStockAvailability() ||
                $this->getMagentoProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED)  {
                // Out of stock or disabled Item? Set QTY = 0
                $qty = 0;
            }
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ########################################

    public function getPrice()
    {
        $price = 0;

        // Configurable product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType()) {

            if ($this->getEbaySellingFormatTemplate()->isPriceVariationModeParent()) {
                $price = $this->getConfigurablePriceParent();
            } else {
                $price = $this->getBaseProductPrice();
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            if ($this->getEbaySellingFormatTemplate()->isPriceVariationModeParent()) {
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

        $price < 0 && $price = 0;

        return $price;
    }

    //-----------------------------------------

    protected function getConfigurablePriceParent()
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $mainProductInstance = $mainProduct->getTypeInstance()->setStoreFilter($this->getListing()->getStoreId());

        $productAttributes = $mainProductInstance->getUsedProductAttributes();
        $configurableAttributes = $mainProductInstance->getConfigurableAttributes();

        $attribute = strtolower($this->getParentObject()->getAttribute());
        $option = strtolower($this->getParentObject()->getOption());

        foreach ($configurableAttributes as $configurableAttribute) {

            $label = strtolower($configurableAttribute->getData('label'));
            $frontLabel = strtolower($configurableAttribute->getProductAttribute()->getFrontend()->getLabel());
            $storeLabel = strtolower($configurableAttribute->getProductAttribute()->getStoreLabel());

            if ($label != $attribute && $frontLabel != $attribute && $storeLabel != $attribute) {
                continue;
            }

            if (!isset($productAttributes[(int)$configurableAttribute->getProductAttribute()->getId()])) {
                continue;
            }

            $productAttribute = $productAttributes[(int)$configurableAttribute->getProductAttribute()->getId()];
            $productAttribute->setStoreId($this->getListing()->getStoreId());

            $productAttributesOptions = $productAttribute->getSource()->getAllOptions(false);

            $configurableOptions = $configurableAttribute->getPrices() ? $configurableAttribute->getPrices() : array();
            foreach ($configurableOptions as $configurableOption) {

                $label = isset($configurableOption['label']) ? strtolower($configurableOption['label']) : '';
                $defaultLabel = isset($configurableOption['default_label']) ?
                                    strtolower($configurableOption['default_label']) : '';
                $storeLabel = isset($configurableOption['store_label']) ?
                                    strtolower($configurableOption['store_label']) : '';

                foreach ($productAttributesOptions as $productAttributesOption) {
                    if ((int)$productAttributesOption['value'] == (int)$configurableOption['value_index']) {
                        $storeLabel = strtolower($productAttributesOption['label']);
                        break;
                    }
                }

                if ($label != $option && $defaultLabel != $option && $storeLabel != $option) {
                    continue;
                }

                if ((bool)(int)$configurableOption['is_percent']) {
                    // Base Price of Main product.
                    $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();
                    $basePrice = $this->getEbayListingProduct()->getBaseProductPrice($src['mode'],$src['attribute']);
                    $price = ($basePrice * (float)$configurableOption['pricing_value']) / 100;
                } else {
                    $price = (float)$configurableOption['pricing_value'];
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBundlePriceParent()
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $mainProductInstance = $mainProduct->getTypeInstance()->setStoreFilter($this->getListing()->getStoreId());
        $bundleAttributes = $mainProductInstance->getOptionsCollection($mainProduct);

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

            $tempOptions = $mainProductInstance
                ->getSelectionsCollection(array(0 => $tempAttribute->getId()), $mainProduct)
                ->getItems();

            foreach ($tempOptions as $tempOption) {

                if ((int)$tempOption->getId() != $this->getParentObject()->getProductId()) {
                    continue;
                }

                if ((bool)(int)$tempOption->getData('selection_price_type')) {
                    // Base Price of Main product.
                    $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();
                    $basePrice = $this->getEbayListingProduct()->getBaseProductPrice($src['mode'],$src['attribute']);
                    $price = ($basePrice * (float)$tempOption->getData('selection_price_value')) / 100;
                } else {
                    $price = (float)$tempOption->getData('selection_price_value');
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getSimpleWithCustomOptionsPrice()
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $simpleAttributes = $mainProduct->getOptions();

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
                            $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();
                            $basePrice = $this->getEbayListingProduct()->getBaseProductPrice(
                                $src['mode'], $src['attribute']
                            );
                            $price = ($basePrice * (float)$tempOption->getData('price')) / 100;
                            break;
                        case 'fixed':
                            $price = (float)$tempOption->getData('price');
                            break;
                    }
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    //-----------------------------------------

    protected function getBaseProductPrice()
    {
        $price = 0;

        $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();

        switch ($src['mode']) {

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_SPECIAL:
                $price = $this->getMagentoProduct()->getSpecialPrice();
                $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_FINAL:
                $customerGroupId = $this->getEbaySellingFormatTemplate()->getCustomerGroupId();
                $price = $this->getMagentoProduct()->getFinalPrice($customerGroupId);
                break;

            default:
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT:
                $price = $this->getMagentoProduct()->getPrice();
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getEbayDescriptionTemplate()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }
        if ($this->getEbayDescriptionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getEbayDescriptionTemplate()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        if (empty($imageLink)) {
            return $imageLink;
        }

        return $this->getEbayDescriptionTemplate()->addWatermarkIfNeed($imageLink);
    }

    public function getImagesForEbay()
    {
        if ($this->getEbayDescriptionTemplate()->isImageMainModeNone()) {
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