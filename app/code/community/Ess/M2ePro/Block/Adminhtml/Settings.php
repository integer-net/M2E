<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Settings extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('settings');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'settings';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Settings');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        /* @var $installator Ess_M2ePro_Model_Wizard_Main */
        $installator = $wizardHelper->getInstallatorWizard();

        if ($wizardHelper->isInstallationActive() &&
            $wizardHelper->getStep($wizardHelper->getNick($installator)) == 'settings') {

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'SettingsHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Complete This Step'),
                'onclick'   => 'SettingsHandlerObj.completeStep();',
                'class'     => 'close'
            ));

        } else {
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

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'SettingsHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'SettingsHandlerObj.save_click()',
                'class'     => 'save'
            ));
        }
        //------------------------------
    }
}