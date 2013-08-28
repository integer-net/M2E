<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_General_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    const TYPE_LOCAL         = 0;
    const TYPE_INTERNATIONAL = 1;

    const SHIPPING_FREE             = 0;
    const SHIPPING_CUSTOM_VALUE     = 1;
    const SHIPPING_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Template_General
     */
    private $generalTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_General_Shipping');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->generalTemplateModel = NULL;
        $temp && $this->magentoProductModel = NULL;
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
                'Template_General', $this->getData('template_general_id'), NULL, array('template')
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

    // ########################################

    public function getShippingType()
    {
        return (int)$this->getData('shipping_type');
    }

    public function isShippingTypeLocal()
    {
        return $this->getShippingType() == self::TYPE_LOCAL;
    }

    public function isShippingTypeInternational()
    {
        return $this->getShippingType() == self::TYPE_INTERNATIONAL;
    }

    //-----------------------------------------

    public function getShippingValue()
    {
        return $this->getData('shipping_value');
    }

    public function getPriority()
    {
        return (int)$this->getData('priority');
    }

    public function getLocations()
    {
        return json_decode($this->getData('locations'),true);
    }

    //-----------------------------------------

    public function getCostMode()
    {
        return (int)$this->getData('cost_mode');
    }

    public function isCostModeFree()
    {
        return $this->getCostMode() == self::SHIPPING_FREE;
    }

    public function getCost()
    {
        $result = 0;

        switch ($this->getCostMode()) {
            case self::SHIPPING_FREE:
                $result = 0;
                break;
            case self::SHIPPING_CUSTOM_VALUE:
                $result = $this->getData('cost_value');
                break;
            case self::SHIPPING_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getData('cost_value'));
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return $result;
    }

    public function getCostAdditional()
    {
        $result = 0;

        switch ($this->getCostMode()) {
            case self::SHIPPING_FREE:
                $result = 0;
                break;
            case self::SHIPPING_CUSTOM_VALUE:
                $result = $this->getData('cost_additional_items');
                break;
            case self::SHIPPING_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue($this->getData('cost_additional_items'));
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return $result;
    }

    // ########################################
}