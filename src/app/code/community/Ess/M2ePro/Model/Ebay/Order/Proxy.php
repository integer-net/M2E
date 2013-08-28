<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var $order Ess_M2ePro_Model_Ebay_Order */
    protected $order = NULL;

    // ########################################

    public function getCheckoutMethod()
    {
        if ($this->order->getEbayAccount()->isMagentoOrdersCustomerNew() ||
            $this->order->getEbayAccount()->isMagentoOrdersCustomerPredefined()) {
            return self::CHECKOUT_REGISTER;
        }

        return self::CHECKOUT_GUEST;
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

        if ($this->order->getEbayAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->order->getEbayAccount()->getMagentoOrdersCustomerId());

            if (is_null($customer->getId())) {
                throw new Exception('Customer with ID specified in eBay account settings does not exist.');
            }
        }

        if ($this->order->getEbayAccount()->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if (!is_null($customer->getId())) {
                return $customer;
            }

            $customerInfo['website_id'] = $this->order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->order->getEbayAccount()->getMagentoOrdersCustomerNewGroupId();
//            $customerInfo['is_subscribed'] = $this->order->getEbayAccount()->isMagentoOrdersCustomerNewSubscribed();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();

//            if ($this->order->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenCreated()) {
//                $customer->sendNewAccountEmail('registered');
//            }
        }

        return $customer;
    }

    // ########################################

    public function getCurrency()
    {
        return $this->order->getCurrency();
    }

    public function getPaymentData()
    {
        $paymentMethodTitle = $this->order->getPaymentMethod();
        $paymentMethodTitle == 'None' && $paymentMethodTitle = Mage::helper('M2ePro')->__('Not Selected Yet');

        $paymentData = array(
            'method'            => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'    => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'payment_method'    => $paymentMethodTitle,
            'channel_order_id'  => $this->order->getEbayOrderId(),
            'channel_final_fee' => $this->convertPrice($this->order->getFinalFee()),
            'transactions'      => $this->getPaymentTransactions()
        );

        return $paymentData;
    }

    public function getPaymentTransactions()
    {
        $transactions = array();

        foreach ($this->order->getExternalTransactionsCollection() as $externalTransaction) {
            $transactions[] = array(
                'transaction_id'   => $externalTransaction->getData('transaction_id'),
                'fee'              => $this->convertPrice((float)$externalTransaction->getData('fee')),
                'sum'              => $this->convertPrice((float)$externalTransaction->getData('sum')),
                'transaction_date' => $externalTransaction->getData('transaction_date')
            );
        }

        return $transactions;
    }

    // ########################################

    public function getShippingData()
    {
        return array(
            'shipping_method' => $this->order->getShippingMethod(),
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('eBay Shipping')
        );
    }

    protected function getShippingPrice()
    {
        $price = $this->order->getShippingPrice();

        if ($this->isTaxModeNone() && !$this->isShippingPriceIncludesTax()) {
            $taxAmount = Mage::getSingleton('tax/calculation')
                ->calcTaxAmount($price, $this->getTaxRate(), false, false);

            $price += $taxAmount;
        }

        return $price;
    }

    // ########################################

    public function getChannelComments()
    {
        $comments = array();

        if ($this->order->getCheckoutBuyerMessage() != '') {
            $comment = '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ': </b>';
            $comment .= $this->order->getCheckoutBuyerMessage() . '<br />';

            $comments[] = $comment;
        }

        return $comments;
    }

    // ########################################

    public function getTaxRate()
    {
        if ($this->order->getEbayAccount()->isMagentoOrdersTaxModeChannel() ||
            $this->order->getEbayAccount()->isMagentoOrdersTaxModeMixed()) {
            return $this->order->getTaxRate();
        }

        return 0;
    }

    public function hasTax()
    {
        return $this->order->hasTax();
    }

    public function hasVat()
    {
        return $this->order->hasVat();
    }

    public function isShippingPriceIncludesTax()
    {
        return $this->order->isShippingPriceIncludesTax();
    }

    public function isTaxModeNone()
    {
        return $this->order->getEbayAccount()->isMagentoOrdersTaxModeNone();
    }

    public function isTaxModeChannel()
    {
        return $this->order->getEbayAccount()->isMagentoOrdersTaxModeChannel();
    }

    public function isTaxModeMagento()
    {
        return $this->order->getEbayAccount()->isMagentoOrdersTaxModeMagento();
    }

    // ########################################
}