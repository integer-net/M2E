<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Rakuten_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('rakutenMarketplaceForm');
        $this->setContainerId('magento_block_rakuten_marketplaces');
        $this->setTemplate('M2ePro/rakuten/marketplace.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $marketplaces = array();
        $marketplaces[] = Mage::helper('M2ePro/Component_Buy')->getCollection('Marketplace')->getFirstItem();
        $marketplaces[] = Mage::helper('M2ePro/Component_Play')->getCollection('Marketplace')->getFirstItem();

        $groups = array();
        $previewGroup = '';
        $idGroup = 1;

        foreach($marketplaces as $marketplace) {

            if ($marketplace->getGroupTitle() != $previewGroup) {
                $previewGroup = $marketplace->getGroupTitle();
                $groups[] = array(
                    'id'           => $idGroup,
                    'title'        => $previewGroup,
                    'marketplaces' => array()
                );
                $idGroup++;
            }

            $marketplace = array(
                'instance' => $marketplace,
                'params'   => array('locked'=>$marketplace->isLocked())
            );

            $groups[count($groups)-1]['marketplaces'][] = $marketplace;
        }

        $this->groups = $groups;
        //----------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => Mage::helper('M2ePro')->__('Update Now'),
            'onclick' => 'MarketplaceHandlerObj.runSingleMarketplaceSynchronization(this)',
            'class' => 'run_single_button'
        ) );

        $this->setChild('run_single_button', $buttonBlock);

        return parent::_beforeToHtml();
    }

    // ########################################
}