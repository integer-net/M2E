<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Order_Builder extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    // Parser hack -> Mage::helper('M2ePro')->__('Duplicated Buy orders with ID #%id%.');

    // ########################################

    /** @var $order Ess_M2ePro_Model_Account */
    private $account = NULL;

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    private $status = self::STATUS_NOT_MODIFIED;

    private $items = array();

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
        $this->setData('marketplace_id', Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID);

        $this->setData('seller_id', $data['seller_id']);
        $this->setData('buy_order_id', $data['order_id']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);
        // ------------------

        // Init sale data
        // ------------------
        $this->setData('paid_amount', (float)$data['paid_amount']);
        $this->setData('currency', 'USD');
        // ------------------

        // Init customer/shipping data
        // ------------------
        $this->setData('buyer_name', $data['buyer_name']);
        $this->setData('buyer_email', $data['buyer_email']);
        $this->setData('billing_address', $data['billing_address']);
        $this->setData('shipping_method', $data['shipping_method']);
        $this->setData('shipping_address', $data['shipping_address']);
        $this->setData('shipping_price', (float)$data['shipping_price']);
        // ------------------

        $this->items = $data['items'];
    }

    // ########################################

    private function initializeOrder()
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->account->getId())
            ->addFieldToFilter('buy_order_id', $this->getData('buy_order_id'))
            ->getItems();
        $existOrdersNumber = count($existOrders);

        if ($existOrdersNumber > 1) {
            $message = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'Duplicated Rakuten.com orders with ID #%id%.', array('!id' => $this->getData('buy_order_id'))
            );
            throw new Exception($message);
        }

        // New order
        // --------------------
        if ($existOrdersNumber == 0) {
            $this->status = self::STATUS_NEW;
            $this->order = Mage::helper('M2ePro/Component_Buy')->getModel('Order');
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
        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        return $this->order;
    }

    // ########################################

    private function createOrUpdateItems()
    {
        $itemsCollection = $this->order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->items as $itemData) {
            $itemData['order_id'] = $this->order->getId();

            /** @var $itemBuilder Ess_M2ePro_Model_Buy_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Buy_Order_Item_Builder');
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
        $this->setData('billing_address', json_encode($this->getData('billing_address')));
        $this->setData('shipping_address', json_encode($this->getData('shipping_address')));
        $this->order->addData($this->getData());

        $this->order->save();
        $this->order->setAccount($this->account);
    }

    // ########################################
}