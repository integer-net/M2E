<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_License_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configurationLicenseForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/license.phtml');
    }

    // ########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_license/confirmKey'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/LicenseHandler.js');
    }

    protected function _beforeToHtml()
    {
        try {
            Mage::helper('M2ePro/Client')->updateBackupConnectionData(true);
        } catch (Exception $exception) {}

        // Set data for form
        //----------------------------
        $this->key = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/Module_License')->getKey());

        $valid = array();
        $valid['domain'] = Mage::helper('M2ePro/Module_License')->getDomain();
        $valid['ip'] = Mage::helper('M2ePro/Module_License')->getIp();
        $valid['directory'] = Mage::helper('M2ePro/Module_License')->getDirectory();

        $this->valid = $valid;

        $components = array();
        foreach (Mage::helper('M2ePro/Component')->getAllowedComponents() as $component) {
            $components[$component] = array(
                'mode' => Mage::helper('M2ePro/Module_License')->getMode($component),
                'status' => Mage::helper('M2ePro/Module_License')->getStatus($component),
                'expiration_date' => Mage::helper('M2ePro/Module_License')->getTextExpirationDate($component)
            );
        }

        $this->components = $components;
        //----------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'setLocation(\'' . $this->getUrl('M2ePro/adminhtml_configuration_license/refreshStatus') . '\');',
            'class'   => 'refresh_status'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('refresh_status',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Enter'),
            'onclick' => 'LicenseHandlerObj.changeLicenseKey();',
            'class'   => 'enter_key'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('enter_key',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Change'),
            'onclick' => 'LicenseHandlerObj.changeLicenseKey();',
            'class'   => 'change_key'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_key',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'LicenseHandlerObj.confirmLicenseKey();',
            'class'   => 'confirm_key'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_key',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}