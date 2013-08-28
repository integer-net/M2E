<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Log_Cleaning extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('logCleaning');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listing_log';
        $this->_mode = 'cleaning';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Logs Clearing');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_log/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'LogCleaningHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'LogCleaningHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('run_all_now', array(
            'label'     => Mage::helper('M2ePro')->__('Run Enabled Now'),
            'onclick'   => 'LogCleaningHandlerObj.runNowLogs()',
            'class'     => 'save'
        ));

        $this->_addButton('clear_all_logs', array(
            'label'     => Mage::helper('M2ePro')->__('Clear All Logs'),
            'onclick'   => 'LogCleaningHandlerObj.clearAllLogs()',
            'class'     => 'save'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save Settings'),
            'onclick'   => 'LogCleaningHandlerObj.save_click()',
            'class'     => 'save'
        ));
        //------------------------------
    }
}