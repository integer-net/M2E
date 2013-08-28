<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Motor_Specific extends Ess_M2ePro_Model_Abstract
{
    const TYPE_VEHICLE    = 0;
    const TYPE_MOTORCYCLE = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Motor_Specific');
    }

    // ########################################

    public function getProductType()
    {
        return (int)$this->getData('product_type');
    }

    public function isProductTypeVehicle()
    {
        return $this->getProductType() == self::TYPE_VEHICLE;
    }

    // ########################################

    public function getCompatibilityData()
    {
        $compatibilityData = array(
            'Make'  => $this->getData('make'),
            'Model' => $this->getData('model'),
            'Year'  => $this->getData('year')
        );

        if ($this->isProductTypeVehicle()) {
            $compatibilityData['Trim'] = $this->getData('trim');
            $compatibilityData['Engine'] = $this->getData('engine');
        } else {
            $compatibilityData['Submodel'] = $this->getData('submodel');
        }

        return $compatibilityData;
    }

    // ########################################
}