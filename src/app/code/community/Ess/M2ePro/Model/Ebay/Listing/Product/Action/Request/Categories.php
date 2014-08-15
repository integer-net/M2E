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

        if ($this->getCompatibilityHelper()->isMarketplaceSupportsSpecific($this->getMarketplace()->getId())) {
            $tempData = $this->getPartsCompatibilityData(
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC
            );
            $tempData !== false && $data['motors_specifics'] = $tempData;
        }

        if ($this->getCompatibilityHelper()->isMarketplaceSupportsKtype($this->getMarketplace()->getId())) {
            $tempData = $this->getPartsCompatibilityData(
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE
            );
            $tempData !== false && $data['motors_ktypes'] = $tempData;
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

    // ----------------------------------------

    public function getPartsCompatibilityData($type)
    {
        if (!$this->isSetCompatibilityAttribute($type)) {
            return false;
        }

        $this->searchNotFoundAttributes();

        $rawData = $this->getRawCompatibilityData($type);

        if (!$this->processNotFoundAttributes('Compatibility')) {
            return false;
        }

        if ($type == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC) {
            return $this->getPreparedMotorPartsCompatibilitySpecificData($rawData);
        }

        if ($type == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE) {
            return $this->getPreparedMotorPartsCompatibilityKtypeData($rawData);
        }

        return NULL;
    }

    // ########################################

    private function getPreparedMotorPartsCompatibilitySpecificData($data)
    {
        $ebayAttributes = $this->getEbayMotorsSpecificsAttributes();

        $preparedData = array();
        $emptySavedEpids = array();

        foreach ($data as $epid => $epidData) {
            if (empty($epidData['info'])) {
                $emptySavedEpids[] = $epid;
                continue;
            }

            $compatibilityList = array();
            $compatibilityData = $this->buildSpecificsCompatibilityData($epidData['info']);

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

            $preparedData[] = array(
                'list' => $compatibilityList,
                'note' => $epidData['note'],
            );
        }

        if(count($emptySavedEpids) > 0) {
            $isSingleEpid = count($emptySavedEpids) > 1;
            $msg = 'The '.implode(', ', $emptySavedEpids).' ePID'.($isSingleEpid ? 's' : '');
            $msg .= ' specified in the Compatibility Attribute';
            $msg .= ' were dropped out of the listing because '.($isSingleEpid ? 'it was' : 'they were');
            $msg .= ' deleted from eBay Catalog of Compatible Vehicles.';
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    private function getPreparedMotorPartsCompatibilityKtypeData($data)
    {
        $preparedData = array();
        $emptySavedKtypes = array();

        foreach ($data as $ktype => $ktypeData) {
            if (empty($ktypeData['info'])) {
                $emptySavedKtypes[] = $ktype;
                continue;
            }

            $preparedData[] = array(
                'ktype' => $ktype,
                'note' => $ktypeData['note'],
            );
        }

        if(count($emptySavedKtypes) > 0) {
            $isSingleKtype = count($emptySavedKtypes) > 1;
            $msg = 'The '.implode(', ', $emptySavedKtypes).' KType'.($isSingleKtype ? 's' : '');
            $msg .= ' specified in the Compatibility Attribute';
            $msg .= ' were dropped out of the listing because '.($isSingleKtype ? 'it was' : 'they were');
            $msg .= ' deleted from eBay Catalog of Compatible Vehicles.';
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    // ----------------------------------------

    private function isSetCompatibilityAttribute($type)
    {
        $attributeCode  = $this->getCompatibilityAttribute($type);
        return !empty($attributeCode);
    }

    private function getRawCompatibilityData($type)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($this->getCompatibilityAttribute($type));
        if (empty($attributeValue)) {
            return array();
        }

        $compatibilityData = $this->getCompatibilityHelper()->parseAttributeValue($attributeValue);

        $typeIdentifier = $this->getCompatibilityHelper()->getIdentifierKey($type);

        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($this->getCompatibilityHelper()->getDictionaryTable($type))
            ->where(
                '`'.$typeIdentifier.'` IN (?)',
                array_keys($compatibilityData)
            );

        foreach ($select->query()->fetchAll() as $attributeRow) {
            $compatibilityData[$attributeRow[$typeIdentifier]]['info'] = $attributeRow;
        }

        return $compatibilityData;
    }

    // ----------------------------------------

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

    private function buildSpecificsCompatibilityData($resource)
    {
        $compatibilityData = array(
            'Make'  => $resource['make'],
            'Model' => $resource['model'],
            'Year'  => $resource['year'],
            'Submodel' => $resource['submodel'],
        );

        if ($resource['product_type'] == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_VEHICLE) {
            $compatibilityData['Trim'] = $resource['trim'];
            $compatibilityData['Engine'] = $resource['engine'];
        }

        return $compatibilityData;
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

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility
     */
    private function getCompatibilityHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility');
    }

    private function getCompatibilityAttribute($type)
    {
        return $this->getCompatibilityHelper()->getAttribute($type);
    }

    // ########################################
}