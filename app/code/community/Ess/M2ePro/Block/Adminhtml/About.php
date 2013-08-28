<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('about');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'about';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('About');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('goto_cmd', array(
            'label'     => 'CMD',
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_cmd/index').'\')',
            'class'     => 'button_link cmd',
            'style'     => is_null($this->getRequest()->getParam('show_cmd')) ? 'display: none;' : ''
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

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }
}