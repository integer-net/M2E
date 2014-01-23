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

        $variations = $this->getListingProduct()->getVariations(true);

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

            $options = $variation->getOptions(true);

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $item['specifics'][$option->getAttribute()] = $option->getOption();
            }

            $data[] = $item;
        }

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
        $attributeData = $product->getResource()->getAttribute($attributeCode);

        if (!$attributeData) {
            return array();
        }

        $attributeLabels = array();

        $configurableAttributes = $product->getTypeInstance()
                                          ->setStoreFilter($this->getListing()->getStoreId())
                                          ->getConfigurableAttributesAsArray($product);

        foreach ($configurableAttributes as $configurableAttribute) {
            if ((int)$attributeData['attribute_id'] == (int)$configurableAttribute['attribute_id']) {
                $attributeLabels = array(
                    $configurableAttribute['label'],
                    $configurableAttribute['frontend_label'],
                    $configurableAttribute['store_label']
                );
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
                foreach ($attributeLabels as $attributeLabel) {
                    if (strtolower($attributeLabel) == strtolower($option->getAttribute())) {
                        $foundAttributeLabel = $option->getAttribute();
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
}