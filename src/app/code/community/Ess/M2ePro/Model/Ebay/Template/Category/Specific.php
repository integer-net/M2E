<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Specific extends Ess_M2ePro_Model_Component_Abstract
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
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category_Specific');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->categoryTemplateModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplateModel)) {

            $this->categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Category', $this->getTemplateCategoryId(), NULL, array('template')
            );

            if (!is_null($this->getMagentoProduct())) {
                $this->categoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->categoryTemplateModel = $instance;
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

    public function getTemplateCategoryId()
    {
        return (int)$this->getData('template_category_id');
    }

    public function getModeRelationId()
    {
        return (int)$this->getData('mode_relation_id');
    }

    // #######################################

    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    //----------------------------------------

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

    // #######################################

    public function getValueMode()
    {
        return (int)$this->getData('value_mode');
    }

    //----------------------------------------

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

    // #######################################

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

        if ($this->isCustomAttributeValueMode() || $this->isCustomLabelAttributeValueMode()) {

            $attributeCode = $this->getData('value_custom_attribute');
            $valueTemp = $this->getAttributeValue($attributeCode);

            $categoryId = $this->getCategoryTemplate()->getCategoryMainId();
            $marketplaceId = $this->getCategoryTemplate()->getMarketplaceId();

            if(!empty($categoryId) && !empty($marketplaceId) && strpos($valueTemp, ',') &&
                $this->getMagentoProduct()->getAttributeFrontendInput($attributeCode) === 'multiselect') {

                $specifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                    ->getSpecifics($categoryId, $marketplaceId);

                $usedAsMultiple = false;
                foreach($specifics['specifics'] as $specific) {

                    if($specific['id'] === $this->getAttributeId() &&
                       in_array($specific['type'],array('select_multiple_or_text','select_multiple'))) {

                        $valuesTemp = explode(',', $valueTemp);

                        foreach($valuesTemp as $val) {
                            $valueData[] =  array('id'=>'unknown','value'=>trim($val));
                        }

                        $usedAsMultiple = true;
                        break;
                    }
                }

                if (!$usedAsMultiple) {
                    $valueData[] = array('id'=>'unknown','value'=>$valueTemp);
                }

            } else {
                $valueData[] = array('id'=>'unknown','value'=>$valueTemp);
            }

            $this->isAttributeSetMode() && $valueData[count($valueData)-1]['id'] = -6;
        }

        return $valueData;
    }

    public function getAttributeData()
    {
        $labelTemp = $this->getData('attribute_title');

        if ($this->isCustomAttributeValueMode()) {
            $labelTemp = Mage::helper('M2ePro/Magento_Attribute')
                ->getAttributeLabel($this->getData('value_custom_attribute'),
                                    $this->getMagentoProduct()->getStoreId());
        }

        return array(
            'id' => $this->getData('attribute_id'),
            'title' => $labelTemp
        );
    }

    // #######################################

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