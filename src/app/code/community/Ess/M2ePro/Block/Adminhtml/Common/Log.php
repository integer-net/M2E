<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Log extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonLog');
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

        //------------------------------
        $this->setTemplate('M2ePro/common/log/log.phtml');
        //------------------------------
    }

    // ########################################

    public function getHeaderHtml()
    {
        $items = $this->getActiveChannelItems();

        $data = array(
            'target_css_class' => 'listing-profile-title',
            'style' => 'max-height: 120px; overflow: auto; width: 200px;',
            'items' => $items
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);
        //------------------------------

        return parent::getHeaderHtml() . $dropDownBlock->toHtml();
    }

    private function getActiveChannelItems()
    {
        $items = array();

        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $items[] = array(
                'label' => Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE),
                'url' => $this->getUrl('*/*/*', array(
                    '_current' => true,
                    'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_AMAZON
                ))
            );
        }
        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $items[] = array(
                'label' => Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE),
                'url' => $this->getUrl('*/*/*', array(
                    '_current' => true,
                    'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_BUY
                ))
            );
        }

        if (count($items) > 1) {
            array_unshift($items, array(
                'label' => Mage::helper('M2ePro')->__('All Channels'),
                'url' => $this->getUrl('*/*/*', array(
                    '_current' => true,
                    'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_ALL
                ))
            ));
        }

        return $items;
    }

    // ----------------------------------------

    public function getHeaderText()
    {
        //------------------------------
        $headerText = parent::getHeaderText();
        $channelTitle = '';
        //------------------------------

        $enabledComponents = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();

        if (count($enabledComponents) > 1) {
            $headerText = Mage::helper('M2ePro')->__('Logs of');
            $changeFilter = Mage::helper('M2ePro')->__('Filter by Channel');

            $channel = $this->getRequest()->getParam('channel');
            if (!empty($channel) && $channel != Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_ALL) {
                $channelTitle = constant( 'Ess_M2ePro_Helper_Component_' . ucfirst($channel) . '::TITLE' );
            } else {
                $channelTitle = Mage::helper('M2ePro')->escapeHtml('All Channels');
            }

            $channelTitle = <<<HTML
&nbsp;<a href="javascript: void(0);"
   id="listing-profile-title"
   class="listing-profile-title"
   style="font-weight: bold;"
   title="{$changeFilter}"><span class="drop_down_header">{$channelTitle}</span></a>
HTML;
        }

        return $headerText . $channelTitle;
    }

    // ########################################

    protected function _toHtml()
    {
        $css = <<<HTML

<style type="text/css">
    #listing_switcher_add_new_drop_down ul li {
        padding: 2px 5px 2px 10px !important;
    }
    #listing-profile-title_drop_down ul li {
        font-size: 12px !important;
    }
</style>

HTML;

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        $activeTab = !is_null($this->getData('active_tab')) ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::TAB_ID_LISTING;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_log_tabs', '', array('active_tab' => $activeTab)
        );

        return $css . $javascript .
            parent::_toHtml() .
            $tabsBlock->toHtml() .
            '<div id="tabs_container"></div>';
    }

    // ########################################
}