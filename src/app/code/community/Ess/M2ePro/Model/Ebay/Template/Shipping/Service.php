<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Service extends Ess_M2ePro_Model_Component_Abstract
{
    const SHIPPING_TYPE_LOCAL         = 0;
    const SHIPPING_TYPE_INTERNATIONAL = 1;

    const COST_MODE_FREE             = 0;
    const COST_MODE_CUSTOM_VALUE     = 1;
    const COST_MODE_CUSTOM_ATTRIBUTE = 2;
    const COST_MODE_CALCULATED       = 3;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping_Service');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->shippingTemplateModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $this->shippingTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Shipping', $this->getTemplateShippingId(), NULL, array('template')
            );
            if (!is_null($this->getMagentoProduct())) {
                $this->shippingTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
         $this->shippingTemplateModel = $instance;
    }

    //------------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // #######################################

    public function getTemplateShippingId()
    {
        return (int)$this->getData('template_shipping_id');
    }

    public function getLocations()
    {
        return json_decode($this->getData('locations'),true);
    }

    public function getPriority()
    {
        return (int)$this->getData('priority');
    }

    // #######################################

    public function getShippingType()
    {
        return (int)$this->getData('shipping_type');
    }

    public function getShippingValue()
    {
        return $this->getData('shipping_value');
    }

    //-----------------------------------------

    public function isShippingTypeLocal()
    {
        return $this->getShippingType() == self::SHIPPING_TYPE_LOCAL;
    }

    public function isShippingTypeInternational()
    {
        return $this->getShippingType() == self::SHIPPING_TYPE_INTERNATIONAL;
    }

    // #######################################

    public function getCostMode()
    {
        return (int)$this->getData('cost_mode');
    }

    //-----------------------------------------

    public function getCostValue()
    {
        return $this->getData('cost_value');
    }

    public function getCostAdditionalValue()
    {
        return $this->getData('cost_additional_value');
    }

    public function getCostSurchargeValue()
    {
        return $this->getData('cost_surcharge_value');
    }

    //-----------------------------------------

    public function isCostModeFree()
    {
        return $this->getCostMode() == self::COST_MODE_FREE;
    }

    public function isCostModeCustomValue()
    {
        return $this->getCostMode() == self::COST_MODE_CUSTOM_VALUE;
    }

    public function isCostModeCustomAttribute()
    {
        return $this->getCostMode() == self::COST_MODE_CUSTOM_ATTRIBUTE;
    }

    //-----------------------------------------

    public function getCostAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostValue();
        }

        return $attributes;
    }

    public function getCostAdditionalAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostAdditionalValue();
        }

        return $attributes;
    }

    public function getCostSurchargeAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostSurchargeValue();
        }

        return $attributes;
    }

    // #######################################

    public function getCost()
    {
        $result = 0;

        switch ($this->getCostMode()) {
            case self::COST_MODE_FREE:
                $result = 0;
                break;
            case self::COST_MODE_CUSTOM_VALUE:
                $result = $this->getCostValue();
                break;
            case self::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getCostValue());
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    public function getCostAdditional()
    {
        $result = 0;

        switch ($this->getCostMode()) {
            case self::COST_MODE_FREE:
                $result = 0;
                break;
            case self::COST_MODE_CUSTOM_VALUE:
                $result = $this->getCostAdditionalValue();
                break;
            case self::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getCostAdditionalValue());
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    public function getCostSurcharge()
    {
        $result = 0;

        switch ($this->getCostMode()) {
            case self::COST_MODE_FREE:
                $result = 0;
                break;
            case self::COST_MODE_CUSTOM_VALUE:
                $result = $this->getCostSurchargeValue();
                break;
            case self::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getCostSurchargeValue());
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getCostAttributes(),
            $this->getCostAdditionalAttributes(),
            $this->getCostSurchargeAttributes()
        ));
    }

    // #######################################
}