<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->setData('order_id', $data['order_id']);
        $this->setData('listing_type', $data['listing_type']);
        $this->setData('transaction_id', $data['transaction_id']);
        $this->setData('item_id', $data['item_id']);
        $this->setData('title', $data['item_title']);
        $this->setData('sku', $data['item_sku']);
        $this->setData('condition_display_name', $data['item_condition_display_name']);
        // ------------------

        // Init sale data
        // ------------------
        $this->setData('price', (float)$data['price']);
        $this->setData('buy_it_now_price', (float)$data['buy_it_now_price']);
        $this->setData('currency', $data['currency']);
        $this->setData('qty_purchased', (int)$data['qty_purchased']);
        $this->setData('auto_pay', (int)$data['auto_pay']);
        $this->setData('variation', json_encode($data['variation']));
        // ------------------
    }

    // ########################################

    public function process()
    {
        return $this->createOrderItem();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_Item
     */
    private function createOrderItem()
    {
        $item = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('item_id', $this->getData('item_id'))
            ->addFieldToFilter('transaction_id', $this->getData('transaction_id'))
            ->getFirstItem();

        $item->addData($this->getData());
        $item->save();

        return $item;
    }

    // ########################################
}