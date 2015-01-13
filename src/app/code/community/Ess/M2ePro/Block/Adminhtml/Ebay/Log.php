<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Log extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayLog');
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Logs');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        //------------------------------
        $this->setTemplate('M2ePro/ebay/log.phtml');
        //------------------------------
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
        CommonHandlerObj = new CommonHandler();
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        $activeTab = !is_null($this->getData('active_tab')) ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_LISTING;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_log_tabs', '', array('active_tab' => $activeTab)
        );

        return $javascript .
        parent::_toHtml() .
        $tabsBlock->toHtml() .
        '<div id="tabs_container"></div>';
    }
}