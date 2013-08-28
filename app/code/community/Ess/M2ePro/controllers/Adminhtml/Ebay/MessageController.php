<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_MessageController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/communication')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Communication'))
             ->_title(Mage::helper('M2ePro')->__('My Messages'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Ebay/MessageHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/communication/my_message');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction();

        //$this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_message'));
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_message_comingSoon'));

        $this->renderLayout();
    }

    public function messageGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_message_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function saveAction()
    {
        $messageId = $this->getRequest()->getParam('message_id');
        $messageText = $this->getRequest()->getParam('message_text');

        $messageModel = Mage::getModel('M2ePro/Ebay_Message')->loadInstance($messageId);
        $messageModel->sendResponse($messageText);

        $paramsConnector = array(
            'since_time' => $messageModel->getData('message_date')
        );

        Mage::getModel('M2ePro/Ebay_Message')->receiveFromEbay($messageModel->getAccount(), $paramsConnector);
    }

    //#############################################

    public function getMessageInfoAction()
    {
        $messageId = $this->getRequest()->getParam('message_id');
        $messageModel = Mage::getModel('M2ePro/Ebay_Message')->loadInstance($messageId);

        $messageInfo = array();
        $messageInfo['text'] = $messageModel->getData('message_text');
        $messageInfo['responses'] = json_decode($messageModel->getData('message_responses'));

        exit(json_encode(array(
            'message_info' => $messageInfo
        )));
    }

    //#############################################
}