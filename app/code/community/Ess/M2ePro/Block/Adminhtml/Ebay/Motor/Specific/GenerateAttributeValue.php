<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Specific_GenerateAttributeValue
    extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/specific/generate_attribute_value.phtml');
    }

    protected function _beforeToHtml()
    {
        $specificsGrid = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_specific_grid');
        $this->setData('specifics_grid_id', $specificsGrid->getId());

        $attributeCode = $this->getData('motors_specifics_attribute');
        $attributeName = '';

        $attribute = Mage::getResourceModel('catalog/product')->getAttribute($attributeCode);

        if ($attribute !== false) {
            $attributeName = $attribute->getFrontendLabel();
        }

        $this->setData('motors_specifics_attribute', $attributeName);

        return parent::_beforeToHtml();
    }
}