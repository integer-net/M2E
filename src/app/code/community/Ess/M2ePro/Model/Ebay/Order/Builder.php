<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Builder extends Mage_Core_Model_Abstract
{
    // ##########################################################

    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const UPDATE_COMPLETED_CHECKOUT = 1;
    const UPDATE_COMPLETED_PAYMENT  = 2;
    const UPDATE_COMPLETED_SHIPPING = 3;
    const UPDATE_BUYER_MESSAGE      = 4;
    const UPDATE_PAYMENT_DATA       = 5;
    const UPDATE_EMAIL              = 6;

    // ##########################################################

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
        // ------------------
        $this->setData('account_id', $this->account->getId());

        $this->setData('ebay_order_id', $data['identifiers']['ebay_order_id']);
        $this->setData('selling_manager_id', $data['identifiers']['selling_manager_id']);

        $this->setData('checkout_status', $this->helper->getCheckoutStatus($data['statuses']['checkout']));

        $this->setData('purchase_update_date', $data['purchase_update_date']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);
        // ------------------

        // ------------------
        $this->setData('paid_amount', (float)$data['selling']['paid_amount']);
        $this->setData('saved_amount', (float)$data['selling']['saved_amount']);
        $this->setData('currency', $data['selling']['currency']);

        if (empty($data['selling']['tax_details']) || !is_array($data['selling']['tax_details'])) {
            $this->setData('tax_details', null);
        } else {
            $this->setData('tax_details', $data['selling']['tax_details']);
        }
        // ------------------

        // ------------------
        $this->setData('buyer_user_id', trim($data['buyer']['user_id']));
        $this->setData('buyer_name', trim($data['buyer']['name']));
        $this->setData('buyer_email', trim($data['buyer']['email']));
        $this->setData('buyer_message', $data['buyer']['message']);
        // ------------------

        // ------------------
        $this->externalTransactions = $data['payment']['external_transactions'];
        unset($data['payment']['external_transactions']);

        $this->setData('payment_details', $data['payment']);

        $paymentStatus = $this->helper->getPaymentStatus(
            $data['payment']['method'], $data['payment']['date'], $data['payment']['status']
        );
        $this->setData('payment_status', $paymentStatus);
        // ------------------

        // ------------------
        $this->setData('shipping_details', $data['shipping']);

        $shippingStatus = $this->helper->getShippingStatus(
            $data['shipping']['date'], !empty($data['shipping']['service'])
        );
        $this->setData('shipping_status', $shippingStatus);
        // ------------------

        // ------------------
        $this->items = $data['items'];
        // ------------------
    }

    // ########################################

    private function initializeMarketplace()
    {
        // Get first order item
        // ------------------
        $item = reset($this->items);
        // ------------------

        if (empty($item['site'])) {
            return;
        }

        $shippingDetails = $this->getData('shipping_details');
        $paymentDetails = $this->getData('payment_details');

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace',$item['site'], 'code');

        $shippingDetails['service'] = $this->helper->getShippingServiceNameByCode(
            $shippingDetails['service'], $marketplace->getId()
        );
        $paymentDetails['method'] = $this->helper->getPaymentMethodNameByCode(
            $paymentDetails['method'], $marketplace->getId()
        );

        $this->setData('marketplace_id', $marketplace->getId());
        $this->setData('shipping_details', $shippingDetails);
        $this->setData('payment_details', $paymentDetails);
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

            $item = $itemBuilder->process();
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

    public function isRefund()
    {
        $paymentDetails = $this->getData('payment_details');
        return $paymentDetails['is_refund'];
    }

    // ----------------------------------------

    public function isSingle()
    {
        return count($this->items) == 1;
    }

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

    public function isNew()
    {
        return $this->status == self::STATUS_NEW;
    }

    public function isUpdated()
    {
        return $this->status == self::STATUS_UPDATED;
    }

    // ########################################

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

        $this->setData('tax_details', json_encode($this->getData('tax_details')));
        $this->setData('shipping_details', json_encode($this->getData('shipping_details')));
        $this->setData('payment_details', json_encode($this->getData('payment_details')));

        $this->order->addData($this->getData());
        $this->order->save();

        $this->order->setAccount($this->account);
    }

    private function prepareShippingAddress()
    {
        $shippingDetails = $this->getData('shipping_details');
        $shippingAddress = $shippingDetails['address'];

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

        $shippingDetails['address'] = $shippingAddress;
        $this->setData('shipping_details', $shippingDetails);
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
                    Ess_M2ePro_Helper_Component_Ebay::NICK, null, $message, Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING
                );

                try {
                    $order->cancelMagentoOrder();
                } catch (Exception $e) {}
            }

            if ($order->getReserve()->isPlaced()) {
                $order->getReserve()->release();
            }

            $orderId = $order->getData('ebay_order_id');
            $order->deleteInstance();

            $message = 'eBay Order #%old_id% was deleted as new combined eBay order #%new_id% was created.';
            $message = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription($message, array(
                '!old_id' => $orderId,
                '!new_id' => $this->order->getData('ebay_order_id')
            ));

            Mage::getSingleton('M2ePro/Order_Log_Manager')->createLogRecord(
                Ess_M2ePro_Helper_Component_Ebay::NICK, null, $message, Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING
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
        if ($this->hasUpdatedBuyerMessage()) {
            $this->updates[] = self::UPDATE_BUYER_MESSAGE;
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

    private function hasUpdatedBuyerMessage()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        if ($this->getData('buyer_message') == '') {
            return false;
        }

        return $this->getData('buyer_message') != $this->order->getChildObject()->getBuyerMessage();
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
        $paymentDetails = $this->getData('payment_details');

        if ($ebayOrder->getPaymentMethod() != $paymentDetails['method']) {
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

        if ($this->hasUpdate(self::UPDATE_BUYER_MESSAGE)) {
            $magentoOrderUpdater->updateComments($proxy->getChannelComments());
        }

        if ($this->hasUpdate(self::UPDATE_EMAIL)) {
            $magentoOrderUpdater->updateCustomerEmail($this->order->getChildObject()->getBuyerEmail());
        }

        $magentoOrderUpdater->finishUpdate();
    }

    // ########################################
}