<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Order
{
    /** @var $quote Mage_Sales_Model_Quote */
    private $quote = NULL;

    /** @var $order Mage_Sales_Model_Order */
    private $order = NULL;

    // ########################################

    public function __construct(Mage_Sales_Model_Quote $quote)
    {
        $this->quote = $quote;
    }

    // ########################################

    public function getOrder()
    {
        return $this->order;
    }

    // ########################################

    public function buildOrder()
    {
        $this->createOrder();
    }

    private function createOrder()
    {
        try {
            $this->order = $this->placeOrder();
        } catch (Exception $e) {
            // Remove ordered items from customer cart
            // ----------
            $this->quote->setIsActive(false)->save();
            // ----------
            throw $e;
        }

        // Remove ordered items from customer cart
        // ----------
        $this->quote->setIsActive(false)->save();
        // ----------
    }

    private function placeOrder()
    {
        if (version_compare(Mage::helper('M2ePro/Magento')->getVersion(false), '1.4.1', '>=')) {
            /** @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('sales/service_quote', $this->quote);
            $service->submitAll();

            return $service->getOrder();
        }

        // Magento version 1.4.0 backward compatibility code

        /** @var $quoteConverter Mage_Sales_Model_Convert_Quote */
        $quoteConverter = Mage::getSingleton('sales/convert_quote');

        /** @var $orderObj Mage_Sales_Model_Order */
        $orderObj = $quoteConverter->addressToOrder($this->quote->getShippingAddress());

        $orderObj->setBillingAddress($quoteConverter->addressToOrderAddress($this->quote->getBillingAddress()));
        $orderObj->setShippingAddress($quoteConverter->addressToOrderAddress($this->quote->getShippingAddress()));
        $orderObj->setPayment($quoteConverter->paymentToOrderPayment($this->quote->getPayment()));

        $items = $this->quote->getShippingAddress()->getAllItems();

        foreach ($items as $item) {
            //@var $item Mage_Sales_Model_Quote_Item
            $orderItem = $quoteConverter->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $orderObj->addItem($orderItem);
        }

        $orderObj->setCanShipPartiallyItem(false);
        $orderObj->place();
        $orderObj->save();

        return $orderObj;
    }

    public function addComments(array $comments)
    {
        if (count($comments) == 0) {
            return;
        }

        $comments = implode('<br /><br />', $comments);
        $header = '<br /><b><u>' . Mage::helper('M2ePro')->__('M2E Pro Notes') . ':</u></b><br /><br />';

        $this->order->addStatusHistoryComment($header . $comments);
        $this->order->save();
    }

    // ########################################
}