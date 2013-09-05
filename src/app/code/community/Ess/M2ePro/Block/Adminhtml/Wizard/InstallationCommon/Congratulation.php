<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Congratulation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Congratulation
{
    // ########################################

    protected function _beforeToHtml()
    {
        $temp = parent::_beforeToHtml();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Congratulations! (Magento Multi-Channels Integration)');
        //------------------------------

        return $temp;
    }

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->helper('M2ePro/Module_Wizard')->createBlock('congratulation_content',$this->getNick())->toHtml();
    }

    // ########################################
}