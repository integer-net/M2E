<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Amazon_Order
{
    public function revertOrderedQty(Varien_Event_Observer $observer)
    {
        /** @var $magentoOrder Mage_Sales_Model_Order */
        $magentoOrder = $observer->getEvent()->getMagentoOrder();

        foreach ($magentoOrder->getAllItems() as $orderItem) {
            /** @var $orderItem Mage_Sales_Model_Order_Item */
            if ($orderItem->getHasChildren()) {
                continue;
            }

            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($orderItem->getProductId());

            if (!$stockItem->getId()) {
                continue;
            }

            $stockItem->addQty($orderItem->getQtyOrdered())->save();
        }
    }
}