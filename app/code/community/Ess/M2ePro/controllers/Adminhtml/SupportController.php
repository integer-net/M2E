<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SupportController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/help')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Help'))
             ->_title(Mage::helper('M2ePro')->__('Support'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/SupportHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/help/support');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_support'))
             ->renderLayout();
    }

    //#############################################

    public function getResultsHtmlAction()
    {
        $query = $this->getRequest()->getParam('query');
        $blockData = Mage::getModel('M2ePro/Support')->getUserVoiceData($query);

        $blockHtml = $this->loadLayout()
                          ->getLayout()
                          ->createBlock('M2ePro/adminhtml_support_results', '', array('user_voice_data' => $blockData))
                          ->toHtml();
        $this->getResponse()->setBody($blockHtml);
    }

    //#############################################

    public function documentationAction()
    {
        $url = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/documentation/', 'baseurl');

        $html = '<iframe src="' .$url . '" width="100%" height="650"></iframe>';
        $this->getResponse()->setBody($html);
    }

    public function knowledgeBaseAction()
    {
        $url = $this->getRequest()->getParam('url');
        if (is_null($url)) {
            $url = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/knowledge_base/', 'baseurl');
        } else {
            $url = base64_decode($url);
        }

        $html = '<iframe src="' . $url . '" width="100%" height="650"></iframe>';
        $this->getResponse()->setBody($html);
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        $keys = array(
            'subject',
            'contact_mail',
            'contact_name',
            'component',
            'description'
        );

        $components = Mage::helper('M2ePro/Component')->getActiveComponents();
        count($components) == 1 && $post['component'] = array_pop($components);

        $data = array();
        foreach ($keys as $key) {
            if (!isset($post[$key])) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should fill in all required fields.'));
                return $this->_redirect('*/*/index');
            }
            $data[$key] = $post[$key];
        }

        Mage::getModel('M2ePro/Support')->send($data);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Your message has been successfully sent.'));
        $this->_redirect('*/*/index');
    }

    //#############################################
}