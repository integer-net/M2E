<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Order_Item_Builder extends Varien_Object
{
    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->setData('order_id', $data['order_id']);
        $this->setData('play_order_item_id', $data['order_item_id']);
        $this->setData('listing_id', $data['listing_id']);
        $this->setData('sku', trim($data['sku']));
        $this->setData('title', trim($data['title']));
        // ------------------

        // Init sale data
        // ------------------
        $this->setData('currency', $data['currency']);
        $this->setData('price', (float)$data['price']);
        $this->setData('fee', (float)$data['fee']);
        $this->setData('exchange_rate', (float)$data['exchange_rate']);
        $this->setData('proceed', (float)$data['proceed']);
        $this->setData('qty', (int)$data['qty']);
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
        $existItem = Mage::helper('M2ePro/Component_Play')
            ->getCollection('Order_Item')
            ->addFieldToFilter('play_order_item_id', $this->getData('play_order_item_id'))
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->getFirstItem();

        $existItem->addData($this->getData());
        $existItem->save();

        return $existItem;
    }

    // ########################################
}