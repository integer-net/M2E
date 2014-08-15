<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    const TAB_ID_AMAZON = 'amazon';
    const TAB_ID_BUY    = 'buy';
    const TAB_ID_PLAY   = 'play';

    // ########################################

    protected $tabs = array();

    protected $enabledTab = NULL;

    protected $tabsContainerBlock = NULL;

    protected $tabsContainerId = 'components_container';

    protected $useAjax = false;

    protected $tabsAjaxUrls = array();

    // ########################################

    static public function getTabIdByComponent($component)
    {
        $tabId = null;

        switch ($component) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $tabId = self::TAB_ID_AMAZON;
                break;
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $tabId = self::TAB_ID_BUY;
                break;
            case Ess_M2ePro_Helper_Component_Play::NICK:
                $tabId = self::TAB_ID_PLAY;
                break;
        }

        return $tabId;
    }

    // ########################################

    public function enableAmazonTab()
    {
        $this->setEnabledTab(self::TAB_ID_AMAZON);
    }

    public function enableBuyTab()
    {
        $this->setEnabledTab(self::TAB_ID_BUY);
    }

    public function enablePlayTab()
    {
        $this->setEnabledTab(self::TAB_ID_PLAY);
    }

    // ########################################

    public function setEnabledTab($id)
    {
        $this->enabledTab = $id;
    }

    // ----------------------------------------

    protected function isTabEnabled($id)
    {
        if (is_null($this->enabledTab)) {
            return true;
        }

        return $id == $this->enabledTab;
    }

    // ----------------------------------------

    protected function canUseAjax()
    {
        if (count($this->tabs) < 2) {
            return false;
        }

        return $this->useAjax;
    }

    // ########################################

    protected function initializeTabs()
    {
        $this->initializeAmazon();
        $this->initializeBuy();
        $this->initializePlay();
    }

    protected function initializeTab($id)
    {
        if ($this->isTabEnabled($id) && !in_array($id, $this->tabs)) {
            $this->tabs[] = $id;
        }
    }

    protected function initializeAmazon()
    {
        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $this->initializeTab(self::TAB_ID_AMAZON);
        }
    }

    protected function initializeBuy()
    {
        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $this->initializeTab(self::TAB_ID_BUY);
        }
    }

    protected function initializePlay()
    {
        if (Mage::helper('M2ePro/Component_Play')->isActive()) {
            $this->initializeTab(self::TAB_ID_PLAY);
        }
    }

    // ########################################

    protected function getTabBlockById($id)
    {
        $id = ucfirst($id);
        $method = "get{$id}TabBlock";

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return NULL;
    }

    protected function getTabHtmlById($id)
    {
        $id = ucfirst($id);
        $method = "get{$id}TabHtml";

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return '';
    }

    protected function getTabLabelById($id)
    {
        $id = ucfirst($id);
        $label = @constant("Ess_M2ePro_Helper_Component_{$id}::TITLE");

        return $label ? Mage::helper('M2ePro')->__($label) : Mage::helper('M2ePro')->__('N/A');
    }

    protected function getTabUrlById($id)
    {
        return isset($this->tabsAjaxUrls[$id]) ? $this->tabsAjaxUrls[$id] : '';
    }

    // ########################################

    protected function _prepareLayout()
    {
        if (count(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents()) == 0) {
            throw new LogicException('At least 1 channel should be enabled.');
        }

        $this->initializeTabs();

        parent::_prepareLayout();
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->_componentsToHtml();
    }

    protected function _componentsToHtml()
    {
        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        if ($tabsCount == 1) {
            $tabId = reset($this->tabs);

            return $this->getTabHtmlById($tabId);
        }

        $tabsContainer = $this->getTabsContainerBlock();
        $tabsContainer->setDestElementId($this->tabsContainerId);

        foreach ($this->tabs as $tabId) {
            $tab = $this->prepareTabById($tabId);
            $tabsContainer->addTab($tabId, $tab);
        }

        $tabsContainer->setActiveTab($this->getActiveTab());

        return $tabsContainer->toHtml() . $this->getTabsContainerDestinationHtml();
    }

    // ########################################

    protected function prepareTabById($id)
    {
        $label = $this->getTabLabelById($id);

        $tab = array(
            'label' => $label,
            'title' => $label
        );

        if ($this->canUseAjax() && $this->getActiveTab() != $id) {
            $tab['class'] = 'ajax';
            $tab['url'] = $this->getTabUrlById($id);
        } else {
            $tab['content'] = $this->getTabHtmlById($id);
        }

        return $tab;
    }

    // ########################################

    protected function getSingleBlock()
    {
        if (count($this->tabs) != 1) {
            return NULL;
        }

        $tabId = reset($this->tabs);

        return $this->getTabBlockById($tabId);
    }

    // ########################################

    /**
     * @abstract
     * @return Mage_Core_Block_Abstract
     */
    abstract protected function getAmazonTabBlock();

    public function getAmazonTabHtml()
    {
        return $this->getAmazonTabBlock()->toHtml();
    }

    /**
     * @abstract
     * @return Mage_Core_Block_Abstract
     */
    abstract protected function getBuyTabBlock();

    public function getBuyTabHtml()
    {
        return $this->getBuyTabBlock()->toHtml();
    }

    /**
     * @abstract
     * @return Mage_Core_Block_Abstract
     */
    abstract protected function getPlayTabBlock();

    public function getPlayTabHtml()
    {
        return $this->getPlayTabBlock()->toHtml();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs
     */
    protected function getTabsContainerBlock()
    {
        if (is_null($this->tabsContainerBlock)) {
            $this->tabsContainerBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_component_tabs');
        }

        return $this->tabsContainerBlock;
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '<div id="'.$this->tabsContainerId.'"></div>';
    }

    // ########################################

    protected function getActiveTab()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault() && $activeTab = self::TAB_ID_AMAZON;
            Mage::helper('M2ePro/View_Common_Component')->isBuyDefault()    && $activeTab = self::TAB_ID_BUY;
            Mage::helper('M2ePro/View_Common_Component')->isPlayDefault()   && $activeTab = self::TAB_ID_PLAY;
        }

        return $activeTab;
    }

    // ########################################
}