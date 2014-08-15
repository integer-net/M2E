<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 */
class Ess_M2ePro_Model_Order extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // M2ePro_TRANSLATIONS
    // Magento Order was not created. Reason: %msg%
    // Magento Order #%order_id% was created.
    // Payment Transaction was not created. Reason: %msg%
    // Invoice was not created. Reason: %msg%
    // Invoice #%invoice_id% was created.
    // Shipment was not created. Reason: %msg%
    // Shipment #%shipment_id% was created.
    // Tracking details were not imported. Reason: %msg%
    // Tracking details were imported.
    // Magento Order #%order_id% was canceled.
    // Magento Order #%order_id% was not canceled. Reason: %msg%
    // Store does not exist.
    // Payment method "M2E Pro Payment" is disabled in magento configuration.
    // Shipping method "M2E Pro Shipping" is disabled in magento configuration.

    // ########################################

    const ADDITIONAL_DATA_KEY_IN_ORDER = 'm2epro_order';

    // ########################################

    private $account = NULL;

    private $marketplace = NULL;

    private $magentoOrder = NULL;

    private $shippingAddress = NULL;

    /** @var Ess_M2ePro_Model_Mysql4_Order_Item_Collection */
    private $itemsCollection = NULL;

    private $proxy = NULL;

    /** @var Ess_M2ePro_Model_Order_Reserve */
    private $reserve = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */
            $item->deleteInstance();
        }
        $this->deleteChildInstance();

        Mage::getResourceModel('M2ePro/Order_Change_Collection')
            ->addFieldToFilter('order_id', $this->getId())
            ->walk('deleteInstance');

        $this->account = NULL;
        $this->magentoOrder = NULL;
        $this->itemsCollection = NULL;
        $this->proxy = NULL;

        $this->delete();

        return true;
    }

    // ########################################

    public function getAccountId()
    {
        return $this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return $this->getData('marketplace_id');
    }

    public function getMagentoOrderId()
    {
        return $this->getData('magento_order_id');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function getReservationState()
    {
        return (int)$this->getData('reservation_state');
    }

    // ########################################

    public function setAccount(Ess_M2ePro_Model_Account $account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->account)) {
            $this->account = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(), 'Account', $this->getAccountId()
            );
        }

        return $this->account;
    }

    // ########################################

    public function setMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplace)) {
            $this->marketplace = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(), 'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplace;
    }

    // ########################################

    public function getStore()
    {
        return Mage::app()->getStore($this->getStoreId());
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_Reserve
     */
    public function getReserve()
    {
        if (is_null($this->reserve)) {
            $this->reserve = Mage::getModel('M2ePro/Order_Reserve', $this);
        }
        return $this->reserve;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Mysql4_Order_Item_Collection
     */
    public function getItemsCollection()
    {
        if (is_null($this->itemsCollection)) {
            $this->itemsCollection = Mage::helper('M2ePro/Component')
                ->getComponentCollection($this->getComponentMode(), 'Order_Item')
                ->addFieldToFilter('order_id', $this->getId());

            foreach ($this->itemsCollection as $item) {
                /** @var $item Ess_M2ePro_Model_Order_Item */
                $item->setOrder($this);
            }
        }

        return $this->itemsCollection;
    }

    // ---------------------------------------

    /**
     * Check whether the order has only single item ordered
     *
     * @return bool
     */
    public function isSingle()
    {
        return $this->getItemsCollection()->count() == 1;
    }

    /**
     * Check whether the order has multiple items ordered
     *
     * @return bool
     */
    public function isCombined()
    {
        return $this->getItemsCollection()->count() > 1;
    }

    // ---------------------------------------

    /**
     * Get instances of the channel items (Ebay_Item, Amazon_Item etc)
     *
     * @return array
     */
    public function getChannelItems()
    {
        $channelItems = array();

        foreach ($this->getItemsCollection()->getItems() as $item) {
            $channelItem = $item->getChildObject()->getChannelItem();

            if (is_null($channelItem)) {
                continue;
            }

            $channelItems[] = $channelItem;
        }

        return $channelItems;
    }

    // ---------------------------------------

    /**
     * Check whether the order has items, listed by M2E Pro (also true for mapped 3rd party listings)
     *
     * @return bool
     */
    public function hasListingItems()
    {
        $channelItems = $this->getChannelItems();

        return count($channelItems) > 0;
    }

    /**
     * Check whether the order has items, listed by 3rd party software
     *
     * @return bool
     */
    public function hasOtherListingItems()
    {
        $channelItems = $this->getChannelItems();

        return count($channelItems) != $this->getItemsCollection()->count();
    }

    // ########################################

    public function addLog($message, $type, array $params = array())
    {
        if (!empty($params)) {
            $message = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription($message, $params);
        }

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->createLogRecord($this->getComponentMode(), $this->getId(), $message, $type);
    }

    public function addSuccessLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS, $params);
    }

    public function addNoticeLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE, $params);
    }

    public function addWarningLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING, $params);
    }

    public function addErrorLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR, $params);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        if (is_null($this->shippingAddress)) {
            $this->shippingAddress = $this->getChildObject()->getShippingAddress();
        }

        return $this->shippingAddress;
    }

    // ########################################

    public function setMagentoOrder($order)
    {
        $this->magentoOrder = $order;
        return $this;
    }

    /**
     * @return null|Mage_Sales_Model_Order
     */
    public function getMagentoOrder()
    {
        if (is_null($this->getMagentoOrderId())) {
            return NULL;
        }

        if (is_null($this->magentoOrder)) {
            $this->magentoOrder = Mage::getModel('sales/order')->load($this->getMagentoOrderId());
        }

        return !is_null($this->magentoOrder->getId()) ? $this->magentoOrder : NULL;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_Proxy
     */
    public function getProxy()
    {
        if (is_null($this->proxy)) {
            $this->proxy = $this->getChildObject()->getProxy();
        }

        return $this->proxy;
    }

    // ########################################

    /**
     * Find the store, where order should be placed
     *
     * @param bool $strict
     * @throws Exception
     */
    public function associateWithStore($strict = true)
    {
        $storeId = $this->getStoreId() ? $this->getStoreId() : $this->getChildObject()->getAssociatedStoreId();
        $store = Mage::getModel('core/store')->load($storeId);

        if (is_null($store->getId())) {
            throw new Exception('Store does not exist.');
        }

        if ($this->getStoreId() != $store->getId()) {
            $this->setData('store_id', $store->getId())->save();
        }

        if (!Mage::getStoreConfig('payment/m2epropayment/active', $store) && $strict) {
            throw new Exception('Payment method "M2E Pro Payment" is disabled in magento configuration.');
        }

        if (!Mage::getStoreConfig('carriers/m2eproshipping/active', $store) && $strict) {
            throw new Exception('Shipping method "M2E Pro Shipping" is disabled in magento configuration.');
        }
    }

    // ########################################

    /**
     * Associate each order item with product in magento
     *
     * @param bool $strict
     * @throws Exception|null
     */
    public function associateItemsWithProducts($strict = true)
    {
        $exception = null;

        foreach ($this->getItemsCollection()->getItems() as $item) {
            try {
                /** @var $item Ess_M2ePro_Model_Order_Item */
                $item->associateWithProduct();
            } catch (Exception $e) {
                if (is_null($exception)) {
                    $exception = $e;
                }
            }
        }

        if ($strict && $exception) {
            throw $exception;
        }
    }

    // ########################################

    public function isReservable()
    {
        if (!is_null($this->getMagentoOrderId())) {
            return false;
        }

        if ($this->getAccount()->getChildObject()->getQtyReservationDays() <= 0) {
            return false;
        }

        if ($this->getReserve()->isPlaced()) {
            return false;
        }

        if (method_exists($this->getChildObject(), 'isReservable')) {
            return $this->getChildObject()->isReservable();
        }

        return true;
    }

    // ########################################

    public function canCreateMagentoOrder()
    {
        if (!is_null($this->getMagentoOrderId())) {
            return false;
        }

        if (!$this->getChildObject()->canCreateMagentoOrder()) {
            return false;
        }

        return true;
    }

    // ########################################

    private function beforeCreateMagentoOrder()
    {
        if (method_exists($this->getChildObject(), 'beforeCreateMagentoOrder')) {
            $this->getChildObject()->beforeCreateMagentoOrder();
        }

        $reserve = $this->getReserve();

        if ($reserve->isPlaced()) {
            $reserve->setFlag('order_reservation', true);
            $reserve->release();
        }
    }

    public function createMagentoOrder()
    {
        try {

            $this->beforeCreateMagentoOrder();

            // Store must be initialized before products
            // ---------------
            $this->associateWithStore();
            $this->associateItemsWithProducts();
            // ---------------

            // Create magento order
            // ---------------
            $proxy = $this->getProxy()->setStore($this->getStore());

            /** @var $magentoQuoteBuilder Ess_M2ePro_Model_Magento_Quote */
            $magentoQuoteBuilder = Mage::getModel('M2ePro/Magento_Quote', $proxy);
            $magentoQuoteBuilder->buildQuote();

            if ($this->getReserve()->getFlag('order_reservation')) {
                foreach ($magentoQuoteBuilder->getQuote()->getAllItems() as $quoteItem) {
                    $quoteItem->setData(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER . '_order_reservation', true);
                }
            }

            /** @var $magentoOrderBuilder Ess_M2ePro_Model_Magento_Order */
            $magentoOrderBuilder = Mage::getModel('M2ePro/Magento_Order', $magentoQuoteBuilder->getQuote());
            $magentoOrderBuilder->setAdditionalData(array(
                self::ADDITIONAL_DATA_KEY_IN_ORDER => $this
            ));
            $magentoOrderBuilder->buildOrder();

            $this->magentoOrder = $magentoOrderBuilder->getOrder();

            unset($magentoQuoteBuilder);
            unset($magentoOrderBuilder);
            // ---------------

        } catch (Exception $e) {

            Mage::dispatchEvent('m2epro_order_place_failure', array('order' => $this));

            $this->addErrorLog('Magento Order was not created. Reason: %msg%', array('msg' => $e->getMessage()));

            // reserve qty back only if it was canceled before the order creation process started
            // ---------------
            if ($this->isReservable() && $this->getReserve()->getFlag('order_reservation')) {
                $this->getReserve()->place();
            }
            // ---------------

            throw $e;
        }
    }

    public function afterCreateMagentoOrder()
    {
        // add history comments
        //------------------------------
        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateComments($this->getProxy()->getComments());
        $magentoOrderUpdater->finishUpdate();
        //------------------------------

        Mage::dispatchEvent('m2epro_order_place_success', array('order' => $this));

        $this->addSuccessLog('Magento Order #%order_id% was created.', array(
            '!order_id' => $this->getMagentoOrder()->getRealOrderId()
        ));

        if (method_exists($this->getChildObject(), 'afterCreateMagentoOrder')) {
            $this->getChildObject()->afterCreateMagentoOrder();
        }
    }

    public function updateMagentoOrderStatus()
    {
        if (is_null($this->getMagentoOrder())) {
            return;
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateStatus($this->getChildObject()->getStatusForMagentoOrder());
        $magentoOrderUpdater->finishUpdate();
    }

    // ########################################

    public function canCancelMagentoOrder()
    {
        $magentoOrder = $this->getMagentoOrder();

        if (is_null($magentoOrder) || $magentoOrder->isCanceled()) {
            return false;
        }

        return true;
    }

    public function cancelMagentoOrder()
    {
        if (!$this->canCancelMagentoOrder()) {
            return;
        }

        try {
            /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
            $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
            $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
            $magentoOrderUpdater->cancel();

            $this->addSuccessLog('Magento Order #%order_id% was canceled.', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId()
            ));
        } catch (Exception $e) {
            $this->addErrorLog('Magento Order #%order_id% was not canceled. Reason: %msg%', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId(),
                'msg' => $e->getMessage()
            ));
            throw $e;
        }
    }

    // ########################################

    public function createInvoice()
    {
        $invoice = null;

        try {
            $invoice = $this->getChildObject()->createInvoice();
        } catch (Exception $e) {
            $this->addErrorLog('Invoice was not created. Reason: %msg%', array('msg' => $e->getMessage()));
        }

        if (!is_null($invoice)) {
            $this->addSuccessLog('Invoice #%invoice_id% was created.', array(
                '!invoice_id' => $invoice->getIncrementId()
            ));
        }

        return $invoice;
    }

    // ########################################

    public function createShipment()
    {
        $shipment = null;

        try {
            $shipment = $this->getChildObject()->createShipment();
        } catch (Exception $e) {
            $this->addErrorLog('Shipment was not created. Reason: %msg%', array('msg' => $e->getMessage()));
        }

        if (!is_null($shipment)) {
            $this->addSuccessLog('Shipment #%shipment_id% was created.', array(
                '!shipment_id' => $shipment->getIncrementId()
            ));
        }

        return $shipment;
    }

    // ########################################
}