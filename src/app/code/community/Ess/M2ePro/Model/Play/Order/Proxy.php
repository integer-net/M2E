<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    // ########################################

    /** @var $order Ess_M2ePro_Model_Play_Order */
    protected $order = NULL;

    // ########################################

    public function getCheckoutMethod()
    {
        if ($this->order->getPlayAccount()->isMagentoOrdersCustomerPredefined() ||
            $this->order->getPlayAccount()->isMagentoOrdersCustomerNew()) {
            return self::CHECKOUT_REGISTER;
        }

        return self::CHECKOUT_GUEST;
    }

    // ########################################

    public function isOrderNumberPrefixSourceChannel()
    {
        return $this->order->getPlayAccount()->isMagentoOrdersNumberSourceChannel();
    }

    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->order->getPlayAccount()->isMagentoOrdersNumberSourceMagento();
    }

    public function getChannelOrderNumber()
    {
        return $this->order->getPlayOrderId();
    }

    public function getOrderNumberPrefix()
    {
        if (!$this->order->getPlayAccount()->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        return $this->order->getPlayAccount()->getMagentoOrdersNumberPrefix();
    }

    // ########################################

    public function getBuyerEmail()
    {
        return $this->order->getBuyerEmail();
    }

    // ########################################

    public function getCustomer()
    {
        $customer = Mage::getModel('customer/customer');

        if ($this->order->getPlayAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->order->getPlayAccount()->getMagentoOrdersCustomerId());

            if (is_null($customer->getId())) {
                throw new Exception('Customer with ID specified in Play account settings does not exist.');
            }
        }

        if ($this->order->getPlayAccount()->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->order->getPlayAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if (!is_null($customer->getId())) {
                return $customer;
            }

            $customerInfo['website_id'] = $this->order->getPlayAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->order->getPlayAccount()->getMagentoOrdersCustomerNewGroupId();
//            $customerInfo['is_subscribed'] = $this->order->getPlayAccount()->isMagentoOrdersCustomerNewSubscribed();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();

//            if ($this->order->getPlayAccount()->isMagentoOrdersCustomerNewNotifyWhenCreated()) {
//                $customer->sendNewAccountEmail();
//            }
        }

        return $customer;
    }

    // ########################################

    public function getCurrency()
    {
        return $this->order->getCurrency();
    }

    // ########################################

    public function getPaymentData()
    {
        $paymentData = array(
            'method'            => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'    => Ess_M2ePro_Helper_Component_Play::NICK,
            'payment_method'    => '',
            'channel_order_id'  => $this->order->getPlayOrderId(),
            'channel_final_fee' => 0,
            'transactions'      => array()
        );

        return $paymentData;
    }

    // ########################################

    public function getShippingData()
    {
        return array(
            'shipping_method' => '',
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('Play Shipping')
        );
    }

    protected function getShippingPrice()
    {
        return $this->order->getShippingPrice();
    }

    // ########################################

    public function hasTax()
    {
        return false;
    }

    public function isSalesTax()
    {
        return false;
    }

    public function isVatTax()
    {
        return false;
    }

    // -----------------------------------------

    public function getProductPriceTaxRate()
    {
        return 0;
    }

    public function getShippingPriceTaxRate()
    {
        return 0;
    }

    // -----------------------------------------

    public function isProductPriceIncludeTax()
    {
        return false;
    }

    public function isShippingPriceIncludeTax()
    {
        return false;
    }

    // -----------------------------------------

    public function isTaxModeNone()
    {
        return $this->order->getPlayAccount()->isMagentoOrdersTaxModeNone();
    }

    public function isTaxModeMagento()
    {
        return $this->order->getPlayAccount()->isMagentoOrdersTaxModeMagento();
    }

    public function isTaxModeChannel()
    {
        return $this->order->getPlayAccount()->isMagentoOrdersTaxModeChannel();
    }

    // ########################################
}