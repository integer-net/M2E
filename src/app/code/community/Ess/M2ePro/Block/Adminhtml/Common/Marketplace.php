<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Marketplace extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    const TAB_ID_RAKUTEN = 'rakuten';

    // ########################################

    private $activeWizardNick = NULL;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('marketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_marketplace';
        //------------------------------

        // Form id of marketplace_general_form
        //------------------------------
        $this->tabsContainerId = 'edit_form';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Marketplaces');
        //------------------------------

        if ((bool)$this->getRequest()->getParam('wizard',false)) {
            $this->activeWizardNick = Mage::helper('M2ePro/Module_Wizard')->getNick(
                Mage::helper('M2ePro/Module_Wizard')->getActiveWizard(Ess_M2ePro_Helper_View_Common::NICK)
            );

            $this->setEnabledTab($this->getTabIdByWizardNick($this->activeWizardNick));

            //------------------------------
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplaceHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Complete This Step'),
                'onclick'   => 'MarketplaceHandlerObj.completeStepAction();',
                'class'     => 'close'
            ));
            //------------------------------
        } else {
            //------------------------------
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplaceHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
            //------------------------------

            //------------------------------
            $this->addButton('run_update_all', array(
                'label' => Mage::helper('M2ePro')->__('Update All Now'),
                'onclick' => 'MarketplaceHandlerObj.updateAction()',
                'class' => 'save update_all_marketplace'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('run_synch_now', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'MarketplaceHandlerObj.saveAction();',
                'class'     => 'save save_and_update_marketplaces'
            ));
            //------------------------------
        }
    }

    protected function initializeTabs()
    {
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
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault()  && $activeTab = self::TAB_ID_AMAZON;
            Mage::helper('M2ePro/View_Common_Component')->isRakutenDefault() && $activeTab = self::TAB_ID_RAKUTEN;
        }

        return $activeTab;
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_marketplace_form')
            );
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
                'rakuten_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_rakuten_marketplace_form','',
                                                 array('active_wizard' => $this->activeWizardNick))
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
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace_help');

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace_general_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

        return $helpBlock->toHtml() .
               parent::_componentsToHtml() .
               $formBlock->toHtml();
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '';
    }

    protected function getTabIdByWizardNick($wizardNick)
    {
        if ($wizardNick == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            return self::TAB_ID_AMAZON;
        }

        return self::TAB_ID_RAKUTEN;
    }

    // ########################################

    public function canShowUpdateNowButton()
    {
        return !(bool)$this->getRequest()->getParam('wizard',false);
    }

    // ########################################
}