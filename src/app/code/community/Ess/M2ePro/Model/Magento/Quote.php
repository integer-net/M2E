<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * Builds the quote object, which then can be converted to magento order
 */
class Ess_M2ePro_Model_Magento_Quote
{
    /** @var Ess_M2ePro_Model_Order_Proxy */
    private $proxyOrder = NULL;

    /** @var Mage_Sales_Model_Quote */
    private $quote = NULL;

    private $originalStoreConfig = array();

    // ########################################

    public function __construct(Ess_M2ePro_Model_Order_Proxy $proxyOrder)
    {
        $this->proxyOrder = $proxyOrder;
    }

    public function __destruct()
    {
        if (is_null($this->quote)) {
            return;
        }

        $store = $this->quote->getStore();

        foreach ($this->originalStoreConfig as $key => $value) {
            $store->setConfig($key, $value);
        }
    }

    // ########################################

    /**
     * Return magento quote object
     *
     * @return Mage_Sales_Model_Quote|null
     */
    public function getQuote()
    {
        return $this->quote;
    }

    // ########################################

    /**
     * Build quote object
     *
     * @throws Exception
     */
    public function buildQuote()
    {
        try {
            // do not change invoke order
            // --------------------
            $this->initializeQuote();
            $this->initializeCustomer();
            $this->initializeAddresses();

            $this->configureStore();
            $this->configureTaxCalculation();

            $this->initializeCurrency();
            $this->initializeShippingMethodData();
            $this->initializeQuoteItems();
            $this->initializePaymentMethodData();

            //$this->quote->setTotalsCollectedFlag(false);
            $this->quote->collectTotals()->save();
            $this->quote->reserveOrderId();
            // --------------------
        } catch (Exception $e) {
            $this->quote->setIsActive(false)->save();
            throw $e;
        }
    }

    // ########################################

    /**
     * Initialize quote objects
     */
    private function initializeQuote()
    {
        $this->quote = Mage::getModel('sales/quote');

        $this->quote->setCheckoutMethod($this->proxyOrder->getCheckoutMethod());
        $this->quote->setStore($this->proxyOrder->getStore());
        $this->quote->getStore()->setData('current_currency', $this->quote->getStore()->getBaseCurrency());
        $this->quote->save();

        Mage::getSingleton('checkout/session')->replaceQuote($this->quote);
    }

    // ########################################

    /**
     * Assign customer
     */
    private function initializeCustomer()
    {
        if ($this->proxyOrder->isCheckoutMethodGuest()) {
            $this->quote
                ->setCustomerId(null)
                ->setCustomerEmail($this->proxyOrder->getBuyerEmail())
                ->setCustomerFirstname($this->proxyOrder->getCustomerFirstName())
                ->setCustomerLastname($this->proxyOrder->getCustomerLastName())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        $this->quote->assignCustomer($this->proxyOrder->getCustomer());
    }

    // ########################################

    /**
     * Initialize shipping and billing address data
     */
    private function initializeAddresses()
    {
        // ----------
        $billingAddress = $this->quote->getBillingAddress();
        $billingAddress->addData($this->proxyOrder->getBillingAddressData());
        $billingAddress->implodeStreetAddress();

        $billingAddress->setLimitCarrier('m2eproshipping');
        $billingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $billingAddress->setCollectShippingRates(true);
        $billingAddress->setShouldIgnoreValidation($this->proxyOrder->shouldIgnoreBillingAddressValidation());
        // ----------

        // ----------
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($this->proxyOrder->getAddressData());
        $shippingAddress->implodeStreetAddress();

        $shippingAddress->setLimitCarrier('m2eproshipping');
        $shippingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $shippingAddress->setCollectShippingRates(true);
        // ----------
    }

    // ########################################

    /**
     * Initialize currency
     */
    private function initializeCurrency()
    {
        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        if ($currencyHelper->isConvertible($this->proxyOrder->getCurrency(), $this->quote->getStore())) {
            $currentCurrency = Mage::getModel('directory/currency')->load($this->proxyOrder->getCurrency());
        } else {
            $currentCurrency = $this->quote->getStore()->getBaseCurrency();
        }

        $this->quote->getStore()->setData('current_currency', $currentCurrency);
    }

    // ########################################

    /**
     * Configure store (invoked only after address, customer and store initialization and before price calculations)
     */
    private function configureStore()
    {
        /** @var $storeConfigurator Ess_M2ePro_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getModel('M2ePro/Magento_Quote_Store_Configurator');
        $storeConfigurator->init($this->quote, $this->proxyOrder);

        $this->originalStoreConfig = $storeConfigurator->getOriginalStoreConfig();

        $storeConfigurator->prepareStoreConfigForOrder();
    }

    // ########################################

    private function configureTaxCalculation()
    {
        // this prevents customer session initialization (which affects cookies)
        // see Mage_Tax_Model_Calculation::getCustomer()
        Mage::getSingleton('tax/calculation')->setCustomer($this->quote->getCustomer());
    }

    // ########################################

    /**
     * Initialize quote items objects
     *
     * @throws Exception
     */
    private function initializeQuoteItems()
    {
        foreach ($this->proxyOrder->getItems() as $item) {

            $this->clearQuoteItemsCache();

            /** @var $quoteItemBuilder Ess_M2ePro_Model_Magento_Quote_Item */
            $quoteItemBuilder = Mage::getModel('M2ePro/Magento_Quote_Item');
            $quoteItemBuilder->init($this->quote, $item);

            $product = $quoteItemBuilder->getProduct();
            $request = $quoteItemBuilder->getRequest();

            // see Mage_Sales_Model_Observer::substractQtyFromQuotes
            $this->quote->setItemsCount($this->quote->getItemsCount() + 1);
            $this->quote->setItemsQty((float)$this->quote->getItemsQty() + $request->getQty());

            $result = $this->quote->addProduct($product, $request);

            if (is_string($result)) {
                throw new Exception($result);
            }

            $quoteItem = $this->quote->getItemByProduct($product);

            if ($quoteItem !== false) {
                $quoteItem->setOriginalCustomPrice($item->getPrice());
                $quoteItem->setNoDiscount(1);
                $quoteItem->setGiftMessageId($quoteItemBuilder->getGiftMessageId());
                $quoteItem->setAdditionalData($quoteItemBuilder->getAdditionalData($quoteItem));
            }
        }
    }

    /**
     * Mage_Sales_Model_Quote_Address caches items after each collectTotals call. Some extensions calls collectTotals
     * after adding new item to quote in observers. So we need clear this cache before adding new item to quote.
     */
    private function clearQuoteItemsCache()
    {
        foreach ($this->quote->getAllAddresses() as $address) {

            /** @var $address Mage_Sales_Model_Quote_Address */

            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonominal');
        }
    }

    // ########################################

    /**
     * Initialize data for M2E Shipping Method
     */
    private function initializeShippingMethodData()
    {
        Mage::helper('M2ePro/Data_Global')->unsetValue('shipping_data');
        Mage::helper('M2ePro/Data_Global')->setValue('shipping_data', $this->proxyOrder->getShippingData());
    }

    // ########################################

    /**
     * Initialize data for M2E Payment Method
     */
    private function initializePaymentMethodData()
    {
        $quotePayment = $this->quote->getPayment();
        $quotePayment->importData($this->proxyOrder->getPaymentData());
    }

    // ########################################
}