<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Listings');
        //------------------------------

        //------------------------------
        if (!is_null($this->getRequest()->getParam('back'))) {
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
        }
        //------------------------------

        //------------------------------
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_listing/index');
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/listing', array('back' => $backUrl));
        $this->_addButton('general_log', array(
            'label'     => Mage::helper('M2ePro')->__('General Log'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing/search', array('back' => $backUrl));
        $this->_addButton('search_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search Products'),
            'onclick'   => 'setLocation(\'' . $url . '\')',
            'class'     => 'button_link search'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        //------------------------------
        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_common_amazon_listing/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_common_buy_listing/index'),
            self::TAB_ID_PLAY   => $this->getUrl('*/adminhtml_common_play_listing/index'),
        );
        //------------------------------
    }

    // ########################################

    protected function getTabsContainerBlock()
    {
        return parent::getTabsContainerBlock()->setId('listing');
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $tutorialShown = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/view/common/amazon/listing/', 'tutorial_shown');

            if (!$tutorialShown) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing');
            }

            $this->setChild('amazon_tab', $block);
        }

        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        $tutorialShown = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/common/amazon/listing/', 'tutorial_shown');

        if (!$tutorialShown) {
            return parent::getAmazonTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getAmazonTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_filter')->toHtml() .
               parent::getAmazonTabHtml();
    }

    // ########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $tutorialShown = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/view/common/buy/listing/', 'tutorial_shown');

            if (!$tutorialShown) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing');
            }

            $this->setChild('buy_tab', $block);
        }

        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        $tutorialShown = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/view/common/buy/listing/', 'tutorial_shown');

        if (!$tutorialShown) {
            return parent::getBuyTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getBuyTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_filter')->toHtml() .
               parent::getBuyTabHtml();
    }

    // ########################################

    protected function getPlayTabBlock()
    {
        if (!$this->getChild('play_tab')) {
            $tutorialShown = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/view/common/play/listing/', 'tutorial_shown');

            if (!$tutorialShown) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing');
            }

            $this->setChild('play_tab', $block);
        }

        return $this->getChild('play_tab');
    }

    public function getPlayTabHtml()
    {
        $tutorialShown = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/common/play/listing/', 'tutorial_shown');

        if (!$tutorialShown) {
            return parent::getPlayTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getPlayTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_filter')->toHtml() .
               parent::getPlayTabHtml();
    }

    // ########################################

    protected function getTabHtmlById($id)
    {
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            return parent::getTabHtmlById($id);
        }

        /** @var $singleBlock Mage_Core_Block_Abstract|Mage_Adminhtml_Block_Widget_Grid_Container */
        $singleBlock = $this->getSingleBlock();

        if (is_object($singleBlock) && $singleBlock instanceof Mage_Adminhtml_Block_Widget_Grid_Container) {
            return $singleBlock->getGridHtml();
        }

        return parent::getTabHtmlById($id);
    }

    protected function _componentsToHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_help');
        $floatingToolbarFixer = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_floatingToolbarFixer');

        return $helpBlock->toHtml() . $floatingToolbarFixer->toHtml() . parent::_componentsToHtml();
    }

    // ########################################

    public function getButtonsHtml($area = null)
    {
        $javascript = $this->getButtonsJavascript();

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            return $javascript . parent::getButtonsHtml($area);
        }

        return $javascript . $this->getSingleBlock()->getButtonsHtml();
    }

    private function getButtonsJavascript()
    {
        if (count($this->tabs) <= 0) {
            return '';
        }

        if (count($this->tabs) == 1) {
            return $this->getSingleBlock()->getTemplatesButtonJavascript();
        }

        $javascript = '';
        $javascript .= $this->getAmazonButtonsJavascript();
        $javascript .= $this->getBuyButtonsJavascript();
        $javascript .= $this->getPlayButtonsJavascript();

        return $javascript;
    }

    private function getAmazonButtonsJavascript()
    {
        if (!Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            return '';
        }

        if ($this->getActiveTab() != self::TAB_ID_AMAZON) {
            return '';
        }

        return $this->getAmazonTabBlock()->getTemplatesButtonJavascript();
    }

    private function getBuyButtonsJavascript()
    {
        if (!Mage::helper('M2ePro/Component_Buy')->isActive()) {
            return '';
        }

        if ($this->getActiveTab() != self::TAB_ID_BUY) {
            return '';
        }

        return $this->getBuyTabBlock()->getTemplatesButtonJavascript();
    }

    private function getPlayButtonsJavascript()
    {
        if (!Mage::helper('M2ePro/Component_Play')->isActive()) {
            return '';
        }

        if ($this->getActiveTab() != self::TAB_ID_PLAY) {
            return '';
        }

        return $this->getPlayTabBlock()->getTemplatesButtonJavascript();
    }

    // ########################################
}