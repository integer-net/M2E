<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * Configures the store settings to match account settings and order information
 */
class Ess_M2ePro_Model_Magento_Quote_Store_Configurator
{
    /** @var $quote Mage_Sales_Model_Quote */
    private $quote = NULL;

    /** @var $proxy Ess_M2ePro_Model_Order_Proxy */
    private $proxyOrder = NULL;

    /** @var $taxConfig Mage_Tax_Model_Config */
    private $taxConfig = NULL;

    // ########################################

    public function init(Mage_Sales_Model_Quote $quote, Ess_M2ePro_Model_Order_Proxy $proxyOrder)
    {
        $this->quote      = $quote;
        $this->proxyOrder = $proxyOrder;
        $this->taxConfig  = Mage::getSingleton('tax/config');
    }

    // ########################################

    /**
     * Get original store settings before any changes were made
     *
     * @return array
     */
    public function getOriginalStoreConfig()
    {
        $keys = array(
            Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX,
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON,
            Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID,
            $this->getOriginCountryIdXmlPath(),
            $this->getOriginRegionIdXmlPath(),
            $this->getOriginPostcodeXmlPath()
        );

        $config = array();

        foreach ($keys as $key) {
            $config[$key] = $this->getStoreConfig($key);
        }

        return $config;
    }

    // ########################################

    /**
     * Prepare store config according to order information and account settings
     */
    public function prepareStoreConfigForOrder()
    {
        // catalog prices
        // --------------------
        // reset flag, use store config instead
        $this->taxConfig->setNeedUsePriceExcludeTax(false);
        $this->setStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $this->isPriceIncludesTax());
        // --------------------

        // shipping prices
        // --------------------
        $isShippingPriceIncludesTax = $this->isShippingPriceIncludesTax();
        if (method_exists($this->taxConfig, 'setShippingPriceIncludeTax')) {
            $this->taxConfig->setShippingPriceIncludeTax($isShippingPriceIncludesTax);
        } else {
            $this->setStoreConfig(
                Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $isShippingPriceIncludesTax
            );
        }
        // --------------------

        // store origin address
        // --------------------
        $this->setStoreConfig($this->getOriginCountryIdXmlPath(), $this->getOriginCountryId());
        $this->setStoreConfig($this->getOriginRegionIdXmlPath(), $this->getOriginRegionId());
        $this->setStoreConfig($this->getOriginPostcodeXmlPath(), $this->getOriginPostcode());
        // --------------------

        // --------------------
        $this->setStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $this->getDefaultCustomerGroupId());
        $this->setStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $this->getTaxCalculationBasedOn());
        // --------------------

        // store shipping tax class
        // --------------------
        $this->setStoreConfig(
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->getShippingTaxClassId()
        );
        // --------------------
    }

    // ########################################

    /**
     * Check whether channel price should include tax
     *
     * @return bool
     */
    private function isPriceIncludesTax()
    {
        $isPriceIncludesTax = (bool)$this->getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX);

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $isPriceIncludesTax;
        }

        if ($this->proxyOrder->isTaxModeChannel()) {
            return !$this->proxyOrder->hasTax();
        }

        if ($this->proxyOrder->hasTax()) {
            return false;
        }

        return $isPriceIncludesTax;
    }

    // ########################################

    /**
     * Check whether channel shipping price should include tax
     *
     * @return bool
     */
    private function isShippingPriceIncludesTax()
    {
        $isShippingPriceIncludesTax = (bool)$this->getStoreConfig(
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX
        );

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $isShippingPriceIncludesTax;
        }

        if ($this->proxyOrder->isTaxModeChannel()) {
            return false;
        }

        if ($this->proxyOrder->getTaxRate() > 0) {
            return false;
        }

        return $isShippingPriceIncludesTax;
    }

    // ########################################

    /**
     * Return tax class id for shipping
     *
     * @return int
     */
    private function getShippingTaxClassId()
    {
        $proxyOrder = $this->proxyOrder;
        $hasRatesForCountry = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->hasRatesForCountry($this->quote->getShippingAddress()->getCountryId());
        $storeShippingTaxRate = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->getStoreShippingTaxRate($this->getStore());
        $calculationBasedOnOrigin = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->isCalculationBasedOnOrigin($this->getStore());

        if ($proxyOrder->isTaxModeNone()
            || $proxyOrder->isTaxModeChannel()
            || ($proxyOrder->isTaxModeMagento() && !$hasRatesForCountry && !$calculationBasedOnOrigin)
        ) {
            return Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE;
        }

        if ($proxyOrder->isTaxModeMagento()
            || $proxyOrder->getTaxRate() == 0
            || $proxyOrder->getTaxRate() == $storeShippingTaxRate
        ) {
            return $this->taxConfig->getShippingTaxClass($this->getStore());
        }

        // Create tax rule according to channel tax rate
        // -------------------------
        /** @var $taxRuleBuilder Ess_M2ePro_Model_Magento_Tax_Rule_Builder */
        $taxRuleBuilder = Mage::getModel('M2ePro/Magento_Tax_Rule_Builder');
        $taxRuleBuilder->buildTaxRule(
            $proxyOrder->getTaxRate(),
            $this->quote->getShippingAddress()->getCountryId(),
            $this->quote->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // -------------------------

        return array_shift($productTaxClasses);
    }

    // ########################################

    /**
     * Return store origin country id
     *
     * @return string
     */
    private function getOriginCountryId()
    {
        $originCountryId = $this->getStoreConfig($this->getOriginCountryIdXmlPath());

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $originCountryId;
        }

        if ($this->proxyOrder->isTaxModeMixed() && $this->proxyOrder->getTaxRate() == 0) {
            return $originCountryId;
        }

        if ($this->proxyOrder->isTaxModeNone()
            || ($this->proxyOrder->isTaxModeChannel() && $this->proxyOrder->getTaxRate() == 0)
        ) {
            return '';
        }

        return $this->quote->getShippingAddress()->getCountryId();
    }

    /**
     * Return store origin region id
     *
     * @return string
     */
    private function getOriginRegionId()
    {
        $originRegionId = $this->getStoreConfig($this->getOriginRegionIdXmlPath());

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $originRegionId;
        }

        if ($this->proxyOrder->isTaxModeMixed() && $this->proxyOrder->getTaxRate() == 0) {
            return $originRegionId;
        }

        if ($this->proxyOrder->isTaxModeNone()
            || ($this->proxyOrder->isTaxModeChannel() && $this->proxyOrder->getTaxRate() == 0)
        ) {
            return '';
        }

        return $this->quote->getShippingAddress()->getRegionId();
    }

    /**
     * Return store origin postcode
     *
     * @return string
     */
    private function getOriginPostcode()
    {
        $originPostcode = $this->getStoreConfig($this->getOriginPostcodeXmlPath());

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $originPostcode;
        }

        if ($this->proxyOrder->isTaxModeMixed() && $this->proxyOrder->getTaxRate() == 0) {
            return $originPostcode;
        }

        if ($this->proxyOrder->isTaxModeNone()
            || ($this->proxyOrder->isTaxModeChannel() && $this->proxyOrder->getTaxRate() == 0)
        ) {
            return '';
        }

        return $this->quote->getShippingAddress()->getPostcode();
    }

    // ########################################

    private function getDefaultCustomerGroupId()
    {
        $defaultCustomerGroupId = $this->getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID);

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $defaultCustomerGroupId;
        }

        $currentDefaultCustomerTaxClass = Mage::getSingleton('tax/calculation')
            ->getDefaultCustomerTaxClass($this->getStore());
        $quoteCustomerTaxClass = $this->quote->getCustomerTaxClassId();

        if ($currentDefaultCustomerTaxClass == $quoteCustomerTaxClass) {
            return $defaultCustomerGroupId;
        }

        // ugliest hack ever!
        // we have to remove exist singleton instance from the Mage registry
        // because Mage_Tax_Model_Calculation::getDefaultCustomerTaxClass() method stores the customer tax class
        // after the first call in protected variable and then it doesn't care what store was given to it
        Mage::unregister('_singleton/tax/calculation');

        // default customer tax class depends on default customer group
        // so we override store setting for this with the customer group from the quote
        // this is done to make store & address tax requests equal
        return $this->quote->getCustomerGroupId();
    }

    // ########################################

    private function getTaxCalculationBasedOn()
    {
        $basedOn = $this->getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON);

        if ($this->proxyOrder->isTaxModeMagento()) {
            return $basedOn;
        }

        if ($this->proxyOrder->isTaxModeMixed() && $this->proxyOrder->getTaxRate() == 0) {
            return $basedOn;
        }

        return 'shipping';
    }

    // ########################################

    /**
     * Return xml path in config for origin country id
     *
     * @return string
     */
    private function getOriginCountryIdXmlPath()
    {
        // Magento 1.4.x backward compatibility
        return @defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID
            : 'shipping/origin/country_id';
    }

    /**
     * Return xml path in config for origin region id
     *
     * @return string
     */
    private function getOriginRegionIdXmlPath()
    {
        // Magento 1.4.x backward compatibility
        return @defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID
            : 'shipping/origin/region_id';
    }

    /**
     * Return xml path in config for origin postcode
     *
     * @return string
     */
    private function getOriginPostcodeXmlPath()
    {
        // Magento 1.4.x backward compatibility
        return @defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE
            : 'shipping/origin/postcode';
    }

    private function getStore()
    {
        return $this->quote->getStore();
    }

    /**
     * Set config value for store object without saving
     *
     * @param $key
     * @param $value
     */
    private function setStoreConfig($key, $value)
    {
        $this->getStore()->setConfig($key, $value);
    }

    /**
     * Get config value from store object
     *
     * @param $key
     * @return null|string
     */
    private function getStoreConfig($key)
    {
        return $this->getStore()->getConfig($key);
    }

    // ########################################
}