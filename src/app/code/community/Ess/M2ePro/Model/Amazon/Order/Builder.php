<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_Builder extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const UPDATE_STATUS = 0;
    const UPDATE_EMAIL  = 1;

    // M2ePro_TRANSLATIONS
    // Duplicated Amazon orders with ID #%id%.

    // ########################################

    /** @var $helper Ess_M2ePro_Model_Amazon_Order_Helper */
    private $helper = NULL;

    /** @var $order Ess_M2ePro_Model_Account */
    private $account = NULL;

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    private $status = self::STATUS_NOT_MODIFIED;

    private $items = array();

    private $updates = array();

    // ########################################

    public function __construct()
    {
        $this->helper = Mage::getSingleton('M2ePro/Amazon_Order_Helper');
    }

    // ########################################

    public function initialize(Ess_M2ePro_Model_Account $account, array $data = array())
    {
        $this->account = $account;

        $this->initializeData($data);
        $this->initializeOrder();
    }

    // ########################################

    private function initializeData(array $data = array())
    {
        // Init general data
        // ------------------
        $this->setData('account_id', $this->account->getId());
        $this->setData('amazon_order_id', $data['amazon_order_id']);
        $this->setData('marketplace_id', $data['marketplace_id']);

        $this->setData('status', $this->helper->getStatus($data['status']));
        $this->setData('is_afn_channel', $data['is_afn_channel']);

        $this->setData('purchase_update_date', $data['purchase_update_date']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);
        // ------------------

        // Init sale data
        // ------------------
        $this->setData('paid_amount', (float)$data['paid_amount']);
        $this->setData('tax_details', json_encode($data['tax_details']));
        $this->setData('discount_details', json_encode($data['discount_details']));
        $this->setData('currency', $data['currency']);
        $this->setData('qty_shipped', $data['qty_shipped']);
        $this->setData('qty_unshipped', $data['qty_unshipped']);
        // ------------------

        // Init customer/shipping data
        // ------------------
        $this->setData('buyer_name', $data['buyer_name']);
        $this->setData('buyer_email', $data['buyer_email']);
        $this->setData('shipping_service', $data['shipping_service']);
        $this->setData('shipping_address', $data['shipping_address']);
        $this->setData('shipping_price', (float)$data['shipping_price']);
        // ------------------

        $this->items = $data['items'];
    }

    // ########################################

    private function initializeOrder()
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->account->getId())
            ->addFieldToFilter('amazon_order_id', $this->getData('amazon_order_id'))
            ->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->getItems();
        $existOrdersNumber = count($existOrders);

        // duplicated M2ePro orders. remove m2e order without magento order id or newest order
        // --------------------
        if ($existOrdersNumber > 1) {
            $isDeleted = false;

            foreach ($existOrders as $key => $order) {
                /** @var Ess_M2ePro_Model_Order $order */

                $magentoOrderId = $order->getData('magento_order_id');
                if (!empty($magentoOrderId)) {
                    continue;
                }

                $order->deleteInstance();
                unset($existOrders[$key]);
                $isDeleted = true;
                break;
            }

            if (!$isDeleted) {
                $orderForRemove = reset($existOrders);
                $orderForRemove->deleteInstance();
            }
        }
        // --------------------

        // New order
        // --------------------
        if ($existOrdersNumber == 0) {
            $this->status = self::STATUS_NEW;
            $this->order = Mage::helper('M2ePro/Component_Amazon')->getModel('Order');
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
        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        if ($this->isUpdated()) {
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

            /** @var $itemBuilder Ess_M2ePro_Model_Amazon_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Amazon_Order_Item_Builder');
            $itemBuilder->initialize($itemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    // ########################################

    /**
     * @return bool
     */
    private function isSingle()
    {
        return count($this->items) == 1;
    }

    /**
     * @return bool
     */
    private function isCombined()
    {
        return count($this->items) > 1;
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    private function isNew()
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    private function isUpdated()
    {
        return $this->status == self::STATUS_UPDATED;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order
     */
    private function createOrUpdateOrder()
    {
        if (!$this->isNew() && $this->getData('status') == Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED) {
            $this->order->setData('status', Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED);
            $this->order->setData('purchase_update_date', $this->getData('purchase_update_date'));
        } else {
            $this->setData('shipping_address', json_encode($this->getData('shipping_address')));
            $this->order->addData($this->getData());
        }

        $this->order->save();
        $this->order->setAccount($this->account);
    }

    // ########################################

    private function checkUpdates()
    {
        if ($this->hasUpdatedStatus()) {
            $this->updates[] = self::UPDATE_STATUS;
        }
        if ($this->hasUpdatedEmail()) {
            $this->updates[] = self::UPDATE_EMAIL;
        }
    }

    private function hasUpdatedStatus()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        return $this->getData('status') != $this->order->getData('status');
    }

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

    private function processMagentoOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        if ($this->hasUpdate(self::UPDATE_STATUS) && $this->order->getChildObject()->isCanceled()) {
            $this->cancelMagentoOrder();
            return;
        }

        if ($this->hasUpdate(self::UPDATE_STATUS)) {
            $this->order->setStatusUpdateRequired(true);
        }

        if (!is_null($this->order->getMagentoOrder()) && $this->hasUpdate(self::UPDATE_EMAIL)) {
            /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
            $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
            $magentoOrderUpdater->setMagentoOrder($this->order->getMagentoOrder());
            $magentoOrderUpdater->updateCustomerEmail($this->order->getChildObject()->getBuyerEmail());
            $magentoOrderUpdater->finishUpdate();
        }
    }

    private function cancelMagentoOrder()
    {
        if (!$this->order->canCancelMagentoOrder()) {
            return;
        }

        $magentoOrderComments = array();
        $magentoOrderComments[] = '<b>Attention!</b> Order was canceled on Amazon.';

        try {
            $this->order->cancelMagentoOrder();
        } catch (Exception $e) {
            $magentoOrderComments[] = 'Order cannot be canceled in magento. Reason: ' . $e->getMessage();
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->order->getMagentoOrder());
        $magentoOrderUpdater->updateComments($magentoOrderComments);
        $magentoOrderUpdater->finishUpdate();
    }

    // ########################################
}