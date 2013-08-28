<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonMarketplaceForm');
        $this->setContainerId('magento_block_amazon_marketplaces');
        $this->setTemplate('M2ePro/amazon/marketplace.phtml');
        //------------------------------
    }

    protected function _prepareForm()
    {
        return parent::_prepareForm();
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
                                                        ->setOrder('group_title', 'ASC')
                                                        ->setOrder('sorder','ASC')
                                                        ->setOrder('title','ASC')
                                                        ->getItems();
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