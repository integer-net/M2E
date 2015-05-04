<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationNewAmazon_Installation_MarketplacesSynchronization
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationMarketplacesSynchronization');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationNewAmazon/installation/marketplacesSynchronization.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getModel('Marketplace');
        $collection = $marketplace->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                    'label'   => Mage::helper('M2ePro')->__('Proceed'),
                    'onclick' => "MigrationNewAmazonHandlerObj."
                                 ."marketplacesSynchronizationAction(this)",
                    'class' => 'process_marketplaces_button'
                ) );
        $this->setChild('process_marketplaces_synchronization_button',$buttonBlock);
        //-------------------------------

        $this->setData('enabledMarketplaces', $collection->getData());

        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}