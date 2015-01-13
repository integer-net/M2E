<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Order_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
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

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    // ########################################

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_order_log_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
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

        return $javascript . parent::_toHtml();
    }

    // ########################################
}