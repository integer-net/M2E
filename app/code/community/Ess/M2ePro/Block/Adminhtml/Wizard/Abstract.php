<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
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

        $this->_addButton('goto_about', array(
            'label'     => Mage::helper('M2ePro')->__('About'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_about/index').'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_support', array(
            'label'     => Mage::helper('M2ePro')->__('Support'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_support/index').'\')',
            'class'     => 'button_link'
        ));

        $videoLink = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/video_tutorials/', 'baseurl');
        $this->_addButton('goto_video_tutorials', array(
            'label'     => Mage::helper('M2ePro')->__('Video Tutorials'),
            'onclick'   => 'window.open(\''.$videoLink.'\', \'_blank\'); return false;',
            'class'     => 'button_link'
        ));

        $docsLink = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/documentation/', 'baseurl');
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