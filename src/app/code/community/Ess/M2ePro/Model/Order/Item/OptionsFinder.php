<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Item_OptionsFinder
{
    /** @var Ess_M2ePro_Model_Magento_Product */
    private $magentoProduct = null;

    private $variation = null;

    private $failedOptions = array();

    public function __construct(array $variation = array())
    {
        $this->variation = $variation;
    }

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getFailedOptions()
    {
        return $this->failedOptions;
    }

    public function hasFailedOptions()
    {
        return count($this->failedOptions) > 0;
    }

    public function getProductDetails()
    {
        if (is_null($this->magentoProduct)) {
            throw new Exception('Magento product is not set.');
        }

        $details = array();

        if ($this->magentoProduct->isSimpleType()
            || $this->magentoProduct->isConfigurableType()
            || $this->magentoProduct->isBundleType()
            || $this->magentoProduct->isDownloadableType()
        ) {
            $details = $this->getSelectedOptions();
            $details['associated_products'] = $this->prepareAssociatedProducts($details['associated_products']);
        }

        if ($this->magentoProduct->isGroupedType()) {
            $associatedProduct = $this->getGroupedAssociatedProduct();

            if (is_null($associatedProduct)) {
                throw new Exception('There is no associated product found for grouped product.');
            }

            $details = array(
                'associated_options'  => array(),
                'associated_products' => array($associatedProduct->getId())
            );
        }

        return $details;
    }

    private function getSelectedOptions()
    {
        $channelOptions = $this->toLowerCase($this->variation);
        $magentoOptions = $this->magentoProduct->getProductVariationsForOrder();

        if (count($magentoOptions) == 0) {
            // product doesn't have required options
            return array(
                'associated_options'  => array(),
                'associated_products' => array()
            );
        }

        $options  = array();
        $products = array();

        //------------------------------
        $configGroup = '/order/magento/settings/';
        $configKey   = 'create_with_first_product_options_when_variation_unavailable';
        $configValue = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($configGroup, $configKey);

        if (empty($channelOptions) && !$configValue) {
            return array(
                'associated_options'  => array(),
                'associated_products' => array()
            );
        }
        //------------------------------

        // Variation info unavailable - return first value for each required option
        // ---------------
        if (empty($channelOptions)) {
            foreach ($magentoOptions as $magentoOption) {
                $optionId = $magentoOption['option_id'];
                $valueId  = $magentoOption['values'][0]['value_id'];

                $options[$optionId] = $valueId;
                $products["{$optionId}::{$valueId}"] = $magentoOption['values'][0]['product_ids'];
            }

            return array(
                'associated_options'  => $options,
                'associated_products' => $products
            );
        }
        // ---------------

        // Map variation with magento options
        // ---------------
        foreach ($magentoOptions as $magentoOption) {
            $magentoOption['labels'] = array_filter($magentoOption['labels']);

            $valueLabel = $this->getValueLabel($channelOptions, $magentoOption['labels']);
            if ($valueLabel == '') {
                $this->failedOptions[] = array_shift($magentoOption['labels']);
                continue;
            }

            $magentoValue = $this->getMagentoValue($valueLabel, $magentoOption['values']);
            if (is_null($magentoValue)) {
                $this->failedOptions[] = array_shift($magentoOption['labels']);
                continue;
            }

            $optionId = $magentoOption['option_id'];
            $valueId  = $magentoValue['value_id'];

            $options[$optionId] = $valueId;
            $products["{$optionId}::{$valueId}"] = $magentoValue['product_ids'];
        }
        // ---------------

        if ($this->magentoProduct->isConfigurableType() && $this->hasFailedOptions()) {
            throw new Exception('There is no associated product found for configurable product.');
        }

        return array(
            'associated_options'  => $options,
            'associated_products' => $products
        );
    }

    /**
     * Return value label for mapped option if found, empty string otherwise
     *
     * @param array $variation
     * @param array $optionLabels
     *
     * @return string
     */
    private function getValueLabel(array $variation, array $optionLabels)
    {
        $optionLabels = $this->toLowerCase($optionLabels);

        foreach ($optionLabels as $label) {
            if (isset($variation[$label])) {
                return $variation[$label];
            }
        }

        return '';
    }

    /**
     * Return value id for value label if found, null otherwise
     *
     * @param       $valueLabel
     * @param array $optionValues
     *
     * @return int|null
     */
    private function getMagentoValue($valueLabel, array $optionValues)
    {
        foreach ($optionValues as $value) {
            $valueLabels = $this->toLowerCase($value['labels']);

            if (in_array($valueLabel, $valueLabels)) {
                return $value;
            }
        }

        return null;
    }

    public function prepareAssociatedProducts(array $associatedProducts)
    {
        if ($this->magentoProduct->isSimpleType() || $this->magentoProduct->isDownloadableType()) {
            return array($this->magentoProduct->getProductId());
        }

        if ($this->magentoProduct->isBundleType()) {
            $bundleAssociatedProducts = array();

            foreach ($associatedProducts as $key => $productIds) {
                $bundleAssociatedProducts[$key] = reset($productIds);
            }

            return $bundleAssociatedProducts;
        }

        if ($this->magentoProduct->isConfigurableType()) {
            $configurableAssociatedProducts = array();

            foreach ($associatedProducts as $productIds) {
                if (count($configurableAssociatedProducts) == 0) {
                    $configurableAssociatedProducts = $productIds;
                } else {
                    $configurableAssociatedProducts = array_intersect($configurableAssociatedProducts, $productIds);
                }
            }

            if (count($configurableAssociatedProducts) != 1) {
                throw new LogicException('There is no associated product found for configurable product.');
            }

            return $configurableAssociatedProducts;
        }

        if ($this->magentoProduct->isGroupedType()) {
            return array_values($associatedProducts);
        }

        return array();
    }

    private function getGroupedAssociatedProduct()
    {
        $variationName = array_shift($this->variation);

        //------------------------------
        $configGroup = '/order/magento/settings/';
        $configKey   = 'create_with_first_product_options_when_variation_unavailable';
        $configValue = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($configGroup, $configKey);

        if ((is_null($variationName) || strlen(trim($variationName)) == 0) && !$configValue) {
            return null;
        }
        //------------------------------

        $associatedProducts = $this->magentoProduct->getProductVariationsForOrder();

        foreach ($associatedProducts as $product) {
            // return product if it's name is equal to variation name
            if (is_null($variationName) || trim(strtolower($product->getName())) == trim(strtolower($variationName))) {
                return $product;
            }
        }

        return null;
    }
    private function toLowerCase(array $data = array())
    {
        if (count($data) == 0) {
            return $data;
        }

        $lowerCasedData = array();

        foreach ($data as $key => $value) {
            $lowerCasedData[trim(strtolower($key))] = trim(strtolower($value));
        }

        return $lowerCasedData;
    }
}
