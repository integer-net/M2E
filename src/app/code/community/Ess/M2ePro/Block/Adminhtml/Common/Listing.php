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
        $url = $this->getUrl('*/adminhtml_common_listing/search', array('back' => $backUrl));
        $this->_addButton('search_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search'),
            'onclick'   => 'setLocation(\'' . $url . '\')',
            'class'     => 'button_link search'
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

    protected function _toHtml()
    {
        $urls = json_encode(array(
            'adminhtml_common_listing/saveTitle' => Mage::helper('adminhtml')
                                                        ->getUrl('M2ePro/adminhtml_common_listing/saveTitle')
        ));

        $translations = json_encode(array(
            'Cancel' => Mage::helper('M2ePro')->__('Cancel'),
            'Save' => Mage::helper('M2ePro')->__('Save'),
            'Edit Listing Title' => Mage::helper('M2ePro')->__('Edit Listing Title'),
        ));

        $uniqueTitleTxt = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('The specified Title is already used for other Listing. Listing Title must be unique.'));

        $constants = Mage::helper('M2ePro')
            ->getClassConstantAsJson('Ess_M2ePro_Helper_Component_'.ucfirst($this->getActiveTab()));

        $javascripts = <<<HTML

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        M2ePro.url.add({$urls});
        M2ePro.translator.add({$translations});
    });

    M2ePro.text.title_not_unique_error = '{$uniqueTitleTxt}';

    M2ePro.php.setConstants(
        {$constants},
        'Ess_M2ePro_Helper_Component'
    );

    var editListingTitle = function(el)
    {
        EditListingTitleObj.gridId = listingJsTabs.activeTab.id.replace('listing_', '') + 'ListingGrid';
        EditListingTitleObj.openPopup(el);
    }

    EditListingTitleObj = new EditListingTitle();

</script>

HTML;

        return parent::_toHtml().$javascripts;
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing');

            $this->setChild('amazon_tab', $block);
        }

        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_filter')->toHtml() .
               parent::getAmazonTabHtml();
    }

    // ########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing');

            $this->setChild('buy_tab', $block);
        }

        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_filter')->toHtml() .
               parent::getBuyTabHtml();
    }

    // ########################################

    protected function getPlayTabBlock()
    {
        if (!$this->getChild('play_tab')) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing');

            $this->setChild('play_tab', $block);
        }

        return $this->getChild('play_tab');
    }

    public function getPlayTabHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_filter')->toHtml() .
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