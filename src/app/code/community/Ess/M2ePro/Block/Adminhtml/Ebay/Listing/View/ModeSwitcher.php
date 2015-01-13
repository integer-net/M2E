<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_ModeSwitcher extends Mage_Adminhtml_Block_Widget
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewModeSwitcher');
        //------------------------------
    }

    protected function _toHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        $data = array(
            'current_view_mode' => $this->getCurrentViewMode(),
            'route' => '*/*/view',
            'items' => array(
                array(
                    'value' => 'ebay',
                    'label' => Mage::helper('M2ePro')->__('eBay')
                ),
                array(
                    'value' => 'settings',
                    'label' => Mage::helper('M2ePro')->__('Settings')
                ),
                array(
                    'value' => 'magento',
                    'label' => Mage::helper('M2ePro')->__('Magento')
                )
            )
        );

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('translation_status', array('neq' =>
            Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_NONE
        ));

        if ($collection->getSize()) {
            $data['items'][] = array(
                'value' => 'translation',
                'label' => Mage::helper('M2ePro')->__('Translation')
            );
        }

        $modeChangeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_view_modeSwitcher');
        $modeChangeBlock->setData($data);
        $modeChangeLabel = Mage::helper('M2ePro')->__('View Mode');

        return <<<HTML
<div style="display: inline; float: left;"><b>{$modeChangeLabel}: </b>{$modeChangeBlock->toHtml()}</div>
HTML;
    }

    private function getCurrentViewMode()
    {
        if (!isset($this->_data['current_view_mode'])) {
            throw new LogicException('View mode is not set.');
        }

        return $this->_data['current_view_mode'];
    }
}