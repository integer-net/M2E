<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Order_Proxy
{
    // ########################################

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
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     */
    public function getItems()
    {
        if (is_null($this->items)) {
            $items = array();

            foreach ($this->order->getParentObject()->getItemsCollection()->getItems() as $item) {
                $proxyItem = $item->getProxy();
                if ($proxyItem->getQty() <= 0) {
                    continue;
                }

                $items[] = $proxyItem;
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

    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->store = $store;
        return $this;
    }

    public function getStore()
    {
        if (is_null($this->store)) {
            throw new Exception('Store is not set.');
        }

        return $this->store;
    }

    // ########################################

    abstract public function getCheckoutMethod();

    public function isCheckoutMethodGuest()
    {
        return $this->getCheckoutMethod() == self::CHECKOUT_GUEST;
    }

    // ########################################

    abstract public function isOrderNumberPrefixSourceMagento();

    abstract public function isOrderNumberPrefixSourceChannel();

    abstract public function getChannelOrderNumber();

    abstract public function getOrderNumberPrefix();

    // ########################################

    abstract public function getBuyerEmail();

    // ########################################

    /**
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

    // ########################################

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

    abstract public function getPaymentData();

    // ########################################

    abstract public function getShippingData();

    abstract protected function getShippingPrice();

    protected function getBaseShippingPrice()
    {
        return $this->convertPriceToBase($this->getShippingPrice());
    }

    // ########################################

    public function getComments()
    {
        return array_merge($this->getGeneralComments(), $this->getChannelComments());
    }

    public function getChannelComments()
    {
        return array();
    }

    public function getGeneralComments()
    {
        $store = $this->getStore();

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');
        $currencyConvertRate = $currencyHelper->getConvertRateFromBase($this->getCurrency(), $store, 4);

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

    abstract public function hasTax();

    abstract public function isSalesTax();

    abstract public function isVatTax();

    // -----------------------------------------

    abstract public function getProductPriceTaxRate();

    abstract public function getShippingPriceTaxRate();

    // -----------------------------------------

    abstract public function isProductPriceIncludeTax();

    abstract public function isShippingPriceIncludeTax();

    // -----------------------------------------

    abstract public function isTaxModeNone();

    abstract public function isTaxModeChannel();

    abstract public function isTaxModeMagento();

    public function isTaxModeMixed()
    {
        return !$this->isTaxModeNone() &&
               !$this->isTaxModeChannel() &&
               !$this->isTaxModeMagento();
    }

    // ########################################
}