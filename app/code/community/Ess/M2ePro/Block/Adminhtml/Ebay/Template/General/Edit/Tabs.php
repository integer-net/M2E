<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_General_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateGeneralEditTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
                'label'   => Mage::helper('M2ePro')->__('General'),
                'title'   => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs_general')
                                  ->toHtml(),
            ))
            ->addTab('specific', array(
                'label'   => Mage::helper('M2ePro')->__('Item Specifics'),
                'title'   => Mage::helper('M2ePro')->__('Item Specifics'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs_specific')
                                  ->toHtml(),
            ))
            ->addTab('upgrade', array(
                'label'   => Mage::helper('M2ePro')->__('Listing Upgrades'),
                'title'   => Mage::helper('M2ePro')->__('Listing Upgrades'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs_upgrade')
                                  ->toHtml(),
            ))
            ->addTab('shipping', array(
                'label'   => Mage::helper('M2ePro')->__('Shipping'),
                'title'   => Mage::helper('M2ePro')->__('Shipping'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs_shipping')
                                  ->toHtml(),
            ))
            ->addTab('payment', array(
                'label'   => Mage::helper('M2ePro')->__('Payment'),
                'title'   => Mage::helper('M2ePro')->__('Payment'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs_payment')
                                  ->toHtml(),
            ))
            ->addTab('refund', array(
                'label'   => Mage::helper('M2ePro')->__('Return Policy'),
                'title'   => Mage::helper('M2ePro')->__('Return Policy'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs_refund')
                                  ->toHtml(),
            ))
            ->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    // ####################################
}