<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Marketplace extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
{
    const TAB_ID_RAKUTEN = 'rakuten';

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('marketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_marketplace';
        //------------------------------

        // Form id of marketplace_general_form
        //------------------------------
        $this->tabsContainerId = 'edit_form';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Marketplaces');
        //------------------------------

        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        $activeWizard = $wizardHelper->getActiveUpgrade();

        if ($activeWizard &&
            $wizardHelper->getStep($wizardHelper->getNick($activeWizard)) == 'marketplace') {

            $this->setEnabledTab($wizardHelper->getNick($activeWizard));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplaceHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Complete This Step'),
                'onclick'   => 'MarketplaceHandlerObj.completeStep();',
                'class'     => 'close'
            ));

        } else {

            if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
                $this->_addButton('goto_general_templates', array(
                    'label'     => Mage::helper('M2ePro')->__('General Templates'),
                    'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_template_general/index').'\')',
                    'class'     => 'button_link'
                ));
            }

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplaceHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('run_synch_now', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Update'),
                'onclick'   => 'MarketplaceHandlerObj.saveSettings(\'runSynchNow\');',
                'class'     => 'save save_and_update_marketplaces'
            ));

        }
    }

    protected function initializeTabs()
    {
        $this->initializeEbay();
        $this->initializeAmazon();
        $this->initializeRakuten();
    }

    protected function initializeRakuten()
    {
        if (Mage::helper('M2ePro/Component')->isRakutenActive()) {
            $this->initializeTab(self::TAB_ID_RAKUTEN);
        }
    }

    // ########################################

    public function setEnabledTab($id)
    {
        if ($id == self::TAB_ID_BUY || $id == self::TAB_ID_PLAY) {
            $id = self::TAB_ID_RAKUTEN;
        }
        parent::setEnabledTab($id);
    }

    protected function getActiveTab()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/Component_Ebay')->isDefault()    && $activeTab = self::TAB_ID_EBAY;
            Mage::helper('M2ePro/Component_Amazon')->isDefault()  && $activeTab = self::TAB_ID_AMAZON;
            Mage::helper('M2ePro/Component')->isRakutenDefault() && $activeTab = self::TAB_ID_RAKUTEN;
        }

        return $activeTab;
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (!$this->getChild('ebay_tab')) {
            $this->setChild('ebay_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_marketplace_form'));
        }
        return $this->getChild('ebay_tab');
    }

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild('amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_marketplace_form'));
        }
        return $this->getChild('amazon_tab');
    }

    protected function getBuyTabBlock()
    {
        return null;
    }

    protected function getPlayTabBlock()
    {
        return null;
    }

    protected function getRakutenTabBlock()
    {
        if (!$this->getChild('rakuten_tab')) {
            $this->setChild(
                'rakuten_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_rakuten_marketplace_form')
            );
        }
        return $this->getChild('rakuten_tab');
    }

    protected function getRakutenTabHtml()
    {
        return $this->getRakutenTabBlock()->toHtml();
    }

    // ########################################

    protected function getTabLabelById($id)
    {
        if ($id == self::TAB_ID_RAKUTEN) {
            return Mage::helper('M2ePro')->__('Rakuten (Beta)');
        }

        return parent::getTabLabelById($id);
    }

    protected function _toHtml()
    {
        return '<div id="marketplaces_progress_bar"></div>' .
               '<div id="marketplaces_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    protected function _componentsToHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_help');

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_general_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

        return $helpBlock->toHtml() .
               parent::_componentsToHtml() .
               $formBlock->toHtml();
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '';
    }

    // ########################################

    public function canShowUpdateNowButton()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');
        $activeWizard = $wizardHelper->getActiveUpgrade();

        return !$activeWizard || $wizardHelper->getStep($wizardHelper->getNick($activeWizard)) != 'marketplace';
    }

    // ########################################
}