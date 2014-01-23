<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * Provides all needed information for order creation in magento.
 */
abstract class Ess_M2ePro_Model_Order_Proxy
{
    const CHECKOUT_GUEST    = 'guest';
    const CHECKOUT_REGISTER = 'register';

    // ########################################

    /** @var $order Ess_M2ePro_Model_Ebay_Order|Ess_M2ePro_Model_Amazon_Order|Ess_M2ePro_Model_Buy_Order */
    protected $order = NULL;

    protected $items = NULL;

    /** @var $store Mage_Core_Model_Store */
    protected $store = NULL;

    protected $addressData = array();

    // ########################################

    public function __construct(Ess_M2ePro_Model_Component_Child_Abstract $order)
    {
        $this->order = $order;
    }

    // ########################################

    /**
     * Return proxy objects for order items
     *
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     */
    public function getItems()
    {
        if (is_null($this->items)) {
            $items = array();

            foreach ($this->order->getParentObject()->getItemsCollection()->getItems() as $item) {
                $items[] = $item->getProxy();
            }

            $this->items = $this->mergeItems($items);
        }

        return $this->items;
    }

    /**
     * Order may have multiple items ordered, but some of them may be mapped to single product in magento.
     * We have to merge them to avoid qty and price calculation issues.
     *
     * @param Ess_M2ePro_Model_Order_Item_Proxy[] $items
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     */
    protected function mergeItems(array $items)
    {
        $unsetItems = array();

        foreach ($items as $key => &$item) {
            if (in_array($key, $unsetItems)) {
                continue;
            }

            foreach ($items as $nestedKey => $nestedItem) {
                if ($key == $nestedKey) {
                    continue;
                }

                if (!$item->equals($nestedItem)) {
                    continue;
                }

                $item->merge($nestedItem);

                $unsetItems[] = $nestedKey;
            }
        }

        foreach ($unsetItems as $key) {
            unset($items[$key]);
        }

        return $items;
    }

    // ########################################

    /**
     * Set store where order will be imported to
     *
     * @param Mage_Core_Model_Store $store
     * @return Ess_M2ePro_Model_Order_Proxy
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Return store order will be imported to
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->store)) {
            throw new Exception('Store is not set.');
        }

        return $this->store;
    }

    // ########################################

    /**
     * Return checkout method
     *
     * @abstract
     * @return string
     */
    abstract public function getCheckoutMethod();

    /**
     * Check whether checkout method is guest
     *
     * @return bool
     */
    public function isCheckoutMethodGuest()
    {
        return $this->getCheckoutMethod() == self::CHECKOUT_GUEST;
    }

    // ########################################

    /**
     * Return buyer email
     *
     * @abstract
     * @return string
     */
    abstract public function getBuyerEmail();

    /**
     * Return customer object
     *
     * @abstract
     * @return Mage_Customer_Model_Customer
     */
    abstract public function getCustomer();

    public function getCustomerFirstName()
    {
        $addressData = $this->getAddressData();

        return $addressData['firstname'];
    }

    public function getCustomerLastName()
    {
        $addressData = $this->getAddressData();

        return $addressData['lastname'];
    }

    /**
     * Return shipping address info
     *
     * @return array
     */
    public function getAddressData()
    {
        if (empty($this->addressData)) {
            $rawAddressData = $this->order->getShippingAddress()->getRawData();

            $recipientNameParts = $this->getNameParts($rawAddressData['recipient_name']);
            $this->addressData['firstname'] = $recipientNameParts['firstname'];
            $this->addressData['lastname'] = $recipientNameParts['lastname'];

            $customerNameParts = $this->getNameParts($rawAddressData['buyer_name']);
            $this->addressData['customer_firstname'] = $customerNameParts['firstname'];
            $this->addressData['customer_lastname'] = $customerNameParts['lastname'];

            $this->addressData['email'] = $rawAddressData['email'];
            $this->addressData['country_id'] = $rawAddressData['country_id'];
            $this->addressData['region'] = $rawAddressData['region'];
            $this->addressData['region_id'] = $this->order->getShippingAddress()->getRegionId();
            $this->addressData['city'] = $rawAddressData['city'];
            $this->addressData['postcode'] = $rawAddressData['postcode'];
            $this->addressData['telephone'] = $rawAddressData['telephone'];
            $this->addressData['street'] = !empty($rawAddressData['street']) ? $rawAddressData['street'] : array();
            $this->addressData['company'] = !empty($rawAddressData['company']) ? $rawAddressData['company'] : '';
            $this->addressData['save_in_address_book'] = 0;
        }

        return $this->addressData;
    }

    public function getBillingAddressData()
    {
        return $this->getAddressData();
    }

    /**
     * Check whether the billing address information should be validated or not
     *
     * @return bool
     */
    public function shouldIgnoreBillingAddressValidation()
    {
        return false;
    }

    // ########################################

    protected function getNameParts($fullName)
    {
        $fullName = trim($fullName);

        $spacePosition = strpos($fullName, ' ');
        $spacePosition === false && $spacePosition = strlen($fullName);

        $firstName = trim(substr($fullName, 0, $spacePosition));
        $lastName = trim(substr($fullName, $spacePosition + 1));

        return array(
            'firstname' => $firstName ? $firstName : 'N/A',
            'lastname'  => $lastName ? $lastName : 'N/A'
        );
    }

    // ########################################

    /**
     * Return order currency code
     *
     * @abstract
     * @return string
     */
    abstract public function getCurrency();

    public function convertPrice($price)
    {
        return Mage::getSingleton('M2ePro/Currency')
            ->convertPrice($price, $this->getCurrency(), $this->getStore());
    }

    public function convertPriceToBase($price)
    {
        return Mage::getSingleton('M2ePro/Currency')
            ->convertPriceToBaseCurrency($price, $this->getCurrency(), $this->getStore());
    }

    // ########################################

    /**
     * Return payment data
     *
     * @abstract
     * @return array
     */
    abstract public function getPaymentData();

    /**
     * Return shipping data
     *
     * @abstract
     * @return array
     */
    abstract public function getShippingData();

    abstract protected function getShippingPrice();

    /**
     * Return shipping price converted to the base store currency
     *
     * @return float
     */
    protected function getBaseShippingPrice()
    {
        return $this->convertPriceToBase($this->getShippingPrice());
    }

    // ########################################

    /**
     * Return comments, which should be added to the order history
     *
     * @return array
     */
    public function getComments()
    {
        return array_merge($this->getGeneralComments(), $this->getChannelComments());
    }

    /**
     * Return channel related order comments
     *
     * @return array
     */
    public function getChannelComments()
    {
        return array();
    }

    /**
     * Return general order comments
     *
     * @return array
     */
    public function getGeneralComments()
    {
        $store = $this->getStore();

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');
        $currencyConvertRate = $currencyHelper->getConvertRateFromBase($this->getCurrency(), $store);

        if ($currencyHelper->isBase($this->getCurrency(), $store)) {
            return array();
        }

        $comments = array();

        if (!$currencyHelper->isAllowed($this->getCurrency(), $store)) {
            $comments[] = <<<COMMENT
<b>Attention!</b> The Order Prices are incorrect.
Conversion was not performed as "{$this->getCurrency()}" currency is not enabled.
Default currency "{$store->getBaseCurrencyCode()}" was used instead.
Please, enable currency in System -> Configuration -> Currency Setup.
COMMENT;
        } elseif ($currencyConvertRate == 0) {
            $comments[] = <<<COMMENT
<b>Attention!</b> The Order Prices are incorrect.
Conversion was not performed as there's no rate for "{$this->getCurrency()}".
Default currency "{$store->getBaseCurrencyCode()}" was used instead.
Please, add currency convert rate in System -> Manage Currency -> Rates.
COMMENT;
        } else {
            $comments[] = <<<COMMENT
Because the Order currency is different from the Store currency,
the conversion from <b>"{$this->getCurrency()}" to "{$store->getBaseCurrencyCode()}"</b> was performed
using <b>{$currencyConvertRate}</b> as a rate.
COMMENT;
        }

        return $comments;
    }

    // ########################################

    /**
     * Return tax rate
     *
     * @abstract
     * @return float
     */
    abstract public function getTaxRate();

    /**
     * Check whether order has Tax (not VAT)
     *
     * @abstract
     * @return bool
     */
    abstract public function hasTax();

    /**
     * Check whether order has VAT (value added tax)
     *
     * @abstract
     * @return bool
     */
    abstract public function hasVat();

    /**
     * Check whether shipping price includes tax
     *
     * @abstract
     * @return bool
     */
    abstract public function isShippingPriceIncludesTax();

    /**
     * Check whether tax mode option is set to "None" in Account settings
     *
     * @abstract
     * @return bool
     */
    abstract public function isTaxModeNone();

    /**
     * Check whether tax mode option is set to "Channel" in Account settings
     *
     * @abstract
     * @return bool
     */
    abstract public function isTaxModeChannel();

    /**
     * Check whether tax mode option is set to "Magento" in Account settings
     *
     * @abstract
     * @return bool
     */
    abstract public function isTaxModeMagento();

    /**
     * Check whether tax mode option is set to "Mixed" in Account settings
     *
     * @abstract
     * @return bool
     */
    public function isTaxModeMixed()
    {
        return !$this->isTaxModeNone() &&
               !$this->isTaxModeChannel() &&
               !$this->isTaxModeMagento();
    }

    // ########################################
}