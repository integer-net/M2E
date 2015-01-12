<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Marketplace_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('edit_form');
        $this->setContainerId('magento_block_ebay_marketplaces');
        $this->setTemplate('M2ePro/ebay/marketplace/form.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $tempMarketplaces = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->getItems();

        $marketplaces = array();
        $storedStatuses = array();

        foreach($tempMarketplaces as $tempMarketplace) {

            /* @var $tempMarketplace Ess_M2ePro_Model_Marketplace */
            $marketplaces[] = array(
                'instance' => $tempMarketplace,
                'params'   => array('locked'=>$tempMarketplace->isLocked())
            );

            $storedStatuses[] = array(
                'marketplace_id' => $tempMarketplace->getMarketplaceId(),
                'status' => $tempMarketplace->getStatus()
            );
        }

        $this->marketplaces = $marketplaces;
        $this->storedStatuses = $storedStatuses;
        //----------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Update Now'),
                'onclick' => 'MarketplaceHandlerObj.runSingleSynchronization(this)',
                'class' => 'run_single_button'
            ) );

        $this->setChild('run_single_button', $buttonBlock);

        return parent::_beforeToHtml();
    }

    // ########################################
}