<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Components_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configurationComponentsForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/components.phtml');
    }

    // ########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_components/save'),
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
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/ComponentsHandler.js');
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->isWizard = (bool)$this->getRequest()->getParam('wizard',false);

        $this->component_ebay_mode = Mage::helper('M2ePro/Component_Ebay')->isActive();
        $this->component_amazon_mode = Mage::helper('M2ePro/Component_Amazon')->isActive();
        $this->component_buy_mode = Mage::helper('M2ePro/Component_Buy')->isActive();
        $this->component_play_mode = Mage::helper('M2ePro/Component_Play')->isActive();

        $this->component_ebay_allowed = Mage::helper('M2ePro/Component_Ebay')->isAllowed();
        $this->component_amazon_allowed = Mage::helper('M2ePro/Component_Amazon')->isAllowed();
        $this->component_buy_allowed = Mage::helper('M2ePro/Component_Buy')->isAllowed();
        $this->component_play_allowed = Mage::helper('M2ePro/Component_Play')->isAllowed();

        $this->component_group_rakuten_allowed = Mage::helper('M2ePro/Component')->isRakutenAllowed();

        $this->view_common_component_default = Mage::helper('M2ePro/View_Common_Component')->getDefaultComponent();
        //----------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}