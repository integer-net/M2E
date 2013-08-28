<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Synchronization_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_synchronization_log';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization Log');
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

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_synchronization/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_synchronization', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_synchronization/index').'\')',
            'class'     => 'button_link'
        ));

        $url = $this->getUrl(
            '*/adminhtml_logCleaning/index',
            array(
                'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_log/synchronization')
            )
        );
        $this->_addButton('goto_logs_cleaning', array(
            'label'     => Mage::helper('M2ePro')->__('Clearing'),
            'onclick'   => 'setLocation(\'' .$url.'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        if (!is_null($this->getRequest()->getParam('synch_task'))) {

            $this->_addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/*').'\')',
                'class'     => 'show_general_log'
            ));
        }
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_synchronization_log_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }
}