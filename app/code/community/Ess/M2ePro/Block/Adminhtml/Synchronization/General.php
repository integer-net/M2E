<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Synchronization_General extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationGeneral');
        $this->setContainerId('magento_block_general_synchronization');
        $this->setTemplate('M2ePro/synchronization/general.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->inspectorMode = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/synchronization/settings/defaults/inspector/','mode'
        );
        //----------------------------

        return parent::_beforeToHtml();
    }
}