<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Abstract extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    protected function removeButtons()
    {
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function appendButtons() {}

    protected function appendWizardCompleteButton()
    {
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                           'id'      => 'wizard_complete',
                                           'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                                           'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                                           'class'   => 'end_button',
                                           'style'   => 'display: none'
                                       ) );
        $this->setChild('end_button', $buttonBlock);
    }

    protected function appendWizardSkipButton()
    {
        $url = $this->getUrl('*/*/skip');
        $this->_addButton('skip', array(
            'label'     => Mage::helper('M2ePro')->__('Skip Wizard'),
            'onclick'   => 'WizardHandlerObj.skip(\''.$url.'\')',
            'class'     => 'skip'
        ));
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->getInitializationBlockHtml();
    }

    // ########################################

    protected function getInitializationBlockHtml()
    {
        $initializationBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_wizard_initialization',
            '',
            array('nick'=>$this->getNick())
        );

        return $initializationBlock->toHtml();
    }

    // ########################################
}