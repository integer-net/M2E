<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_View_ModeSwitcher_Abstract extends Mage_Adminhtml_Block_Widget
{
    const NICK = 'default';
    const LABEL = 'Default';

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingViewModeSwitcher');
        //------------------------------

        $this->setData('component_nick', self::NICK);
        $this->setData('component_label', self::LABEL);
    }

    protected function _toHtml()
    {
        $data = array(
            'current_view_mode' => $this->getCurrentViewMode(),
            'route' => '*/*/view',
            'items' => $this->getMenuItems()
        );

        $modeChangeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_view_modeSwitcher');
        $modeChangeBlock->setData($data);
        $modeChangeLabel = Mage::helper('M2ePro')->__('View Mode');

        return <<<HTML
<div style="display: inline; float: left;"><b>{$modeChangeLabel}: </b>{$modeChangeBlock->toHtml()}</div>
HTML;
    }

    protected function getMenuItems()
    {
        return array(
            array(
                'value' => $this->getComponentNick(),
                'label' => Mage::helper('M2ePro')->__($this->getComponentLabel())
            ),
            array(
                'value' => 'settings',
                'label' => Mage::helper('M2ePro')->__('Settings')
            ),
            array(
                'value' => 'magento',
                'label' => Mage::helper('M2ePro')->__('Magento')
            )
        );
    }

    private function getCurrentViewMode()
    {
        if (!isset($this->_data['current_view_mode'])) {
            throw new LogicException('View Mode is not set.');
        }

        return $this->_data['current_view_mode'];
    }
}