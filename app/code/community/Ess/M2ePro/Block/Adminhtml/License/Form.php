<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_License_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('licenseForm');
        //------------------------------

        $this->setTemplate('M2ePro/license.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/confirmKey'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->key = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/License')->getKey());

        $valid = array();
        $valid['domain'] = Mage::helper('M2ePro/License')->getDomain();
        $valid['ip'] = Mage::helper('M2ePro/License')->getIp();
        $valid['directory'] = Mage::helper('M2ePro/License')->getDirectory();

        $this->valid = $valid;

        $components = array();
        foreach (Mage::helper('M2ePro/Component')->getAllowedComponents() as $component) {
            $components[$component] = array(
                'mode' => Mage::helper('M2ePro/License')->getMode($component),
                'status' => Mage::helper('M2ePro/License')->getStatus($component),
                'expiration_date' => Mage::helper('M2ePro/License')->getTextExpirationDate($component)
            );
        }

        $this->components = $components;
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/*/refreshStatus').'\');',
                                'class' => 'refresh_status'
                            ) );
        $this->setChild('refresh_status',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Enter'),
                                'onclick' => 'LicenseHandlerObj.changeLicenseKey();',
                                'class' => 'enter_key'
                            ) );
        $this->setChild('enter_key',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Change'),
                                'onclick' => 'LicenseHandlerObj.changeLicenseKey();',
                                'class' => 'change_key'
                            ) );
        $this->setChild('change_key',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'LicenseHandlerObj.save_click(\''.$this->getUrl('*/*/confirmKey').'\');',
                                'class' => 'confirm_key'
                            ) );
        $this->setChild('confirm_key',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}