<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Order getResource()
 */
class Ess_M2ePro_Model_Ebay_Order extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const CHECKOUT_STATUS_INCOMPLETE = 0;
    const CHECKOUT_STATUS_COMPLETED  = 1;

    const PAYMENT_STATUS_NOT_SELECTED = 0;
    const PAYMENT_STATUS_ERROR        = 1;
    const PAYMENT_STATUS_PROCESS      = 2;
    const PAYMENT_STATUS_COMPLETED    = 3;

    const SHIPPING_STATUS_NOT_SELECTED = 0;
    const SHIPPING_STATUS_PROCESSING   = 1;
    const SHIPPING_STATUS_COMPLETED    = 2;

    const TAX_SHIPPING_EXCLUDED = 0;
    const TAX_SHIPPING_INCLUDED = 1;

    // ->__('Magento Order was canceled.');
    // ->__('Magento Order cannot be canceled.');

    // ########################################

    /** @var $externalTransactionsCollection Ess_M2ePro_Model_Mysql4_Ebay_Order_ExternalTransaction_Collection */
    private $externalTransactionsCollection = NULL;

    private $subTotalPrice = NULL;

    private $grandTotalPrice = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Order');
    }

    // ########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Ebay_Order_Proxy', $this);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    // ########################################

    public function getExternalTransactionsCollection()
    {
        if (is_null($this->externalTransactionsCollection)) {
            $this->externalTransactionsCollection = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction')
                ->getCollection()
                ->addFieldToFilter('order_id', $this->getData('order_id'));
        }

        return $this->externalTransactionsCollection;
    }

    //-----------------------------------------

    /**
     * @return bool
     */
    public function hasExternalTransactions()
    {
        return $this->getExternalTransactionsCollection()->count() > 0;
    }

    // ########################################

    public function getEbayOrderId()
    {
        return $this->getData('ebay_order_id');
    }

    public function getSellingManagerRecordNumber()
    {
        return $this->getData('selling_manager_record_number');
    }

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getBuyerUserId()
    {
        return $this->getData('buyer_user_id');
    }

    public function getCheckoutBuyerMessage()
    {
        return $this->getData('checkout_buyer_message');
    }

    public function getPaymentMethod()
    {
        return $this->getData('payment_method');
    }

    public function getShippingMethod()
    {
        return $this->getData('shipping_method');
    }

    public function getShippingPrice()
    {
        return (float)$this->getData('shipping_price');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        $address = @unserialize($this->getData('shipping_address'));

        if (is_array($address)) {
            // compatibility with M2E 3.x
            // -------------
            $address = array(
                'country_code' => $address['country_id'],
                'country_name' => null,
                'city'         => $address['city'],
                'state'        => $address['region_id'],
                'postal_code'  => $address['postcode'],
                'phone'        => $address['telephone'],
                'street'       => $address['street']
            );
            // -------------
        } else {
            $address = json_decode($this->getData('shipping_address'), true);
        }

        $address = is_array($address) ? $address : array();

        return Mage::getModel('M2ePro/Ebay_Order_ShippingAddress', $this->getParentObject())
            ->setData($address);
    }

    /**
     * @return array
     */
    public function getShippingTrackingDetails()
    {
        // compatibility with M2E 3.x
        // -------------
        $trackingDetails = @unserialize($this->getData('shipping_tracking_details'));
        $trackingDetails === false && $trackingDetails = json_decode($this->getData('shipping_tracking_details'), true);
        $trackingDetails = is_array($trackingDetails) ? $trackingDetails : array();
        // -------------

        return $trackingDetails;
    }

    public function getGlobalShippingDetails()
    {
        return $this->getSettings('global_shipping_details');
    }

    public function isUseGlobalShippingProgram()
    {
        return count($this->getGlobalShippingDetails()) != 0;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_ShippingAddress
     */
    public function getGlobalShippingWarehouseAddress()
    {
        if (!$this->isUseGlobalShippingProgram()) {
            return null;
        }

        $globalShippingData = $this->getGlobalShippingDetails();
        $warehouseAddress = isset($globalShippingData['warehouse_address']) ? $globalShippingData['warehouse_address'] :
            array();

        return Mage::getModel('M2ePro/Ebay_Order_ShippingAddress', $this->getParentObject())
            ->setData($warehouseAddress);
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getFinalFee()
    {
        return (float)$this->getData('final_fee');
    }

    public function getTaxRate()
    {
        return (float)$this->getData('tax_rate');
    }

    public function getTaxAmount()
    {
        return (float)$this->getData('tax_amount');
    }

    //-----------------------------------------

    /**
     * @return bool
     */
    public function isShippingPriceIncludesTax()
    {
        if ($this->getTaxRate() <= 0) {
            return false;
        }

        return (int)$this->getData('tax_includes_shipping') == self::TAX_SHIPPING_EXCLUDED;
    }

    //-----------------------------------------

    /**
     * @return bool
     */
    public function isCheckoutCompleted()
    {
        return (int)$this->getData('checkout_status') == self::CHECKOUT_STATUS_COMPLETED;
    }

    //-----------------------------------------

    /**
     * @return bool
     */
    public function isPaymentCompleted()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isPaymentMethodNotSelected()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_NOT_SELECTED;
    }

    /**
     * @return bool
     */
    public function isPaymentInProcess()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_PROCESS;
    }

    /**
     * @return bool
     */
    public function isPaymentFailed()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_ERROR;
    }

    /**
     * @return bool
     */
    public function isPaymentStatusUnknown()
    {
        return !$this->isPaymentCompleted() &&
               !$this->isPaymentMethodNotSelected() &&
               !$this->isPaymentInProcess() &&
               !$this->isPaymentFailed();
    }

    //-----------------------------------------

    /**
     * @return bool
     */
    public function isShippingCompleted()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isShippingMethodNotSelected()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_NOT_SELECTED;
    }

    /**
     * @return bool
     */
    public function isShippingInProcess()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isShippingStatusUnknown()
    {
        return !$this->isShippingCompleted() &&
               !$this->isShippingMethodNotSelected() &&
               !$this->isShippingInProcess();
    }

    //-----------------------------------------

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->getTaxRate() > 0 && $this->getTaxAmount() > 0;
    }

    /**
     * @return bool
     */
    public function hasVat()
    {
        return $this->getTaxRate() > 0 && $this->getTaxAmount() == 0;
    }

    //-----------------------------------------

    public function getRealTaxAmount()
    {
        if ($this->getTaxRate() == 0) {
            return 0;
        }

        if ($this->hasTax()) {
            return $this->getTaxAmount();
        }

        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');
        $taxAmount = 0;

        foreach ($this->getParentObject()->getItemsCollection() as $item) {
            $taxAmount += $taxCalculator->calcTaxAmount(
                $item->getData('price'), $this->getTaxRate(), true, true
            );
        }

        $taxAmount += $taxCalculator->calcTaxAmount(
            $this->getShippingPrice(),
            $this->getTaxRate(),
            $this->isShippingPriceIncludesTax(),
            true
        );

        return $taxAmount;
    }

    public function getSubtotalPrice()
    {
        if (is_null($this->subTotalPrice)) {
            $this->subTotalPrice = $this->getResource()->getItemsTotal($this->getId());
        }

        return $this->subTotalPrice;
    }

    public function getGrandTotalPrice()
    {
        if (is_null($this->grandTotalPrice)) {
            $this->grandTotalPrice = $this->getSubtotalPrice();
            $this->grandTotalPrice += round((float)$this->getData('shipping_price'), 2);
            $this->grandTotalPrice += round((float)$this->getData('tax_amount'), 2);
        }

        return $this->grandTotalPrice;
    }

    // ########################################

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isCheckoutCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusNew();
        $this->isPaymentCompleted()  && $status = $this->getEbayAccount()->getMagentoOrdersStatusPaid();
        $this->isShippingCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusShipped();

        return $status;
    }

    // ########################################

    public function getAssociatedStoreId()
    {
        $storeId = NULL;

        $channelItems = $this->getParentObject()->getChannelItems();

        if (count($channelItems) == 0) {
            // 3rd party order
            // ---------------
            $storeId = $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------
        } else {
            // M2E order
            // ---------------
            if ($this->getEbayAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getEbayAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $firstChannelItem = reset($channelItems);
                $storeId = $firstChannelItem->getStoreId();
            }
            // ---------------
        }

        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        return $storeId;
    }

    // ########################################

    public function canCreateMagentoOrder()
    {
        $ebayAccount = $this->getEbayAccount();

        if (!$this->isCheckoutCompleted()
            && ($ebayAccount->shouldCreateMagentoOrderWhenCheckedOut()
                || $ebayAccount->shouldCreateMagentoOrderWhenCheckedOutAndPaid())
        ) {
            return false;
        }

        if (!$this->isPaymentCompleted()
            && ($ebayAccount->shouldCreateMagentoOrderWhenPaid()
                || $ebayAccount->shouldCreateMagentoOrderWhenCheckedOutAndPaid())
        ) {
            return false;
        }

        return true;
    }

    // ########################################

    public function afterCreateMagentoOrder()
    {
        if ($this->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            $this->getParentObject()->getMagentoOrder()->sendNewOrderEmail();
        }
    }

    // ########################################

    public function canCreatePaymentTransaction()
    {
        if ($this->getExternalTransactionsCollection()->count() <= 0) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if (is_null($magentoOrder)) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    public function createPaymentTransactions()
    {
        if (!$this->canCreatePaymentTransaction()) {
            return NULL;
        }

        /** @var $proxy Ess_M2ePro_Model_Ebay_Order_Proxy */
        $proxy = $this->getParentObject()->getProxy();
        $proxy->setStore($this->getParentObject()->getStore());

        foreach ($proxy->getPaymentTransactions() as $transaction) {
            try {
                /** @var $paymentTransactionBuilder Ess_M2ePro_Model_Magento_Order_PaymentTransaction */
                $paymentTransactionBuilder = Mage::getModel('M2ePro/Magento_Order_PaymentTransaction');
                $paymentTransactionBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
                $paymentTransactionBuilder->setData($transaction);
                $paymentTransactionBuilder->buildPaymentTransaction();
            } catch (Exception $e) {
                $this->getParentObject()->addErrorLog(
                    'Payment Transaction was not created. Reason: %msg%', array('msg' => $e->getMessage())
                );
            }
        }
    }

    // ########################################

    public function canCreateInvoice()
    {
        if (!$this->isPaymentCompleted()) {
            return false;
        }

        if (!$this->getEbayAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if (is_null($magentoOrder)) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    public function createInvoice()
    {
        if (!$this->canCreateInvoice()) {
            return NULL;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        // Create invoice
        // -------------
        /** @var $invoiceBuilder Ess_M2ePro_Model_Magento_Order_Invoice */
        $invoiceBuilder = Mage::getModel('M2ePro/Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();
        // -------------

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $invoice->sendEmail();
        }

        return $invoice;
    }

    // ########################################

    public function canCreateShipment()
    {
        if (!$this->isShippingCompleted()) {
            return false;
        }

        if (!$this->getEbayAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if (is_null($magentoOrder)) {
            return false;
        }

        if ($magentoOrder->hasShipments() || !$magentoOrder->canShip()) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    public function createShipment()
    {
        if (!$this->canCreateShipment()) {
            return NULL;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        // Create shipment
        // -------------
        /** @var $shipmentBuilder Ess_M2ePro_Model_Magento_Order_Shipment */
        $shipmentBuilder = Mage::getModel('M2ePro/Magento_Order_Shipment');
        $shipmentBuilder->setMagentoOrder($magentoOrder);
        $shipmentBuilder->buildShipment();
        // -------------

        return $shipmentBuilder->getShipment();
    }

    // ########################################

    public function canCreateTracks()
    {
        $trackingDetails = $this->getShippingTrackingDetails();
        if (count($trackingDetails) == 0) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if (is_null($magentoOrder)) {
            return false;
        }

        if (!$magentoOrder->hasShipments()) {
            return false;
        }

        return true;
    }

    public function createTracks()
    {
        if (!$this->canCreateTracks()) {
            return NULL;
        }

        $tracks = array();

        try {
            // Create tracks
            // -------------
            /** @var $trackBuilder Ess_M2ePro_Model_Magento_Order_Shipment_Track */
            $trackBuilder = Mage::getModel('M2ePro/Magento_Order_Shipment_Track');
            $trackBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
            $trackBuilder->setTrackingDetails($this->getShippingTrackingDetails());
            $trackBuilder->setSupportedCarriers(Mage::helper('M2ePro/Component_Ebay')->getCarriers());
            $trackBuilder->buildTracks();
            $tracks = $trackBuilder->getTracks();
            // -------------
        } catch (Exception $e) {
            $this->getParentObject()->addErrorLog(
                'Tracking details were not imported. Reason: %msg%', array('msg' => $e->getMessage())
            );
        }

        if (count($tracks) > 0) {
            $this->getParentObject()->addSuccessLog('Tracking details were imported.');
        }

        return $tracks;
    }

    // ########################################

    private function processConnector($action, array $params = array())
    {
        /** @var $dispatcher Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Connector_Ebay_Order_Dispatcher');

        return $dispatcher->process($action, $this->getParentObject(), $params);
    }

    //-----------------------------------------

    public function canUpdatePaymentStatus()
    {
        return !$this->isPaymentCompleted() && !$this->isPaymentStatusUnknown();
    }

    public function updatePaymentStatus(array $params = array())
    {
        if (!$this->canUpdatePaymentStatus()) {
            return false;
        }
        return $this->processConnector(Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_PAY, $params);
    }

    //-----------------------------------------

    public function canUpdateShippingStatus(array $trackingDetails = array())
    {
        if (!$this->isPaymentCompleted() || $this->isShippingStatusUnknown()) {
            return false;
        }

        if (!$this->isShippingMethodNotSelected() && !$this->isShippingInProcess() && empty($trackingDetails)) {
            return false;
        }

        return true;
    }

    public function updateShippingStatus(array $trackingDetails = array())
    {
        $params = array();
        $action = Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP;

        if (!empty($trackingDetails['tracking_number'])) {
            $action = Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK;

            // Prepare tracking information
            // -------------
            $params['tracking_number'] = $trackingDetails['tracking_number'];
            $params['carrier_code'] = Mage::helper('M2ePro/Component_Ebay')->getCarrierTitle(
                $trackingDetails['carrier_code'], $trackingDetails['carrier_title']
            );
            // -------------
        }

        return $this->processConnector($action, $params);
    }

    // ########################################

    public function deleteInstance()
    {
        $table = Mage::getResourceModel('M2ePro/Ebay_Order_ExternalTransaction')->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($table, array('order_id = ?'=>$this->getData('order_id')));

        return $this->delete();
    }

    // ########################################
}