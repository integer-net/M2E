<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Account_Edit_Tabs_ListingOther extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyAccountEditTabsListingOther');
        //------------------------------

        $this->setTemplate('M2ePro/buy/account/tabs/listing_other.phtml');
    }

    protected function _beforeToHtml()
    {
        $attributesTemp = Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());

        $attributes = array();
        foreach ($attributesTemp as $attributeTemp) {
            if ((int)$attributeTemp->getData('is_visible') != 1) {
                continue;
            }
            $attributes[] = array(
                'label' => $attributeTemp->getData('frontend_label'),
                'code'  => $attributeTemp->getData('attribute_code')
            );
        }

        $this->attributes = $attributes;
        //var_dump($this->attributes); exit();

        return parent::_beforeToHtml();
    }
}