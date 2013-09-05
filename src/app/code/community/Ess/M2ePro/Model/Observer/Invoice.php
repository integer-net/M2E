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
                $order = Mage::helper('M2ePro/Component')
                    ->getUnknownObject('Order', $magentoOrderId, 'magento_order_id');
            } catch (Exception $e) {
                return;
            }

            if (!$order->isComponentModeEbay()) {
                return;
            }

            Mage::getSingleton('M2ePro/Order_Log_Manager')
                ->setInitiator(Ess_M2ePro_Model_Order_Log::INITIATOR_EXTENSION);

            $result = $order->getChildObject()->updatePaymentStatus();

            $result ? $this->addSessionSuccessMessage()
                    : $this->addSessionErrorMessage($order);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################

    private function addSessionSuccessMessage()
    {
        $message = Mage::helper('M2ePro')->__('Payment Status for eBay Order was updated to Paid.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    private function addSessionErrorMessage(Ess_M2ePro_Model_Order $order)
    {
        // todo adminhtml_log
        $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_log/order', array('order_id' => $order->getId()));

        $startLink = '<a href="' . $url . '" target="_blank">';
        $endLink = '</a>';
        $channel = $order->getComponentTitle();

        $message  = Mage::helper('M2ePro')->__(
            'Payment Status for %s Order was not updated. View %sorder log%s for more details.',
            $channel, $startLink, $endLink
        );

        Mage::getSingleton('adminhtml/session')->addError($message);
    }

    //####################################
}