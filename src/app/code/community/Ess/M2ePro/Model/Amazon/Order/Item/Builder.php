<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->setData('amazon_order_item_id', $data['amazon_order_item_id']);
        $this->setData('order_id', $data['order_id']);
        $this->setData('sku', trim($data['sku']));
        $this->setData('general_id', trim($data['general_id']));
        $this->setData('is_isbn_general_id', (int)$data['is_isbn_general_id']);
        $this->setData('title', trim($data['title']));
        $this->setData('gift_type', trim($data['gift_type']));
        $this->setData('gift_message', trim($data['gift_message']));
        // ------------------

        // Init sale data
        // ------------------
        $this->setData('price', (float)$data['price']);
        $this->setData('gift_price', (float)$data['gift_price']);
        $this->setData('currency', trim($data['currency']));
        $this->setData('discount_details', json_encode($data['discount_details']));
        $this->setData('qty_purchased', (int)$data['qty_purchased']);
        $this->setData('qty_shipped', (int)$data['qty_shipped']);
        $this->setData('tax_details', json_encode($data['tax_details']));
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
        $existItem = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order_Item')
            ->addFieldToFilter('amazon_order_item_id', $this->getData('amazon_order_item_id'))
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('sku', $this->getData('sku'))
            ->getFirstItem();

        $existItem->addData($this->getData());
        $existItem->save();

        return $existItem;
    }

    // ########################################
}