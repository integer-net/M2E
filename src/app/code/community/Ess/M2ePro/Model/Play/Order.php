<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Play_Order getResource()
 */
class Ess_M2ePro_Model_Play_Order extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    const STATUS_PENDING  = 0;
    const STATUS_SOLD     = 1;
    const STATUS_POSTED   = 2;
    const STATUS_CANCELED = 3;
    const STATUS_REFUNDED = 4;

    // ########################################

    private $subTotalPrice = NULL;

    private $grandTotalPrice = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Order');
    }

    // ########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Play_Order_Proxy', $this);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Play_Account
     */
    public function getPlayAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    // ########################################

    public function getPlayOrderId()
    {
        return $this->getData('play_order_id');
    }

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getShippingPrice()
    {
        if (is_null($this->getParentObject()->getMagentoOrderId()) && $this->getData('shipping_price') == 0) {
            $this->setData('shipping_price', $this->generateShippingPrice())->save();
        }
        return (float)$this->getData('shipping_price');
    }

    /**
     * @return Ess_M2ePro_Model_Play_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        $address = json_decode($this->getData('shipping_address'), true);
        $address = is_array($address) ? $address : array();

        return Mage::getModel('M2ePro/Play_Order_ShippingAddress', $this->getParentObject())
            ->setData($address);
    }

    // ########################################

    public function isPending()
    {
        return $this->getData('status') == self::STATUS_PENDING;
    }

    public function isSold()
    {
        return $this->getData('status') == self::STATUS_SOLD;
    }

    public function isPosted()
    {
        return $this->getData('status') == self::STATUS_POSTED;
    }

    public function isCanceled()
    {
        return $this->getData('status') == self::STATUS_CANCELED;
    }

    public function isRefunded()
    {
        return $this->getData('status') == self::STATUS_REFUNDED;
    }

    //-----------------------------------------

    public function isShippingCompleted()
    {
        return (bool)(int)$this->getData('shipping_status');
    }

    // ########################################

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
            $this->grandTotalPrice += $this->getShippingPrice();
        }

        return round($this->grandTotalPrice, 2);
    }

    // ########################################

    public function generateShippingPrice()
    {
        $shippingPrice = 0;

        foreach ($this->getParentObject()->getItemsCollection() as $item) {
            $shippingPrice += Mage::getResourceSingleton('M2ePro/Play_Order_Item')
                ->getShippingPriceFromListingProduct($item->getChildObject());
        }

        return $shippingPrice;
    }

    // ########################################

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isSold()    && $status = $this->getPlayAccount()->getMagentoOrdersStatusProcessing();
        $this->isPosted()  && $status = $this->getPlayAccount()->getMagentoOrdersStatusShipped();

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
            $storeId = $this->getPlayAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------
        } else {
            // M2E order
            // ---------------
            if ($this->getPlayAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getPlayAccount()->getMagentoOrdersListingsStoreId();
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

    /**
     * Check possibility for magento order creation
     *
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        if ($this->isPending() || $this->isCanceled() || $this->isRefunded()) {
            return false;
        }

        return true;
    }

    public function beforeCreateMagentoOrder()
    {
        if ($this->isPending() || $this->isCanceled() || $this->isRefunded()) {
            throw new Exception(
                'Magento Order creation is not allowed for Pending, Canceled and Refunded Play.com Orders.'
            );
        }
    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getPlayAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            $this->getParentObject()->getMagentoOrder()->sendNewOrderEmail();
        }
    }

    // ########################################

    public function canCreateInvoice()
    {
        if (!$this->getPlayAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        if ($this->isPending() || $this->isCanceled() || $this->isRefunded()) {
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

        if ($this->getPlayAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $invoice->sendEmail();
        }

        return $invoice;
    }

    // ########################################

    public function canCreateShipment()
    {
        if (!$this->getPlayAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        if (!$this->isPosted()) {
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

    public function canUpdateShippingStatus()
    {
        if ($this->isPending() || $this->isCanceled() || $this->isRefunded()) {
            return false;
        }

        return true;
    }

    // ########################################

    public function deleteInstance()
    {
        return $this->delete();
    }

    // ########################################
}