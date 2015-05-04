<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_ModeSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ModeSwitcher_Abstract
{

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewModeSwitcher');
        //------------------------------

        $this->setData('component_nick', 'ebay');
        $this->setData('component_label', 'eBay');
    }

    protected function _toHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::_toHtml();
    }

    protected function getMenuItems()
    {
        $data = array(
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

        /** @var  $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('translation_status', array('neq' =>
            Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_NONE
        ));

        if ($collection->getSize()) {
            $data[] = array(
                'value' => 'translation',
                'label' => Mage::helper('M2ePro')->__('Translation')
            );
        }

        return $data;
    }

}