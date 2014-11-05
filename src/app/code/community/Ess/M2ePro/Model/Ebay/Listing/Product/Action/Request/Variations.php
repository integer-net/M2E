<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Variations
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        $data = array(
            'is_variation_item' => $this->getIsVariationItem()
        );

        $this->logLimitationsAndReasons();

        if (!$this->getIsVariationItem() || !$this->getConfigurator()->isVariations()) {
            return $data;
        }

        $data['variation'] = $this->getVariationsData();

        if ($sets = $this->getSetsData()) {
            $data['variations_sets'] = $sets;
        }

        $data['variation_image'] = $this->getImagesData();

        return $data;
    }

    // ########################################

    public function getVariationsData()
    {
        $data = array();

        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();

        $variations = $this->getListingProduct()->getVariations(true);
        $productsIds = array();

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            $item = array(
                '_instance_' => $variation,
                'price' => $variation->getChildObject()->getPrice(),
                'qty' => $variation->getChildObject()->isDelete() ? 0 : $variation->getChildObject()->getQty(),
                'sku' => $variation->getChildObject()->getSku(),
                'add' => $variation->getChildObject()->isAdd(),
                'delete' => $variation->getChildObject()->isDelete(),
                'specifics' => array()
            );

            if (($qtyMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
                $qtyMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT) && !$item['delete']) {

                $options = $variation->getOptions();
                foreach ($options as $option) {
                    $productsIds[] = $option['product_id'];
                }
            }

            if ($this->getEbayListingProduct()->isPriceDiscountStp()) {

                $priceDiscountData = array(
                    'original_retail_price' => $variation->getChildObject()->getPriceDiscountStp()
                );

                if ($this->getEbayMarketplace()->isStpAdvancedEnabled()) {
                    $priceDiscountData = array_merge(
                        $priceDiscountData,
                        $this->getEbayListingProduct()->getEbaySellingFormatTemplate()
                             ->getPriceDiscountStpAdditionalFlags()
                    );
                }

                $item['price_discount_stp'] = $priceDiscountData;
            }

            if ($this->getEbayListingProduct()->isPriceDiscountMap()) {
                $priceDiscountMapData = array(
                    'minimum_advertised_price' => $variation->getChildObject()->getPriceDiscountMap(),
                );

                $exposure = $variation->getChildObject()->
                    getEbaySellingFormatTemplate()->getPriceDiscountMapExposureType();
                $priceDiscountMapData['minimum_advertised_price_exposure'] =
                    Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::
                        getPriceDiscountMapExposureType($exposure);

                $item['price_discount_map'] = $priceDiscountMapData;
            }

            $options = $variation->getOptions(true);

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $item['specifics'][$option->getAttribute()] = $option->getOption();
            }

            $data[] = $item;
        }

        $this->checkQtyWarnings($productsIds);

        return $data;
    }

    public function getSetsData()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (isset($additionalData['variations_sets'])) {
            return $additionalData['variations_sets'];
        }

        return false;
    }

    public function getImagesData()
    {
        $attributeLabels = array();

        if ($this->getMagentoProduct()->isConfigurableType()) {
            $attributeLabels = $this->getConfigurableImagesAttributeLabels();
        }

        if ($this->getMagentoProduct()->isGroupedType()) {
            $attributeLabels = array(Ess_M2ePro_Model_Magento_Product::GROUPED_PRODUCT_ATTRIBUTE_LABEL);
        }

        if (count($attributeLabels) <= 0) {
            return array();
        }

        return $this->getImagesDataByAttributeLabels($attributeLabels);
    }

    // ########################################

    private function logLimitationsAndReasons()
    {
        if ($this->getMagentoProduct()->isProductWithoutVariations()) {
            return;
        }

        if (!$this->getEbayMarketplace()->isMultivariationEnabled()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The product was listed as a simple product as it has limitation for multi-variation items. '.
                    'Reason: eBay site allows to list only simple items.'
                )
            );
            return;
        }

        $tempResult = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                    ->isVariationEnabled(
                                        (int)$this->getEbayListingProduct()->getCategoryTemplate()->getMainCategory(),
                                        $this->getMarketplace()->getId()
                                    );

        if (!$tempResult) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The product was listed as a simple product as it has limitation for multi-variation items. '.
                    'Reason: eBay primary category allows to list only simple items.'
                )
            );
            return;
        }

        if ($this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isIgnoreVariationsEnabled()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The product was listed as a simple product as it has limitation for multi-variation items. '.
                    'Reason: ignore variation option is enabled in selling format policy.'
                )
            );
            return;
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The product was listed as a simple product as it has limitation for multi-variation items. '.
                    'Reason: listing type "auction" does not support multi-variations.'
                )
            );
            return;
        }
    }

    // ----------------------------------------

    private function getConfigurableImagesAttributeLabels()
    {
        $descriptionTemplate = $this->getEbayListingProduct()->getDescriptionTemplate();

        if (!$descriptionTemplate->isVariationConfigurableImages()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $attributeCode = $descriptionTemplate->getVariationConfigurableImages();

        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $product->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            return array();
        }

        $attribute->setStoreId($product->getStoreId());

        $attributeLabels = array();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        foreach ($productTypeInstance->getConfigurableAttributes() as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($product->getStoreId());

            if ((int)$attribute->getAttributeId() == (int)$configurableAttribute->getAttributeId()) {

                $attributeLabels = array_values($attribute->getStoreLabels());
                $attributeLabels[] = $configurableAttribute->getData('label');
                $attributeLabels[] = $attribute->getFrontendLabel();

                $attributeLabels = array_filter($attributeLabels);

                break;
            }
        }

        if (empty($attributeLabels)) {

            $this->addNotFoundAttributesMessages(
                Mage::helper('M2ePro')->__('Change Images for Attribute'),
                array($attributeCode)
            );

            return array();
        }

        return $attributeLabels;
    }

    private function getImagesDataByAttributeLabels(array $attributeLabels)
    {
        $images = array();
        $attributeLabel = false;

        $variations = $this->getListingProduct()->getVariations(true);

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            if ($variation->getChildObject()->isDelete()) {
                continue;
            }

            $options = $variation->getOptions(true);

            foreach ($options as $option) {

                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                $foundAttributeLabel = false;
                foreach ($attributeLabels as $tempLabel) {
                    if (strtolower($tempLabel) == strtolower($option->getAttribute())) {
                        $foundAttributeLabel = $option->getAttribute();
                        break;
                    }
                }

                if ($foundAttributeLabel === false) {
                    continue;
                }

                $attributeLabel = $foundAttributeLabel;

                $optionImages = $option->getChildObject()->getImagesForEbay();

                if (count($optionImages) <= 0) {
                    continue;
                }

                $images[$option->getOption()] = array_slice($optionImages,0,1);
            }
        }

        if (!$attributeLabel || !$images) {
            return array();
        }

        return array(
            'specific' => $attributeLabel,
            'images' => $images
        );
    }

    // ########################################

    public function checkQtyWarnings($productsIds)
    {
        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT) {

            $productsIds = array_unique($productsIds);
            $qtyWarnings = array();

            $listingProductId = $this->getListingProduct()->getId();
            $storeId = $this->getListing()->getStoreId();

            foreach ($productsIds as $productId) {
                if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics
                        [$listingProductId][$productId][$storeId]['qty'])) {

                    $qtys = Ess_M2ePro_Model_Magento_Product::$statistics
                        [$listingProductId][$productId][$storeId]['qty'];
                    $qtyWarnings = array_unique(array_merge($qtyWarnings, array_keys($qtys)));
                }

                if (count($qtyWarnings) === 2) {
                    break;
                }
            }

            foreach ($qtyWarnings as $qtyWarningType) {
                $this->addQtyWarnings($qtyWarningType);
            }
        }
    }

    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
        // M2ePro_TRANSLATIONS
        // During the quantity calculation the settings in the "Manage Stock No" field were taken into consideration.
            $this->addWarningMessage('During the quantity calculation the settings in the "Manage Stock No" '.
                                     'field were taken into consideration.');
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            // M2ePro_TRANSLATIONS
            // During the quantity calculation the settings in the "Backorders" field were taken into consideration.
            $this->addWarningMessage('During the quantity calculation the settings in the "Backorders" '.
                                     'field were taken into consideration.');
        }
    }

    // ########################################
}