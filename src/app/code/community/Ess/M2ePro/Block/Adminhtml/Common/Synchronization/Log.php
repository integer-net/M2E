<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Synchronization_Log extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_synchronization_log';
        //------------------------------

        //------------------------------
        $this->setTemplate('M2ePro/common/log/log.phtml');
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
        //------------------------------

        if (!is_null($this->getRequest()->getParam('back'))) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_synchronization/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            //------------------------------
        }

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_synchronization/index');
        $this->_addButton('goto_synchronization', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        if (!is_null($this->getRequest()->getParam('task'))) {
            //------------------------------
            $url = $this->getUrl('*/*/*');
            $this->_addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' . $url .'\')',
                'class'     => 'show_general_log'
            ));
            //------------------------------
        }
    }

    // ########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_log_help')->toHtml();

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_log_tabs', '',
            array(
                'channel' => $this->getRequest()->getParam('channel'),
                'log_type' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::LOG_TYPE_ID_SYNCHRONIZATION
            )
        )->toHtml();

        $translations = json_encode(array(
            'Description' => Mage::helper('M2ePro')->__('Description')
        ));

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml() . $helpBlock . $logBlock;
    }

    // ########################################
}