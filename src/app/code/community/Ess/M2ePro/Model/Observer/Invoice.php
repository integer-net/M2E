<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Invoice
{
    //####################################

    public function salesOrderInvoicePay(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::helper('M2ePro/Data_Global')->getValue('skip_invoice_observer')) {
                // Not process invoice observer when set such flag
                Mage::helper('M2ePro/Data_Global')->unsetValue('skip_invoice_observer');
                return;
            }

            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = $observer->getEvent()->getInvoice();
            $magentoOrderId = $invoice->getOrderId();

            try {
                /** @var $order Ess_M2ePro_Model_Order */
                $order = Mage::helper('M2ePro/Component_Ebay')
                    ->getObject('Order', $magentoOrderId, 'magento_order_id');
            } catch (Exception $e) {
                return;
            }

            if (!$order->getChildObject()->canUpdatePaymentStatus()) {
                return;
            }

            $this->createChange($order);

            Mage::getSingleton('M2ePro/Order_Log_Manager')
                ->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

            $result = $order->getChildObject()->updatePaymentStatus();

            $result ? $this->addSessionSuccessMessage()
                    : $this->addSessionErrorMessage($order);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################

    private function createChange(Ess_M2ePro_Model_Order $order)
    {
        // save change
        //------------------------------
        $orderId   = $order->getId();
        $action    = Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_PAYMENT;
        $creator   = Ess_M2ePro_Model_Order_Change::CREATOR_TYPE_OBSERVER;
        $component = $order->getComponentMode();

        Mage::getModel('M2ePro/Order_Change')->create($orderId, $action, $creator, $component, array());
        //------------------------------
    }

    //####################################

    private function addSessionSuccessMessage()
    {
        $message = Mage::helper('M2ePro')->__('Payment Status for eBay Order was updated to Paid.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    private function addSessionErrorMessage(Ess_M2ePro_Model_Order $order)
    {
        $url = Mage::helper('adminhtml')
            ->getUrl('M2ePro/adminhtml_ebay_log/order', array('order_id' => $order->getId()));

        $channel = $order->getComponentTitle();
        // M2ePro_TRANSLATIONS
        // Payment Status for %chanel_title% Order was not updated. View <a href="%url%" target="_blank">order log</a> for more details.
        $message  = Mage::helper('M2ePro')->__(
            'Payment Status for %chanel_title% Order was not updated.'.
            ' View <a href="%url%" target="_blank">order log</a> for more details.'.
            $channel, $url
        );

        Mage::getSingleton('adminhtml/session')->addError($message);
    }

    //####################################
}