<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_OrderController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/sales')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('eBay Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/OrderHandler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js')
             ->addJs('M2ePro/Order/Edit/ShippingAddressHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/sales/order');
    }

    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Model_Order_Log::INITIATOR_USER);
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->_redirect('*/adminhtml_order/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Order */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order');
        $block->enableEbayTab();

        $this->getResponse()->setBody($block->getEbayTabHtml());
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $this->_initAction();
        $this->_initPopUp();

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_view'))
             ->renderLayout();
    }

    //#############################################

    public function orderItemGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_view_item')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function editShippingAddressAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_edit_shippingAddress'))
             ->renderLayout();
    }

    public function saveShippingAddressAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_order/index');
        }

        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        $data = array();
        $keys = array(
            'buyer_name',
            'buyer_email'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('buyer_name', $data['buyer_name']);
        $order->setData('buyer_email', $data['buyer_email']);

        $data = array();
        $keys = array(
            'street',
            'city',
            'country_code',
            'state',
            'postal_code',
            'phone'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('shipping_address', json_encode($data));
        $order->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order address has been updated.'));

        $this->_redirect('*/adminhtml_ebay_order/view', array('id' => $order->getId()));
    }

    //#############################################

    private function processConnector($action, array $params = array())
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select order(s).'));
            return $this->_redirect('*/adminhtml_order/index');
        }

        $ordersIds = array();
        !is_null($id) && $ordersIds[] = $id;
        !is_null($ids) && $ordersIds = array_merge($ordersIds,(array)$ids);

        return Mage::getModel('M2ePro/Connector_Server_Ebay_Order_Dispatcher')->process($action, $ordersIds, $params);
    }

    //--------------------

    public function updatePaymentStatusAction()
    {
        if ($this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_PAY)) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Payment status for selected eBay Order(s) was updated to Paid.')
            );
        } else {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Payment status for selected eBay Order(s) was not updated.')
            );
        }

        return $this->_redirectUrl($this->_getRefererUrl());
    }

    public function updateShippingStatusAction()
    {
        if ($this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP)) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Shipping status for selected eBay Order(s) was updated to Shipped.')
            );
        } else {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Shipping status for selected eBay Order(s) was not updated.')
            );
        }

        return $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################

    public function createMagentoOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $force = $this->getRequest()->getParam('force');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        if (!is_null($order->getMagentoOrderId()) && $force != 'yes') {
            $message = 'Magento Order is already created for this %s Order. ' .
                       'Press Create Order button to create new one.';

            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__($message, Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::NICK))
            );
            $this->_redirect('*/*/view', array('id' => $id));
            return;
        }

        // Create magento order
        // -------------
        try {
            $order->createMagentoOrder();
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Magento Order was created.'));
        } catch (Exception $e) {
            $message = Mage::helper('M2ePro')->__(
                'Magento Order was not created. Reason: %s',
                Mage::getSingleton('M2ePro/Log_Abstract')->decodeDescription($e->getMessage())
            );
            $this->_getSession()->addError($message);
        }
        // -------------

        if ($order->getChildObject()->canCreatePaymentTransaction()) {
            $order->getChildObject()->createPaymentTransactions();
        }

        if ($order->getChildObject()->canCreateInvoice()) {
            $result = $order->createInvoice();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Invoice was created.'));
        }

        if ($order->getChildObject()->canCreateShipment()) {
            $result = $order->createShipment();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Shipment was created.'));
        }

        if ($order->getChildObject()->canCreateTracks()) {
            $order->getChildObject()->createTracks();
        }

        // -------------
        $order->updateMagentoOrderStatus();
        // -------------

        return $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################

    public function goToPaypalAction()
    {
        $transactionId = $this->getRequest()->getParam('transaction_id');

        if (!$transactionId) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Transaction ID should be defined.'));
            return $this->_redirect('*/adminhtml_order/index');
        }

        /** @var $transaction Ess_M2ePro_Model_Ebay_Order_ExternalTransaction */
        $transaction = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction')->load($transactionId, 'transaction_id');

        if (is_null($transaction->getId())) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('eBay order transaction does not exist.'));
            return $this->_redirect('*/adminhtml_order/index');
        }

        if (!$transaction->isPaypal()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('This is not a PayPal transaction.'));
            return $this->_redirect('*/adminhtml_order/index');
        }

        return $this->_redirectUrl($transaction->getPaypalUrl());
    }

    //#############################################
}