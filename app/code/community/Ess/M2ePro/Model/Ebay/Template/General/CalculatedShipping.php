<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping extends Ess_M2ePro_Model_Component_Abstract
{
    const MEASUREMENT_SYSTEM_ENGLISH = 1;
    const MEASUREMENT_SYSTEM_METRIC  = 2;

    const EBAY_MEASUREMENT_SYSTEM_ENGLISH = 'English';
    const EBAY_MEASUREMENT_SYSTEM_METRIC  = 'Metric';

    const PACKAGE_SIZE_EBAY             = 1;
    const PACKAGE_SIZE_CUSTOM_ATTRIBUTE = 2;

    const DIMENSIONS_NONE               = 0;
    const DIMENSIONS_CUSTOM_VALUE       = 1;
    const DIMENSIONS_CUSTOM_ATTRIBUTE   = 2;

    const WEIGHT_NONE                   = 0;
    const WEIGHT_CUSTOM_VALUE           = 1;
    const WEIGHT_CUSTOM_ATTRIBUTE       = 2;

    const HANDLING_NONE             = 0;
    const HANDLING_CUSTOM_VALUE     = 1;
    const HANDLING_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Template_General
     */
    private $generalTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_General_CalculatedShipping');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->generalTemplateModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        if (is_null($this->generalTemplateModel)) {
            $this->generalTemplateModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Template_General', $this->getId(), NULL, array('template')
            );
        }

        return $this->generalTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_General $instance
     */
    public function setGeneralTemplate(Ess_M2ePro_Model_Template_General $instance)
    {
         $this->generalTemplateModel = $instance;
    }

    // ########################################

    public function getPostalCode()
    {
        return $this->getData('originating_postal_code');
    }

    //------------------------------------------

    public function getMeasurementSystem()
    {
        return (int)$this->getData('measurement_system');
    }

    public function isMeasurementSystemMetric()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_METRIC;
    }

    public function isMeasurementSystemEnglish()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_ENGLISH;
    }

    //------------------------------------------

    public function getPackageSizeSource()
    {
        return array(
            'mode'      => $this->getData('package_size_mode'),
            'value'     => $this->getData('package_size_ebay'),
            'attribute' => $this->getData('package_size_attribute')
        );
    }

    public function getDimensionsSource()
    {
        return array(
            'mode' => $this->getData('dimension_mode'),

            'width_value'  => $this->getData('dimension_width'),
            'height_value' => $this->getData('dimension_height'),
            'depth_value'  => $this->getData('dimension_depth'),

            'width_attribute'  => $this->getData('dimension_width_attribute'),
            'height_attribute' => $this->getData('dimension_height_attribute'),
            'depth_attribute'  => $this->getData('dimension_depth_attribute')
        );
    }

    public function getWeightSource()
    {
        return array(
            'mode' => $this->getData('weight_mode'),
            'weight_major' => $this->getData('weight_major'),
            'weight_minor' => $this->getData('weight_minor'),
            'weight_attribute' => $this->getData('weight_attribute')
        );
    }

    //------------------------------------------

    public function getLocalHandlingSource()
    {
        return array(
            'mode'      => $this->getData('local_handling_cost_mode'),
            'value'     => $this->getData('local_handling_cost_value'),
            'attribute' => $this->getData('local_handling_cost_attribute')
        );
    }

    public function getInternationalHandlingSource()
    {
        return array(
            'mode'      => $this->getData('international_handling_cost_mode'),
            'value'     => $this->getData('international_handling_cost_value'),
            'attribute' => $this->getData('international_handling_cost_attribute')
        );
    }

    // ########################################
}