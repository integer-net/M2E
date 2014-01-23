<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Builder extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const EBAY_CHECKOUT_STATUS_SINGLE_COMPLETE   = 'CheckoutComplete';
    const EBAY_CHECKOUT_STATUS_COMBINED_COMPLETE = 'Complete';

    const EBAY_PAYMENT_METHOD_NONE      = 'None';
    const EBAY_PAYMENT_STATUS_SUCCEEDED = 'NoPaymentFailure';

    const UPDATE_COMPLETED_CHECKOUT = 1;
    const UPDATE_COMPLETED_PAYMENT  = 2;
    const UPDATE_COMPLETED_SHIPPING = 3;
    const UPDATE_CHECKOUT_MESSAGE   = 4;
    const UPDATE_PAYMENT_DATA       = 5;
    const UPDATE_EMAIL              = 6;

    // ->__('Payment status was updated to Paid on eBay.');
    // ->__('Shipping status was updated to Shipped on eBay.');
    // ->__('Buyer has changed the shipping address of this order at the time of completing payment on eBay.');
    // ->__('Duplicated eBay orders with ID #%id%.');
    // ->__('Order Creation Rules were not met. Press Create Order button at Order view page to create it anyway.');
    // ->__('Magento Order #%order_id% should be canceled as new combined eBay order #%new_id% was created.');
    // ->__('eBay Order #%old_id% was deleted as new combined order #%new_id% was created.');

    // ########################################

    /** @var $helper Ess_M2ePro_Model_Ebay_Order_Helper */
    private $helper = NULL;

    /** @var $order Ess_M2ePro_Model_Account */
    private $account = NULL;

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    private $items = array();

    private $externalTransactions = array();

    private $status = self::STATUS_NOT_MODIFIED;

    private $updates = array();

    // ########################################

    public function __construct()
    {
        $this->helper = Mage::getSingleton('M2ePro/Ebay_Order_Helper');
    }

    // ########################################

    public function initialize(Ess_M2ePro_Model_Account $account, array $data = array())
    {
        $this->account = $account;

        $this->initializeData($data);
        $this->initializeMarketplace();
        $this->initializeOrder();
    }

    // ########################################

    protected function initializeData(array $data = array())
    {
        // Init general data
        // ------------------
        $this->setData('account_id', $this->account->getId());

        $this->setData('ebay_order_id', $data['ebay_order_id']);
        $this->setData('selling_manager_record_number', $data['selling_manager_record_number']);

        $this->setData('checkout_status', $this->helper->getCheckoutStatus($data['checkout_status']));
        $this->setData('checkout_buyer_message', trim($data['checkout_buyer_message']));

        $this->setData('purchase_update_date', $data['purchase_update_date']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);
        // ------------------

        // Init sale data
        // ------------------
        //$this->setData('price', (float)$data['price']); // do we need this?
        $this->setData('is_refund', (bool)$data['is_refund']);
        $this->setData('paid_amount', (float)$data['paid_amount']);
        $this->setData('saved_amount', (float)$data['saved_amount']);
        $this->setData('currency', $data['currency']);
        $this->setData('best_offer', (int)$data['best_offer']);
        $this->setData('final_fee', (float)$data['final_fee']);
        // ------------------

        // Init tax data
        // ------------------
        $this->setData('tax_rate', (float)$data['tax_rate']);
        $this->setData('tax_state', $data['tax_state']);
        $this->setData('tax_amount', (float)$data['tax_amount']);
        $this->setData('tax_includes_shipping', (int)$data['tax_includes_shipping']);
        // ------------------

        // Init customer data
        // ------------------
        $this->setData('buyer_user_id', trim($data['buyer_user_id']));
        $this->setData('buyer_name', trim($data['buyer_name']));
        $this->setData('buyer_email', trim($data['buyer_email']));
        // ------------------

        // Init payment data
        // ------------------
        $this->setData('payment_method', $data['payment_method']);
        $this->setData('payment_status_ebay', $data['payment_status_ebay']);
        $this->setData('payment_status_hold', $data['payment_status_hold']);
        $this->setData('payment_date', $data['payment_date']);

        $paymentStatus = $this->helper->getPaymentStatus(
            $data['payment_method'], $data['payment_date'], $data['payment_status_ebay']
        );
        $this->setData('payment_status', $paymentStatus);
        // ------------------

        // Init shipping data
        // ------------------
        $this->setData('get_it_fast', (int)$data['get_it_fast']);
        $this->setData('shipping_method', $data['shipping_method']);
        $this->setData('shipping_method_selected', (int)$data['shipping_method_selected']);
        $this->setData('shipping_address', $data['shipping_address']);
        $this->setData('shipping_tracking_details', $data['shipping_tracking_details']);
        $this->setData('shipping_price', (float)$data['shipping_price']);
        $this->setData('shipping_type', $data['shipping_type']);
        $this->setData('shipping_date', $data['shipping_date']);

        $shippingStatus = $this->helper->getShippingStatus($data['shipping_date'], $data['shipping_method_selected']);
        $this->setData('shipping_status', $shippingStatus);

        $this->setData('global_shipping_details', $data['global_shipping_details']);
        // ------------------

        $this->items = $data['items'];
        $this->externalTransactions = $data['external_transactions'];
    }

    // ########################################

    private function initializeMarketplace()
    {
        // Get first order item
        // ------------------
        $item = reset($this->items);
        // ------------------

        if (empty($item['item_site'])) {
            return;
        }

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace',$item['item_site'], 'code');

        $shippingMethodName = $this->helper->getShippingMethodNameByCode(
            $this->getData('shipping_method'), $marketplace->getId()
        );
        $paymentMethodName = $this->helper->getPaymentMethodNameByCode(
            $this->getData('payment_method'), $marketplace->getId()
        );

        $this->setData('marketplace_id', $marketplace->getId());
        $this->setData('shipping_method', $shippingMethodName);
        $this->setData('payment_method', $paymentMethodName);
    }

    // ########################################

    private function initializeOrder()
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->account->getId())
            ->addFieldToFilter('ebay_order_id', $this->getData('ebay_order_id'))
            ->getItems();
        $existOrdersNumber = count($existOrders);

        if ($existOrdersNumber > 1) {
            $message = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'Duplicated eBay orders with ID #%id%.', array('!id' => $this->getData('ebay_order_id'))
            );
            throw new Exception($message);
        }

        // New order
        // --------------------
        if ($existOrdersNumber == 0) {
            $this->status = self::STATUS_NEW;
            $this->order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order');
            $this->order->setStatusUpdateRequired(true);

            return;
        }
        // --------------------

        // Already exist order
        // --------------------
        $this->order = reset($existOrders);
        $this->status = self::STATUS_UPDATED;

        if (is_null($this->order->getMagentoOrderId())) {
            $this->order->setStatusUpdateRequired(true);
        }
        // --------------------
    }

    // ########################################

    public function process()
    {
        if (!$this->canCreateOrUpdateOrder()) {
            return NULL;
        }

        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();
        $this->createOrUpdateExternalTransactions();

        if ($this->isNew()) {
            $this->processNew();
        }

        if ($this->isUpdated()) {
            $this->processOrderUpdates();
            $this->processMagentoOrderUpdates();
        }

        return $this->order;
    }

    // ########################################

    private function createOrUpdateItems()
    {
        $itemsCollection = $this->order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->items as $itemData) {
            $itemData['order_id'] = $this->order->getId();

            /** @var $itemBuilder Ess_M2ePro_Model_Ebay_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Ebay_Order_Item_Builder');
            $itemBuilder->initialize($itemData);

            $item = $itemBuilder->process($this->order, $this->isNew(), $this->isCombined());
            $item->setOrder($this->order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    // ########################################

    private function createOrUpdateExternalTransactions()
    {
        $externalTransactionsCollection = $this->order->getChildObject()->getExternalTransactionsCollection();
        $externalTransactionsCollection->load();

        foreach ($this->externalTransactions as $transactionData) {
            $transactionData['order_id'] = $this->order->getId();

            /** @var $transactionBuilder Ess_M2ePro_Model_Ebay_Order_ExternalTransaction_Builder */
            $transactionBuilder = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction_Builder');
            $transactionBuilder->initialize($transactionData);

            $transaction = $transactionBuilder->process();
            $transaction->setOrder($this->order);

            $externalTransactionsCollection->removeItemByKey($transaction->getId());
            $externalTransactionsCollection->addItem($transaction);
        }
    }

    // ########################################

    /**
     * @return bool
     */
    public function isRefund()
    {
        return $this->getData('is_refund');
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isSingle()
    {
        return count($this->items) == 1;
    }

    /**
     * @return bool
     */
    public function isCombined()
    {
        return count($this->items) > 1;
    }

    // ----------------------------------------

    private function hasExternalTransactions()
    {
        return count($this->externalTransactions) > 0;
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    public function isUpdated()
    {
        return $this->status == self::STATUS_UPDATED;
    }

    // ########################################

    /**
     * @return bool
     */
    private function canCreateOrUpdateOrder()
    {
        if ($this->isNew() && $this->isRefund()) {
            return false;
        }

        return true;
    }

    /**
     * @return Ess_M2ePro_Model_Order
     */
    private function createOrUpdateOrder()
    {
        $this->prepareShippingAddress();

        $this->setData('shipping_address', json_encode($this->getData('shipping_address')));
        $this->setData('shipping_tracking_details', json_encode($this->getData('shipping_tracking_details')));
        $this->setData('global_shipping_details', json_encode($this->getData('global_shipping_details')));

        $this->order->addData($this->getData());
        $this->order->save();

        $this->order->setAccount($this->account);
    }

    private function prepareShippingAddress()
    {
        $shippingAddress = $this->getData('shipping_address');
        $shippingAddress['company'] = '';

        if (!isset($shippingAddress['street']) || !is_array($shippingAddress['street'])) {
            $shippingAddress['street'] = array();
        }

        $shippingAddress['street'] = array_filter($shippingAddress['street']);

        $group = '/ebay/order/settings/marketplace_'.(int)$this->getData('marketplace_id').'/';
        $useFirstStreetLineAsCompany = Mage::helper('M2ePro/Module')
            ->getConfig()
                ->getGroupValue($group, 'use_first_street_line_as_company');

        if ($useFirstStreetLineAsCompany && count($shippingAddress['street']) > 1) {
            $shippingAddress['company'] = array_shift($shippingAddress['street']);
        }

        $this->setData('shipping_address', $shippingAddress);
    }

    // ########################################

    private function processNew()
    {
        if (!$this->isNew()) {
            return;
        }

        if ($this->isCombined()) {
            $this->processOrdersContainingItemsFromCurrentOrder();
        }

        /** @var $ebayAccount Ess_M2ePro_Model_Ebay_Account */
        $ebayAccount = $this->account->getChildObject();

        if ($this->order->hasListingItems() && !$ebayAccount->isMagentoOrdersListingsModeEnabled()) {
            return;
        }

        if ($this->order->hasOtherListingItems() && !$ebayAccount->isMagentoOrdersListingsOtherModeEnabled()) {
            return;
        }

        if (!$this->order->getChildObject()->canCreateMagentoOrder()) {
            $this->order->addWarningLog('Magento order was not created. Reason: %msg%', array(
                'msg' => 'Order Creation Rules were not met. ' .
                         'Press Create Order button at Order view page to create it anyway.'
            ));
            return;
        }
    }

    private function processOrdersContainingItemsFromCurrentOrder()
    {
        /** @var $relatedOrders Ess_M2ePro_Model_Order[] */
        $relatedOrders = Mage::getResourceModel('M2ePro/Ebay_Order')
            ->getOrdersContainingItemsFromOrder($this->order);

        foreach ($relatedOrders as $order) {
            if ($order->canCancelMagentoOrder()) {
                $message = 'Magento Order #%order_id% should be canceled '.
                           'as new combined eBay order #%new_id% was created.';
                $message = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription($message, array(
                    '!order_id' => $order->getMagentoOrder()->getRealOrderId(),
                    '!new_id' => $this->order->getData('ebay_order_id')
                ));

                Mage::getSingleton('M2ePro/Order_Log_Manager')->createLogRecord(
                    Ess_M2ePro_Helper_Component_Ebay::NICK, null, $message, Ess_M2ePro_Model_Order_Log::TYPE_WARNING
                );

                try {
                    $order->cancelMagentoOrder();
                } catch (Exception $e) {}
            }

            $orderId = $order->getData('ebay_order_id');
            $order->deleteInstance();

            $message = 'eBay Order #%old_id% was deleted as new combined eBay order #%new_id% was created.';
            $message = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription($message, array(
                '!old_id' => $orderId,
                '!new_id' => $this->order->getData('ebay_order_id')
            ));

            Mage::getSingleton('M2ePro/Order_Log_Manager')->createLogRecord(
                Ess_M2ePro_Helper_Component_Ebay::NICK, null, $message, Ess_M2ePro_Model_Order_Log::TYPE_WARNING
            );
        }
    }

    // ########################################

    private function checkUpdates()
    {
        if (!$this->isUpdated()) {
            return;
        }

        if ($this->hasUpdatedCompletedCheckout()) {
            $this->updates[] = self::UPDATE_COMPLETED_CHECKOUT;
        }
        if ($this->hasUpdatedCheckoutMessage()) {
            $this->updates[] = self::UPDATE_CHECKOUT_MESSAGE;
        }
        if ($this->hasUpdatedCompletedPayment()) {
            $this->updates[] = self::UPDATE_COMPLETED_PAYMENT;
        }
        if ($this->hasUpdatedPaymentData()) {
            $this->updates[] = self::UPDATE_PAYMENT_DATA;
        }
        if ($this->hasUpdatedCompletedShipping()) {
            $this->updates[] = self::UPDATE_COMPLETED_SHIPPING;
        }
        if ($this->hasUpdatedEmail()) {
            $this->updates[] = self::UPDATE_EMAIL;
        }
    }

    // ----------------------------------------

    private function hasUpdatedCompletedCheckout()
    {
        if (!$this->isUpdated() || $this->order->getChildObject()->isCheckoutCompleted()) {
            return false;
        }

        return $this->getData('checkout_status') == Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED;
    }

    private function hasUpdatedCheckoutMessage()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        if ($this->getData('checkout_buyer_message') == '') {
            return false;
        }

        return $this->getData('checkout_buyer_message') != $this->order->getChildObject()->getCheckoutBuyerMessage();
    }

    // ----------------------------------------

    private function hasUpdatedCompletedPayment()
    {
        if (!$this->isUpdated() || $this->order->getChildObject()->isPaymentCompleted()) {
            return false;
        }

        return $this->getData('payment_status') == Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED;
    }

    // ----------------------------------------

    private function hasUpdatedCompletedShipping()
    {
        if (!$this->isUpdated() || $this->order->getChildObject()->isShippingCompleted()) {
            return false;
        }

        return $this->getData('shipping_status') == Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED;
    }

    // ----------------------------------------

    private function hasUpdatedPaymentData()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order */
        $ebayOrder = $this->order->getChildObject();

        if ($ebayOrder->getData('payment_method') != $this->getData('payment_method')) {
            return true;
        }

        if (!$ebayOrder->hasExternalTransactions() && $this->hasExternalTransactions()) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    private function hasUpdatedEmail()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        $newEmail = $this->getData('buyer_email');
        $oldEmail = $this->order->getData('buyer_email');

        if ($newEmail == $oldEmail) {
            return false;
        }

        return filter_var($newEmail, FILTER_VALIDATE_EMAIL) !== false;
    }

    // ########################################

    private function hasUpdates()
    {
        return !empty($this->updates);
    }

    private function hasUpdate($update)
    {
        if (!$update) {
            return false;
        }

        return in_array($update, $this->updates);
    }

    private function processOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_CHECKOUT)) {
            $this->order->addSuccessLog('Buyer has completed checkout on eBay.');
            $this->order->setStatusUpdateRequired(true);
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_PAYMENT)) {
            $this->order->addSuccessLog('Payment status was updated to Paid on eBay.');
            $this->order->setStatusUpdateRequired(true);
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_SHIPPING)) {
            $this->order->addSuccessLog('Shipping status was updated to Shipped on eBay.');
            $this->order->setStatusUpdateRequired(true);
        }
    }

    private function processMagentoOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $magentoOrder = $this->order->getMagentoOrder();
        if (is_null($magentoOrder)) {
            return;
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);

        $proxy = $this->order->getProxy();
        $proxy->setStore($this->order->getStore());

        if ($this->hasUpdate(self::UPDATE_PAYMENT_DATA)) {
            $magentoOrderUpdater->updatePaymentData($proxy->getPaymentData());
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_CHECKOUT)) {
            $magentoOrderUpdater->updateShippingAddress($proxy->getAddressData());
            $magentoOrderUpdater->updateCustomerAddress($proxy->getAddressData());
        }

        if ($this->hasUpdate(self::UPDATE_CHECKOUT_MESSAGE)) {
            $magentoOrderUpdater->updateComments($proxy->getChannelComments());
        }

        if ($this->hasUpdate(self::UPDATE_EMAIL)) {
            $magentoOrderUpdater->updateCustomerEmail($this->order->getChildObject()->getBuyerEmail());
        }

        $magentoOrderUpdater->finishUpdate();
    }

    // ########################################
}