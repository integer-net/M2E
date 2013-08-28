<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_General_Specific extends Ess_M2ePro_Model_Component_Abstract
{
    const MODE_ITEM_SPECIFICS = 1;
    const MODE_ATTRIBUTE_SET = 2;
    const MODE_CUSTOM_ITEM_SPECIFICS = 3;

    const VALUE_MODE_NONE = 0;
    const VALUE_MODE_EBAY_RECOMMENDED = 1;
    const VALUE_MODE_CUSTOM_VALUE = 2;
    const VALUE_MODE_CUSTOM_ATTRIBUTE = 3;
    const VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE = 4;

    const RENDER_TYPE_TEXT = 'text';
    const RENDER_TYPE_SELECT_ONE = 'select_one';
    const RENDER_TYPE_SELECT_MULTIPLE = 'select_multiple';
    const RENDER_TYPE_SELECT_ONE_OR_TEXT = 'select_one_or_text';
    const RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT = 'select_multiple_or_text';

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
        $this->_init('M2ePro/Ebay_Template_General_Specific');
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

    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    public function isItemSpecificsMode()
    {
        return $this->getMode() == self::MODE_ITEM_SPECIFICS;
    }

    public function isAttributeSetMode()
    {
        return $this->getMode() == self::MODE_ATTRIBUTE_SET;
    }

    public function isCustomItemSpecificsMode()
    {
        return $this->getMode() == self::MODE_CUSTOM_ITEM_SPECIFICS;
    }

    //-------------------------

    public function getValueMode()
    {
        return (int)$this->getData('value_mode');
    }

    public function isNoneValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_NONE;
    }

    public function isEbayRecommendedValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_EBAY_RECOMMENDED;
    }

    public function isCustomValueValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_VALUE;
    }

    public function isCustomAttributeValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function isCustomLabelAttributeValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
    }

    //-------------------------

    public function getModeRelationId()
    {
        return (int)$this->getData('mode_relation_id');
    }

    public function getAttributeData()
    {
        $labelTemp = $this->getData('attribute_title');

        if ($this->isCustomAttributeValueMode()) {
            $labelTemp = $this->getMagentoProduct()->getAttributeLabel($this->getData('value_custom_attribute'));
        }

        return array(
            'id' => $this->getData('attribute_id'),
            'title' => $labelTemp
        );
    }

    //-------------------------

    public function getValues()
    {
        $valueData = array();

        if ($this->isNoneValueMode()) {
            $valueData[] = array('id'=>'unknown','value'=>'--');
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -10;
        }

        if ($this->isEbayRecommendedValueMode()) {
            $valueData = json_decode($this->getData('value_ebay_recommended'),true);
        }

        if ($this->isCustomValueValueMode()) {
            $valueData[] = array('id'=>'unknown','value'=>$this->getData('value_custom_value'));
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -6;
        }

        if ($this->isCustomAttributeValueMode()) {
            $valueTemp = $this->getAttributeValue($this->getData('value_custom_attribute'));
            $valueData[] = array('id'=>'unknown','value'=>$valueTemp);
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -6;
        }

        if ($this->isCustomLabelAttributeValueMode()) {
            $valueTemp = $this->getAttributeValue($this->getData('value_custom_attribute'));
            $valueData[] = array('id'=>'unknown','value'=>$valueTemp);
            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -6;
        }

        return $valueData;
    }

    // ########################################

    private function getAttributeValue($attributeCode)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

        if ($attributeCode == 'country_of_manufacture') {
            $locale = Mage::getStoreConfig(
                Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $this->getMagentoProduct()->getStoreId()
            );

            if ($countryName = Mage::helper('M2ePro/Magento')->getTranslatedCountryName($attributeValue, $locale)) {
                $attributeValue = $countryName;
            }
        }

        return $attributeValue;
    }

    // ########################################
}