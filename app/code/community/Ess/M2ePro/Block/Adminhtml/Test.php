<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Test extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('test');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_test';
        //------------------------------

        // Form id of marketplace_general_form
        //------------------------------
        $this->tabsContainerId = 'edit_form';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Test');
        //------------------------------
    }

    protected function initializeTabs()
    {
        $this->initializeEbay();
        $this->initializeAmazon();
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (!$this->getChild('ebay_tab')) {
            $this->setChild('ebay_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_test_ebay'));
        }
        return $this->getChild('ebay_tab');
    }

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild('amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_test_amazon'));
        }
        return $this->getChild('amazon_tab');
    }

    protected function getPlayTabBlock()
    {
        return null;
    }

    protected function getBuyTabBlock()
    {
        return null;
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