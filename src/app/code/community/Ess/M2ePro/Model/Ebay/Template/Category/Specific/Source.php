<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Specific_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $categorySpecificTemplateModel Ess_M2ePro_Model_Ebay_Template_Category_Specific
     */
    private $categorySpecificTemplateModel = null;

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setCategorySpecificTemplate(Ess_M2ePro_Model_Ebay_Template_Category_Specific $instance)
    {
        $this->categorySpecificTemplateModel = $instance;
        return $this;
    }

    public function getCategorySpecificTemplate()
    {
        return $this->categorySpecificTemplateModel;
    }

    // ----------------------------------------

    public function getCategoryTemplate()
    {
        return $this->getCategorySpecificTemplate()->getCategoryTemplate();
    }

    // ########################################

    public  function getLabel()
    {
        if ($this->getCategorySpecificTemplate()->isCustomItemSpecificsMode() &&
            $this->getCategorySpecificTemplate()->isCustomAttributeValueMode()) {
            return $this->getAttributeLabel();
        }

        return $this->getCategorySpecificTemplate()->getData('attribute_title');
    }

    public function getValues()
    {
        $valueData = array();

        if ($this->getCategorySpecificTemplate()->isNoneValueMode()) {
            $valueData[] = '--';
        }

        if ($this->getCategorySpecificTemplate()->isEbayRecommendedValueMode()) {
            $valueData = json_decode($this->getCategorySpecificTemplate()->getData('value_ebay_recommended'),true);
        }

        if ($this->getCategorySpecificTemplate()->isCustomValueValueMode()) {
            $valueData = json_decode($this->getCategorySpecificTemplate()->getData('value_custom_value'),true);
        }

        if ($this->getCategorySpecificTemplate()->isCustomAttributeValueMode() ||
            $this->getCategorySpecificTemplate()->isCustomLabelAttributeValueMode()) {

            $attributeCode = $this->getCategorySpecificTemplate()->getData('value_custom_attribute');
            $valueTemp = $this->getAttributeValue($attributeCode);

            $categoryId = $this->getCategoryTemplate()->getCategoryMainId();
            $marketplaceId = $this->getCategoryTemplate()->getMarketplaceId();

            if(!empty($categoryId) && !empty($marketplaceId) && strpos($valueTemp, ',') &&
                $this->getMagentoProduct()->getAttributeFrontendInput($attributeCode) === 'multiselect') {

                $specifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                    ->getSpecifics($categoryId, $marketplaceId);

                $usedAsMultiple = false;
                foreach($specifics as $specific) {

                    if ($specific['title'] === $this->getCategorySpecificTemplate()->getData('attribute_title') &&
                        in_array($specific['type'],array('select_multiple_or_text','select_multiple'))) {

                        $valuesTemp = explode(',', $valueTemp);

                        foreach($valuesTemp as $val) {
                            $valueData[] =  trim($val);
                        }

                        $usedAsMultiple = true;
                        break;
                    }
                }

                if (!$usedAsMultiple) {
                    $valueData[] = $valueTemp;
                }

            } else {
                $valueData[] = $valueTemp;
            }
        }

        return $valueData;
    }

    // ########################################

    private function getAttributeLabel()
    {
        return Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $this->getCategorySpecificTemplate()->getData('value_custom_attribute'),
                    $this->getMagentoProduct()->getStoreId()
                );
    }

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