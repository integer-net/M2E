<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Order_Log extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('orderLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_order_log';
        //------------------------------

        //------------------------------
        $this->setTemplate('M2ePro/common/log/log.phtml');
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Orders Log');
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

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_order/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_orders', array(
            'label'     => Mage::helper('M2ePro')->__('Orders'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_common_order/index').'\')',
            'class'     => 'button_link'
        ));
        //------------------------------
    }

    // ########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_order_log_help')->toHtml();

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_log_tabs', '',
            array(
                'channel' => $this->getRequest()->getParam('channel'),
                'log_type' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::LOG_TYPE_ID_ORDER
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