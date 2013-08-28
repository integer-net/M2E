<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SettingsController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/configuration')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Settings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Configuration/SettingsHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/configuration/settings');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_settings'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        $ebayMode = (int)$this->getRequest()->getParam('component_ebay_mode');
        $amazonMode = (int)$this->getRequest()->getParam('component_amazon_mode');
        $buyMode = (int)$this->getRequest()->getParam('component_buy_mode');
        $playMode = (int)$this->getRequest()->getParam('component_play_mode');

        if (!$ebayMode && !$amazonMode && !$buyMode && !$playMode) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('At least one channel should be enabled.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/', 'default',
            $this->getRequest()->getParam('component_default', Ess_M2ePro_Helper_Component_Ebay::NICK)
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/ebay/', 'mode',
            $ebayMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/amazon/', 'mode',
            $amazonMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/buy/', 'mode',
            $buyMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/play/', 'mode',
            $playMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/products/settings/', 'show_thumbnails',
            (int)$this->getRequest()->getParam('products_show_thumbnails')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/block_notices/settings/', 'show',
            (int)$this->getRequest()->getParam('block_notices_show')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/feedbacks/notification/', 'mode',
            (int)$this->getRequest()->getParam('feedbacks_notification_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/messages/notification/', 'mode',
            (int)$this->getRequest()->getParam('messages_notification_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/cron/notification/', 'mode',
            (int)$this->getRequest()->getParam('cron_notification_mode')
        );

        // Update Buy marketplace status
        // ----------------------------------
        Mage::helper('M2ePro/Component_Buy')->getCollection('Marketplace')
            ->getFirstItem()
            ->setData('status', $buyMode)
            ->save();
        // ----------------------------------

        // Update Play marketplace status
        // ----------------------------------
        Mage::helper('M2ePro/Component_Play')->getCollection('Marketplace')
            ->getFirstItem()
            ->setData('status', $playMode)
            ->save();
        // ----------------------------------

        Mage::helper('M2ePro/Wizard')->clearMenuCache();

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The global settings have been successfully saved.')
        );
        $this->_redirect('*/*/index');
    }

    //#############################################

    public function restoreBlockNoticesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('All help blocks were restored.'));
        $this->_redirect('*/*/index');
    }

    //#############################################
}