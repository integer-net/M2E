<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    const ATTRIBUTE_MODE_NONE = 0;
    const ATTRIBUTE_MODE_CUSTOM_VALUE = 1;
    const ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE = 2;
    const ATTRIBUTE_MODE_RECOMMENDED_VALUE = 3;

    const TYPE_SELECT = 1;
    const TYPE_MULTISELECT = 2;
    const TYPE_INT = 3;
    const TYPE_STRING = 4;
    const TYPE_DECIMAL = 5;

    const TYPE_IS_REQUIRED = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_NewProduct_Attribute');
    }

    // ########################################

    public function getName()
    {
        return $this->getData('attribute_name');
    }

    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    public function getRecommendedValue()
    {
        return is_null($this->getData('recommended_value')) ? array() :
                json_decode($this->getData('recommended_value'),true);
    }

    public function getCustomValue()
    {
        return $this->getData('custom_value');
    }

    public function getCustomAttribute()
    {
        return $this->getData('custom_attribute');
    }

    public function getAttributeSource()
    {
        return array(
            'mode' => $this->getMode(),
            'name' => $this->getName(),
            'recommended_value' => $this->getRecommendedValue(),
            'custom_value' => $this->getCustomValue(),
            'custom_attribute' => $this->getCustomAttribute(),
        );
    }

    // ########################################
}