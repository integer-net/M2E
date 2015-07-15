<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_OrderController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Amazon Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Order/Debug.js')
             ->addJs('M2ePro/Order/Handler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js')
             ->addJs('M2ePro/Order/Edit/ShippingAddressHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/orders');
    }

    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->_redirect('*/adminhtml_common_order/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Order */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_order');
        $block->enableAmazonTab();

        $this->getResponse()->setBody($block->getAmazonTabHtml());
    }

    public function gridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_order_grid')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction();
        $this->_initPopUp();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_order_view'))
            ->renderLayout();
    }

    //#############################################

    public function orderItemGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        if (!$id || !$order->getId()) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_order_view_item')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function createMagentoOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $force = $this->getRequest()->getParam('force');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        // M2ePro_TRANSLATIONS
        // Magento Order is already created for this Amazon Order.
        if (!is_null($order->getMagentoOrderId()) && $force != 'yes') {
            $message = 'Magento Order is already created for this Amazon Order. ' .
                       'Press Create Order Button to create new one.';

            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__($message)
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
                'Magento Order was not created. Reason: %error_message%',
                Mage::getSingleton('M2ePro/Log_Abstract')->decodeDescription($e->getMessage())
            );
            $this->_getSession()->addError($message);
        }
        // -------------

        // Create invoice
        // -------------
        if ($order->getChildObject()->canCreateInvoice()) {
            $result = $order->createInvoice();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Invoice was created.'));
        }
        // -------------

        // Create shipment
        // -------------
        if ($order->getChildObject()->canCreateShipment()) {
            $result = $order->createShipment();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Shipment was created.'));
        }
        // -------------

        // -------------
        $order->updateMagentoOrderStatus();
        // -------------

        $this->_redirect('*/*/view', array('id' => $id));
    }

    //#############################################

    public function editShippingAddressAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_order_edit_shippingAddress'))
             ->renderLayout();
    }

    public function saveShippingAddressAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_common_order/index');
        }

        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

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
            'county',
            'country_code',
            'state',
            'city',
            'postal_code',
            'recipient_name',
            'phone',
            'street'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        $oldShippingAddress = $order->getSettings('shipping_address');
        $data['recipient_name'] = !empty($oldShippingAddress['recipient_name'])
            ? $oldShippingAddress['recipient_name'] : null;

        $order->setSettings('shipping_address', $data);
        $order->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order address has been updated.'));

        $this->_redirect('*/adminhtml_common_amazon_order/view', array('id' => $order->getId()));
    }

    //#############################################

    public function updateShippingStatusAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Order(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var Ess_M2ePro_Model_Order[] $orders */
        $orders = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order')
                ->addFieldToFilter('id', array('in' => $ids))
                ->getItems();

        foreach ($orders as $order) {
            if ($order->getChildObject()->canUpdateShippingStatus()) {
                $order->getChildObject()->updateShippingStatus();
            }
        }

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Updating Amazon Order(s) Status to Shipped in Progress...')
        );

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################

    public function goToAmazonAction()
    {
        $magentoOrderId = $this->getRequest()->getParam('magento_order_id');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getModel('Order')->load($magentoOrderId, 'magento_order_id');

        if (is_null($order->getId())) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order does not exist.'));
            return $this->_redirect('*/adminhtml_common_order/index');
        }

        $url = Mage::helper('M2ePro/Component_Amazon')->getOrderUrl(
            $order->getChildObject()->getAmazonOrderId(), $order->getMarketplaceId()
        );

        return $this->_redirectUrl($url);
    }

    //#############################################
}