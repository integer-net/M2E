<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    const TAB_ID_SYNCHRONIZATION        = 'synchronization';
    const TAB_ID_TEMPLATE               = 'template';
    const TAB_ID_MARKETPLACE            = 'marketplace';
    const TAB_ID_GENERAL                = 'general';
    const TAB_ID_ACCOUNT                = 'account';

    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('ebayConfigurationTabs');
        $this->setDestElementId('tabs_container');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $isAdvancedMode = Mage::helper('M2ePro/View_Ebay')->isAdvancedMode();

        $this->addTab(self::TAB_ID_GENERAL, $this->prepareTabGeneral());
        $this->addTab(self::TAB_ID_ACCOUNT, $this->prepareTabAccount());
        $this->addTab(self::TAB_ID_MARKETPLACE, $this->prepareTabMarketplace());
        $isAdvancedMode && $this->addTab(self::TAB_ID_TEMPLATE, $this->prepareTabTemplate());
        $this->addTab(self::TAB_ID_SYNCHRONIZATION, $this->prepareTabSynchronization());

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    // ########################################

    protected function prepareTabMarketplace()
    {
        $tab = array(
            'label' => $this->__('eBay Sites'),
            'title' => $this->__('eBay Sites')
        );

        if ($this->getData('active_tab') == self::TAB_ID_MARKETPLACE) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_marketplace_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_marketplace')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_marketplace/index');
        }

        return $tab;
    }

    protected function prepareTabSynchronization()
    {
        $tab = array(
            'label' => $this->__('Synchronization'),
            'title' => $this->__('Synchronization')
        );

        if ($this->getData('active_tab') == self::TAB_ID_SYNCHRONIZATION) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_synchronization_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_synchronization')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_synchronization/index');
        }

        return $tab;
    }

    protected function prepareTabTemplate()
    {
        $tab = array(
            'label' => $this->__('Policies'),
            'title' => $this->__('Policies')
        );

        if ($this->getData('active_tab') == self::TAB_ID_TEMPLATE) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_template/index');
        }

        return $tab;
    }

    protected function prepareTabGeneral()
    {
        $tab = array(
            'label' => $this->__('General'),
            'title' => $this->__('General')
        );

        if ($this->getData('active_tab') == self::TAB_ID_GENERAL) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_configuration_general_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_configuration_general')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_configuration/index');
        }

        return $tab;
    }

    protected function prepareTabAccount()
    {
        $tab = array(
            'label' => $this->__('Account Settings'),
            'title' => $this->__('Account Settings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_ACCOUNT) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_account/index');
        }

        return $tab;
    }

    // ########################################
}