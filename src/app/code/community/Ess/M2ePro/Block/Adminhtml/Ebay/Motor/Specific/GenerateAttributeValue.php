<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Specific_GenerateAttributeValue
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/specific/generate_attribute_value.phtml');
    }

    protected function _beforeToHtml()
    {
        $specificsGrid = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_specific_grid');
        $this->setData('specifics_grid_id', $specificsGrid->getId());

        return parent::_beforeToHtml();
    }

    // ####################################
}