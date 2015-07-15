<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Dispatcher
{
    //####################################

    public function systemConfigurationSaveAction(Varien_Event_Observer $eventObserver)
    {
        $this->process('Magento_Configuration', $eventObserver);
    }

    //####################################

    public function catalogProductSaveBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_AddUpdate_Before', $eventObserver);
    }

    public function catalogProductSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_AddUpdate_After', $eventObserver);
    }

    //------------------------------------

    public function catalogProductDeleteBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_Delete', $eventObserver);
    }

    //####################################

    public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_Attribute_Update_Before', $eventObserver);
    }

    //####################################

    public function catalogCategoryChangeProducts(Varien_Event_Observer $eventObserver)
    {
        $this->process('Category', $eventObserver);
    }

    public function catalogInventoryStockItemSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('StockItem', $eventObserver);
    }

    //####################################

    public function synchronizationBeforeStart(Varien_Event_Observer $eventObserver)
    {
        $this->process('Indexes_Disable', $eventObserver);
    }

    public function synchronizationAfterStart(Varien_Event_Observer $eventObserver)
    {
        $this->process('Indexes_Enable', $eventObserver);
    }

    //####################################

    public function salesOrderInvoicePay(Varien_Event_Observer $eventObserver)
    {
        $this->process('Invoice', $eventObserver);
    }

    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Shipment', $eventObserver);
    }

    public function salesOrderCreditmemoRefund(Varien_Event_Observer $eventObserver)
    {
        $this->process('CreditMemo', $eventObserver);
    }

    public function salesOrderSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order', $eventObserver);
    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Quote', $eventObserver);
    }

    //####################################

    public function associateEbayItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Ebay_Order_Item', $eventObserver);
    }

    public function associateAmazonItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Amazon_Order_Item', $eventObserver);
    }

    public function associateBuyItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Buy_Order_Item', $eventObserver);
    }

    //####################################

    public function revertAmazonOrderedQty(Varien_Event_Observer $eventObserver)
    {
        $this->process('Amazon_Order', $eventObserver);
    }

    //####################################

    private function process($observerModel, Varien_Event_Observer $eventObserver)
    {
        try {

            /** @var Ess_M2ePro_Model_Observer_Abstract $observer */
            $observer = Mage::getModel('M2ePro/Observer_'.$observerModel);
            $observer->setEventObserver($eventObserver);

            if (!$observer->canProcess()) {
                return;
            }

            $observer->beforeProcess();
            $observer->process();
            $observer->afterProcess();

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //####################################
}