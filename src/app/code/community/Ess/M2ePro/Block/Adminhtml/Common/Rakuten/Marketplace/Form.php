<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Rakuten_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('rakutenMarketplaceForm');
        $this->setContainerId('magento_block_rakuten_marketplaces');
        $this->setTemplate('M2ePro/common/rakuten/marketplace.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $marketplaces = array();
        $marketplaces[Ess_M2ePro_Helper_Component_Buy::NICK] = Mage::helper('M2ePro/Component_Buy')
                                                                        ->getCollection('Marketplace')->getFirstItem();
        $marketplaces[Ess_M2ePro_Helper_Component_Play::NICK] = Mage::helper('M2ePro/Component_Play')
                                                                        ->getCollection('Marketplace')->getFirstItem();

        $activeWizard = $this->getActiveWizard();
        if (!is_null($activeWizard)) {
            $marketplaces = array($marketplaces[$activeWizard]);
        }

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

        //------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Update Now'),
            'onclick' => 'MarketplaceHandlerObj.runSingleMarketplaceSynchronization(this)',
            'class'   => 'run_single_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('run_single_button', $buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}