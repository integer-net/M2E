<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_ConfigurationController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Ebay/ConfigurationHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_configuration', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_GENERAL)
                )
            )->renderLayout();
    }

    public function globalAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_GLOBAL)
                )
            )->renderLayout();
    }

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/', 'mode',
            $this->getRequest()->getParam('view_ebay_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/feedbacks/notification/', 'mode',
            (int)$this->getRequest()->getParam('view_ebay_feedbacks_notification_mode')
        );

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/template/category/', 'use_last_specifics',
            (int)$this->getRequest()->getParam('use_last_specifics_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/connector/listing/', 'check_the_same_product_already_listed',
            (int)$this->getRequest()->getParam('check_the_same_product_already_listed_mode')
        );

        $sellingCurrency = $this->getRequest()->getParam('selling_currency');
        if (!empty($sellingCurrency)) {
            foreach ($sellingCurrency as $code => $value) {
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/selling/currency/', $code, (string)$value
                );
            }
        }

        $motorsSpecificsAttribute = $this->getRequest()->getParam('motors_specifics_attribute');
        $motorsKtypesAttribute = $this->getRequest()->getParam('motors_ktypes_attribute');

        if (!empty($motorsKtypesAttribute) && !empty($motorsSpecificsAttribute) &&
            $motorsSpecificsAttribute == $motorsKtypesAttribute
        ) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('ePIDs and KTypes attributes can not be the same.')
            );
            $this->_redirectUrl($this->_getRefererUrl());
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/motor/', 'motors_specifics_attribute',
            $motorsSpecificsAttribute
        );

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/motor/', 'motors_ktypes_attribute',
            $motorsKtypesAttribute
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Settings was successfully saved.'));
        $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################
}