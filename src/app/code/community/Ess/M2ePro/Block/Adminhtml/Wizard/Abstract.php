<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Abstract extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    protected function prepareButtons()
    {
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('goto_support', array(
            'label'     => Mage::helper('M2ePro')->__('Support'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_support/index').'\')',
            'class'     => 'button_link'
        ));

        // --------------------------------

        $videoLink = Mage::helper('M2ePro/View_Ebay')->getVideoTutorialsUrl();

        if (Mage::helper('M2ePro/Module_Wizard')->getView($this->nick) == 'common') {
            $videoLink = Mage::helper('M2ePro/View_Common')->getVideoTutorialsUrl();
        }

        $this->_addButton('goto_video_tutorials', array(
            'label'     => Mage::helper('M2ePro')->__('Video Tutorials'),
            'onclick'   => 'window.open(\''.$videoLink.'\', \'_blank\'); return false;',
            'class'     => 'button_link'
        ));

        // --------------------------------

        $docsLink =  Mage::helper('M2ePro/View_Ebay')->getDocumentationUrl();

        if (Mage::helper('M2ePro/Module_Wizard')->getView($this->nick) == 'common') {
            $docsLink =  Mage::helper('M2ePro/View_Common')->getDocumentationUrl();
        }

        $this->_addButton('goto_docs', array(
            'label'     => Mage::helper('M2ePro')->__('Documentation'),
            'onclick'   => 'window.open(\''.$docsLink.'\', \'_blank\'); return false;',
            'class'     => 'button_link'
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