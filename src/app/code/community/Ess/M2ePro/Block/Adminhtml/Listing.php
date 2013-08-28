<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Listings');
        //------------------------------

        if (!is_null($this->getRequest()->getParam('back'))) {

            $tempUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$tempUrl.'\')',
                'class'     => 'back'
            ));
        }

        $tempUrl = $this->getUrl(
            '*/adminhtml_log/listing',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index'))
        );
        $this->_addButton('general_log', array(
            'label'     => Mage::helper('M2ePro')->__('General Log'),
            'onclick'   => 'setLocation(\'' . $tempUrl .'\')',
            'class'     => 'button_link'
        ));

        $tempUrl = $this->getUrl(
            '*/adminhtml_listing/search',
            array('back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index'))
        );
        $this->_addButton('search_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search Products'),
            'onclick'   => 'setLocation(\'' . $tempUrl . '\')',
            'class'     => 'button_link search'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_EBAY   => $this->getUrl('*/adminhtml_ebay_listing/index'),
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_amazon_listing/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_buy_listing/index'),
            self::TAB_ID_PLAY   => $this->getUrl('*/adminhtml_play_listing/index'),
        );
    }

    // ########################################

    protected function getTabsContainerBlock()
    {
        return parent::getTabsContainerBlock()->setId('listing');
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (!$this->getChild('ebay_tab')) {
            $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/cache/ebay/listing/', 'tutorial_shown');

            if (!$tutorialShowed) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing');
            }

            $this->setChild('ebay_tab', $block);
        }

        return $this->getChild('ebay_tab');
    }

    public function getEbayTabHtml()
    {
        $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/cache/ebay/listing/', 'tutorial_shown');

        if (!$tutorialShowed) {
            return parent::getEbayTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getEbayTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_filter')->toHtml() .
               parent::getEbayTabHtml();
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/cache/amazon/listing/', 'tutorial_shown');

            if (!$tutorialShowed) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing');
            }

            $this->setChild('amazon_tab', $block);
        }

        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/cache/amazon/listing/', 'tutorial_shown');

        if (!$tutorialShowed) {
            return parent::getAmazonTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getAmazonTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_filter')->toHtml() .
               parent::getAmazonTabHtml();
    }

    // ########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/cache/buy/listing/', 'tutorial_shown');

            if (!$tutorialShowed) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing');
            }

            $this->setChild('buy_tab', $block);
        }

        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/cache/buy/listing/', 'tutorial_shown');

        if (!$tutorialShowed) {
            return parent::getBuyTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getBuyTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_filter')->toHtml() .
               parent::getBuyTabHtml();
    }

    // ########################################

    protected function getPlayTabBlock()
    {
        if (!$this->getChild('play_tab')) {
            $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/cache/play/listing/', 'tutorial_shown');

            if (!$tutorialShowed) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_tutorial');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing');
            }

            $this->setChild('play_tab', $block);
        }

        return $this->getChild('play_tab');
    }

    public function getPlayTabHtml()
    {
        $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/cache/play/listing/', 'tutorial_shown');

        if (!$tutorialShowed) {
            return parent::getPlayTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getPlayTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_filter')->toHtml() .
               parent::getPlayTabHtml();
    }

    // ########################################

    protected function getTabHtmlById($id)
    {
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) != 1) {
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
        $javascript = <<<JAVASCRIPT

<script type="text/javascript">

if (varienGlobalEvents) {
    varienGlobalEvents.attachEventHandler('showTab', function() {
        if (typeof listingJsTabs == 'undefined') {
            return;
        }

        // we need to remove container if it is already exist to be sure
        // it has the last element with class content-header in DOM (see tools.js createToolbar())
        // ----------
        if ($('fake_buttons_container')) {
            $('fake_buttons_container').remove();
        }
        // ----------

        // prepare fake buttons container
        // ----------
        var fakeButtonsContainer = new Element('div', {
            id: 'fake_buttons_container'
        });

        document.body.insertBefore(fakeButtonsContainer, document.body.lastChild);

        fakeButtonsContainer.hide();
        // ----------

        // update fake buttons container html and reset floating toolbar
        // ----------
        var activeTabButtonsHtml = $$('#' + listingJsTabs.activeTab.id + '_content div.content-header')[0].innerHTML;
        $('fake_buttons_container').update('<div class="content-header">' + activeTabButtonsHtml + '</div>');

        updateTopButtonToolbarToggle();
        // ----------
    });
}

</script>

JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_help');

        return $helpBlock->toHtml() . parent::_componentsToHtml() . $javascript;
    }

    // ########################################

    public function getButtonsHtml($area = null)
    {
        $javascript = $this->getButtonsJavascript();

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) != 1) {
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
        $javascript .= $this->getEbayButtonsJavascript();
        $javascript .= $this->getAmazonButtonsJavascript();
        $javascript .= $this->getBuyButtonsJavascript();
        $javascript .= $this->getPlayButtonsJavascript();

        return $javascript;
    }

    private function getEbayButtonsJavascript()
    {
        if (!Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            return '';
        }

        if ($this->getActiveTab() != self::TAB_ID_EBAY) {
            return '';
        }

        return $this->getEbayTabBlock()->getTemplatesButtonJavascript();
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