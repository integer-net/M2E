<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    const TAB_ID_AMAZON     = 'amazon';
    const TAB_ID_BUY        = 'buy';
    const TAB_ID_PLAY       = 'play';

    // ----------------------------------------

    const LOG_TYPE_ID_LISTING            = 'listing';
    const LOG_TYPE_ID_LISTING_OTHER      = 'listing_other';
    const LOG_TYPE_ID_ORDER              = 'order';
    const LOG_TYPE_ID_SYNCHRONIZATION    = 'synchronization';

    // ########################################

    protected $urlMap = array(
        self::LOG_TYPE_ID_LISTING => 'listing',
        self::LOG_TYPE_ID_LISTING_OTHER => 'listingOther',
        self::LOG_TYPE_ID_ORDER => 'order',
        self::LOG_TYPE_ID_SYNCHRONIZATION => 'synchronization',
    );

    protected $logType;

    /**
     * @param string $logType
     */
    public function setLogType($logType)
    {
        $this->logType = $logType;
    }

    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('commonLogTabs');
        $this->setDestElementId('tabs_container');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $this->setLogType($this->getData('log_type'));

        $activeComponents = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();

        $this->setData('channel', $this->getActiveTab());
        $this->getRequest()->setParam('channel', $this->getData('channel'));

        foreach ($activeComponents as $component) {
            $prepareTabMethod = 'prepareTab'.ucfirst($component);
            $this->addTab($component, $this->$prepareTabMethod());
        }

        $this->setActiveTab($this->getData('channel'));

        return parent::_prepareLayout();
    }

    // ########################################

    protected function prepareTabAmazon()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
            'title' => Mage::helper('M2ePro/Component_Amazon')->getTitle()
        );

        if ($this->getData('channel') == self::TAB_ID_AMAZON) {
            $tab['content'] = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_'.$this->logType.'_log_grid', '',
                array(
                    'channel' => self::TAB_ID_AMAZON
                )
            )->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/'.$this->urlMap[$this->logType], array(
                'channel' => self::TAB_ID_AMAZON,
                'log_type' => $this->logType
            ));
        }

        return $tab;
    }

    protected function prepareTabBuy()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro/Component_Buy')->getTitle(),
            'title' => Mage::helper('M2ePro/Component_Buy')->getTitle()
        );

        if ($this->getData('channel') == self::TAB_ID_BUY) {
            $tab['content'] = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_'.$this->logType.'_log_grid', '',
                array(
                    'channel' => self::TAB_ID_BUY
                )
            )->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/'.$this->urlMap[$this->logType], array(
                'channel' => self::TAB_ID_BUY,
                'log_type' => $this->logType
            ));
        }

        return $tab;
    }

    protected function prepareTabPlay()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro/Component_Play')->getTitle(),
            'title' => Mage::helper('M2ePro/Component_Play')->getTitle()
        );

        if ($this->getData('channel') == self::TAB_ID_PLAY) {
            $tab['content'] = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_'.$this->logType.'_log_grid', '',
                array(
                    'channel' => self::TAB_ID_PLAY
                )
            )->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/'.$this->urlMap[$this->logType], array(
                'channel' => self::TAB_ID_PLAY,
                'log_type' => $this->logType
            ));
        }

        return $tab;
    }

    // ########################################

    protected function _toHtml()
    {
        $translations = json_encode(array(
            'Description' => Mage::helper('M2ePro')->__('Description')
        ));

        $tabElId = $this->getId();
        $tabsIds = $this->getTabsIds();

        $jsHideOneTab = '';

        if (count($tabsIds) === 1) {
            $jsHideOneTab = <<<JS
$('{$tabElId}').hide();
JS;
        }

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        LogHandlerObj = new LogHandler();

        {$jsHideOneTab}
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml() . '<div id="tabs_container"></div>';
    }

    // ########################################

    protected function getActiveTab()
    {
        $activeTab = $this->getData('channel');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault() && $activeTab = self::TAB_ID_AMAZON;
            Mage::helper('M2ePro/View_Common_Component')->isBuyDefault()    && $activeTab = self::TAB_ID_BUY;
            Mage::helper('M2ePro/View_Common_Component')->isPlayDefault()   && $activeTab = self::TAB_ID_PLAY;
        }

        return $activeTab;
    }

    // ########################################
}