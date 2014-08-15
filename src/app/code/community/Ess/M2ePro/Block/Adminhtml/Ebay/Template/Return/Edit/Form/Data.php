<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Return_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateReturnEditFormData');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/return/form/data.phtml');
    }

    // ####################################

    public function isCustom()
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    public function getTitle()
    {
        if ($this->isCustom()) {
            return isset($this->_data['custom_title']) ? $this->_data['custom_title'] : '';
        }

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_return');

        if (is_null($template)) {
            return '';
        }

        return $template->getTitle();
    }

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_return');

        if (is_null($template) || is_null($template->getId())) {
            return array();
        }

        $data = $template->getData();

        return $data;
    }

    public function getDefault()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return Mage::getSingleton('M2ePro/Ebay_Template_Return')->getDefaultSettingsSimpleMode();
        }

        return Mage::getSingleton('M2ePro/Ebay_Template_Return')->getDefaultSettingsAdvancedMode();
    }

    public function getMarketplaceData()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (!$marketplace instanceof Ess_M2ePro_Model_Marketplace) {
            throw new LogicException('Marketplace is required for editing return template.');
        }

        $data = array(
            'id' => $marketplace->getId(),
            'info' => $marketplace->getChildObject()->getReturnPolicyInfo()
        );

        return $data;
    }

    // ####################################
}