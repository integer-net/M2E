<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Amazon_Welcome extends Ess_M2ePro_Block_Adminhtml_Wizard_Welcome
{
    // ########################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $content = $this->helper('M2ePro/Wizard')->createBlock('welcome_content',$this->getNick());
        $this->setChild('content', $content);
        //------------------------------

        $temp = parent::_beforeToHtml();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Welcome to Magento Amazon Integration!');
        //------------------------------

        return $temp;
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('content')
            . $this->getChildHtml('start_wizard_button');
    }

    // ########################################
}