<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
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

    public function getAddressData()
    {
        if (!$this->order->isUseGlobalShippingProgram()) {
            return parent::getAddressData();
        }

        $rawAddressData = $this->order->getGlobalShippingWarehouseAddress()->getRawData();
        $globalShippingDetails = $this->order->getGlobalShippingDetails();

        $addressData = array();

        $recipientNameParts = $this->getNameParts($rawAddressData['recipient_name']);
        $addressData['firstname'] = $recipientNameParts['firstname'];
        $addressData['lastname']  = $recipientNameParts['lastname'];

        $customerNameParts = $this->getNameParts($rawAddressData['buyer_name']);
        $addressData['customer_firstname'] = $customerNameParts['firstname'];
        $addressData['customer_lastname']  = $customerNameParts['lastname'];

        $addressData['email']      = $rawAddressData['email'];
        $addressData['country_id'] = $rawAddressData['country_id'];
        $addressData['region']     = $rawAddressData['region'];
        $addressData['region_id']  = $this->order->getGlobalShippingWarehouseAddress()->getRegionId();
        $addressData['city']       = $rawAddressData['city'];
        $addressData['postcode']   = $rawAddressData['postcode'];
        $addressData['telephone']  = $rawAddressData['telephone'];
        $addressData['company']    = !empty($rawAddressData['company']) ? $rawAddressData['company'] : '';

        // Adding reference id for global shipping into street array
        // ----------------------------------------------
        $streetParts = !empty($rawAddressData['street']) ? $rawAddressData['street'] : array();
        $referenceId = 'Ref #'.$globalShippingDetails['warehouse_address']['reference_id'];

        $addressData['street'] = array();
        if (count($streetParts) >= 2) {
            $addressData['street'] = array(
                $referenceId,
                implode(' ', $streetParts),
            );
        } else {
            array_unshift($streetParts, $referenceId);
            $addressData['street'] = $streetParts;
        }
        // ----------------------------------------------

        $addressData['save_in_address_book'] = 0;

        return $addressData;
    }

    public function getBillingAddressData()
    {
        if (!$this->order->isUseGlobalShippingProgram()) {
            return parent::getBillingAddressData();
        }

        return parent::getAddressData();
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

        if ($this->order->isUseGlobalShippingProgram()) {
            $comments[] = '<b>'.Mage::helper('M2ePro')->__('Global Shipping Program is used for this Order').'</b><br />';
        }

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
        if ($this->order->isUseGlobalShippingProgram()) {
            return true;
        }

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