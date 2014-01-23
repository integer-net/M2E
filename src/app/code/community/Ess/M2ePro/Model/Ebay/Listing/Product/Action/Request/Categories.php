<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Categories
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $otherCategoryTemplate = NULL;

    // ########################################

    public function getData()
    {
        $data = $this->getCategoriesData();

        $data['item_specifics'] = array_merge(
            $this->getEbayItemSpecificsData(),
            $this->getCustomItemSpecificsData()
        );

        $data['attribute_set'] = $this->getAttributeSetData();

        if ($this->getMarketplace()->getId() == Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            $tempData = $this->getMotorsSpecificsData();
            $tempData !== false && $data['motors_specifics'] = $tempData;
        }

        return $data;
    }

    // ########################################

    public function getCategoriesData()
    {
        $data = array(
            'category_main_id' => $this->getCategoryTemplate()->getMainCategory(),
            'category_secondary_id' => 0,
            'store_category_main_id' => 0,
            'store_category_secondary_id' => 0
        );

        if (!is_null($this->getOtherCategoryTemplate())) {
            $data['category_secondary_id'] = $this->getOtherCategoryTemplate()->getSecondaryCategory();
            $data['store_category_main_id'] = $this->getOtherCategoryTemplate()->getStoreCategoryMain();
            $data['store_category_secondary_id'] = $this->getOtherCategoryTemplate()->getStoreCategorySecondary();
        }

        return $data;
    }

    // ----------------------------------------

    public function getEbayItemSpecificsData()
    {
        $data = array();

        $filter = array('mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS);
        $specifics = $this->getCategoryTemplate()->getSpecifics(true, $filter);

        foreach ($specifics as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $this->searchNotFoundAttributes();

            $tempAttributeData = $specific->getAttributeData();
            $tempAttributeValues = $specific->getValues();

            if (!$this->processNotFoundAttributes('Specifics')) {
                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $data[] = array(
                'name' => $tempAttributeData['id'],
                'value' => $values
            );
        }

        return $data;
    }

    public function getCustomItemSpecificsData()
    {
        $data = array();

        $filter = array('mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS);
        $specifics = $this->getCategoryTemplate()->getSpecifics(true, $filter);

        foreach ($specifics as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $this->searchNotFoundAttributes();

            $tempAttributeData = $specific->getAttributeData();
            $tempAttributeValues = $specific->getValues();

            if (!$this->processNotFoundAttributes('Specifics')) {
                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $data[] = array(
                'name' => $tempAttributeData['title'],
                'value' => $values
            );
        }

        return $data;
    }

    // ########################################

    public function getAttributeSetData()
    {
        $data = array(
            'attribute_set_id' => 0,
            'attributes' => array()
        );

        $filters = array('mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ATTRIBUTE_SET);
        $specifics = $this->getCategoryTemplate()->getSpecifics(true, $filters);

        foreach ($specifics as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $this->searchNotFoundAttributes();

            $tempAttributeData = $specific->getAttributeData();
            $tempAttributeValues = $specific->getValues();

            if (!$this->processNotFoundAttributes('Specifics')) {
                continue;
            }

            $data['attribute_set_id'] = $specific->getModeRelationId();

            $data['attributes'][] = array(
                'id' => $tempAttributeData['id'],
                'value' => $tempAttributeValues
            );
        }

        return $data;
    }

    public function getMotorsSpecificsData()
    {
        if (!$this->isSetMotorsSpecificsAttribute()) {
            return false;
        }

        $ebayAttributes = $this->getEbayMotorsSpecificsAttributes();

        if (empty($ebayAttributes)) {
            return false;
        }

        $this->searchNotFoundAttributes();

        $savedAttributes = $this->getSavedMotorsSpecificsAttributes();

        if (!$this->processNotFoundAttributes('Compatibility')) {
            return false;
        }

        $data = array();

        foreach ($savedAttributes as $savedAttribute) {

            /** @var Ess_M2ePro_Model_Ebay_Motor_Specific $savedAttribute */

            $compatibilityList = array();
            $compatibilityData = $savedAttribute->getCompatibilityData();

            foreach ($compatibilityData as $key => $value) {

                if ($value == '--') {
                    unset($compatibilityData[$key]);
                    continue;
                }

                $name = $key;

                foreach ($ebayAttributes as $ebayAttribute) {
                    if ($ebayAttribute['title'] == $key) {
                        $name = $ebayAttribute['ebay_id'];
                        break;
                    }
                }

                $compatibilityList[] = array(
                    'name'  => $name,
                    'value' => $value
                );
            }

            $data[] = $compatibilityList;
        }

        return $data;
    }

    // ########################################

    private function isSetMotorsSpecificsAttribute()
    {
        $attributeCode  = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/motor/', 'motors_specifics_attribute'
        );

        return !empty($attributeCode);
    }

    private function getEbayMotorsSpecificsAttributes()
    {
        $categoryId = $this->getCategoryTemplate()->getMainCategory();
        $categoryData = $this->getEbayMarketplace()->getCategory($categoryId);

        $features = !empty($categoryData['features']) ?
                    (array)json_decode($categoryData['features'], true) : array();

        $attributes = !empty($features['parts_compatibility_attributes']) ?
                      $features['parts_compatibility_attributes'] : array();

        return $attributes;
    }

    private function getSavedMotorsSpecificsAttributes()
    {
        $attributeCode  = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/motor/', 'motors_specifics_attribute'
        );

        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

        if (empty($attributeValue)) {
            return array();
        }

        $epids = explode(',', $attributeValue);

        return Mage::getModel('M2ePro/Ebay_Motor_Specific')
                        ->getCollection()
                        ->addFieldToFilter('epid', array('in' => $epids))
                        ->getItems();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    private function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplate)) {
            $this->categoryTemplate = $this->getListingProduct()
                                           ->getChildObject()
                                           ->getCategoryTemplate();
        }
        return $this->categoryTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private function getOtherCategoryTemplate()
    {
        if (is_null($this->otherCategoryTemplate)) {
            $this->otherCategoryTemplate = $this->getListingProduct()
                                                ->getChildObject()
                                                ->getOtherCategoryTemplate();
        }
        return $this->otherCategoryTemplate;
    }

    // ########################################
}