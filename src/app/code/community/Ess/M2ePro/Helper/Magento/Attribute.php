<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento_Attribute extends Ess_M2ePro_Helper_Magento_Abstract
{
    // ################################

    public function getAll($returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $attributes = $this->_convertCollectionToReturnType($attributeCollection, $returnType);
        if ($returnType != self::RETURN_TYPE_ARRAYS) {
            return $attributes;
        }

        $resultAttributes = array();
        foreach ($attributes as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label']
            );
        }

        return $resultAttributes;
    }

    // --------------------------------

    public function getByAttributeSet($attributeSet, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeSetId = $this->_getIdFromInput($attributeSet);
        if ($attributeSetId === false) {
            return array();
        }

        return $this->getByAttributeSets(array($attributeSetId), $returnType);
    }

    public function getByAttributeSets(array $attributeSets, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeSetIds = $this->_getIdsFromInput($attributeSets, 'attribute_set_id');
        if (empty($attributeSetIds)) {
            return array();
        }

        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setAttributeSetFilter($attributeSetIds)
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $attributeCollection->getSelect()->group('entity_attribute.attribute_id');

        $attributes = $this->_convertCollectionToReturnType($attributeCollection, $returnType);
        if ($returnType != self::RETURN_TYPE_ARRAYS) {
            return $attributes;
        }

        $resultAttributes = array();
        foreach ($attributes as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label']
            );
        }

        return $resultAttributes;
    }

    // ################################

    public function getGeneralFromAttributeSets(array $attributeSets)
    {
        $attributeSetIds = $this->_getIdsFromInput($attributeSets, 'attribute_set_id');
        if (empty($attributeSetIds)) {
            return array();
        }

        $attributes = array();
        $isFirst = true;
        $idsParts = array_chunk($attributeSetIds, 50);
        foreach ($idsParts as $part) {
            $tempAttributes = $this->_getGeneralFromAttributeSets($part);

            if ($isFirst) {
                $attributes = $tempAttributes;
                $isFirst = false;

                continue;
            }

            if (!$isFirst && empty($attributes)) {
                return array();
            }

            $attributes = array_intersect($attributes, $tempAttributes);
        }

        if (empty($attributes)) {
            return array();
        }

        $attributesData = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addFieldToFilter('main_table.attribute_id', array('in' => $attributes))
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC)
            ->toArray();

        $resultAttributes = array();
        foreach ($attributesData['items'] as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label'],
            );
        }

        return $resultAttributes;
    }

    public function getGeneralFromAllAttributeSets()
    {
        $allAttributeSets = Mage::helper('M2ePro/Magento_AttributeSet')->getAll(self::RETURN_TYPE_IDS);
        return $this->getGeneralFromAttributeSets($allAttributeSets);
    }

    // -------------------------------------------

    private function _getGeneralFromAttributeSets(array $attributeSetIds)
    {
        if (count($attributeSetIds) > 50) {
            throw new Exception("Attribute sets must be less then 50");
        }

        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setInAllAttributeSetsFilter($attributeSetIds)
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        return $attributeCollection->getAllIds();
    }

    // --------------------------------

    public function getGeneralFromProducts(array $products, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $productsAttributeSetIds = Mage::helper('M2ePro/Magento_AttributeSet')->getFromProducts(
            $products, self::RETURN_TYPE_IDS
        );

        return $this->getGeneralFromAttributeSets($productsAttributeSetIds, $returnType);
    }

    // ################################

    public function getConfigurableByAttributeSets(array $attributeSets)
    {
        $attributes = NULL;

        foreach ($attributeSets as $attributeSetId) {

            $attributesTemp = $this->getConfigurable($attributeSetId);

            if (is_null($attributes)) {
                $attributes = $attributesTemp;
                continue;
            }

            $intersectAttributes = array();
            foreach ($attributesTemp as $attributeTemp) {
                $findValue = false;
                foreach ($attributes as $attribute) {
                    if ($attributeTemp['code'] == $attribute['code'] &&
                        $attributeTemp['label'] == $attribute['label']) {
                        $findValue = true;
                        break;
                    }
                }
                if ($findValue) {
                    $intersectAttributes[] = $attributeTemp;
                }
            }

            $attributes = $intersectAttributes;
        }

        if (is_null($attributes)) {
            return array();
        }

        return $attributes;
    }

    public function getAllConfigurable()
    {
        return $this->getConfigurable();
    }

    // -------------------------------------------

    private function getConfigurable($attributeSetId = NULL)
    {
        $product = Mage::getModel('catalog/product');
        $product->setTypeId('configurable');
        $product->setData('_edit_mode', true);

        if (!is_null($attributeSetId)) {
            $product->setAttributeSetId((int)$attributeSetId);
        }

        $typeInstance = $product->getTypeInstance()->setStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID);
        $attributes = $typeInstance->getSetAttributes($product);

        $result = array();
        foreach ($attributes as $attribute) {
            if ($typeInstance->canUseAttribute($attribute, $product)) {
                $result[] = array(
                    'code' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontend()->getLabel()
                );
            }
        }

        return $result;
    }

    // ################################

    public function getAttributeLabel($attributeCode, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            return $attributeCode;
        }

        $label = $attribute->getStoreLabel($storeId);
        $label == '' && $label = $attribute->getFrontendLabel();

        return $label == '' ? $attributeCode : $label;
    }

    public function getAttributesLabels(array $attributeCodes)
    {
        if (empty($attributeCodes)) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('eav_attribute');

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId();
        $dbSelect = $connRead->select();
        $dbSelect->from($tableName)
            ->where('attribute_code in (\''.implode('\',\'', $attributeCodes).'\')')
            ->where('entity_type_id = ?', $entityTypeId);
        $fetchResult = $connRead->fetchAll($dbSelect);

        $result = array();
        foreach ($fetchResult as $attribute) {
            $result[] = array(
                'label' => $attribute['frontend_label'],
                'code'  => $attribute['attribute_code']
            );
        }

        return $result;
    }

    public function isExistInAttributesArray($attributeCode, array $attributes)
    {
        if ($attributeCode == '') {
            return false;
        }

        foreach ($attributes as $attribute) {
            if ($attribute['code'] == $attributeCode) {
                return true;
            }
        }
        return false;
    }

    // ################################

    public function getSetsFromProductsWhichLacksAttributes(array $attributes, array $productIds)
    {
        if (count($attributes) == 0 || count($productIds) == 0) {
            return array();
        }

        //------------------------------
        $scopeAttributesOptionArray = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromProducts($productIds);
        $scopeAttributes = array();
        foreach ($scopeAttributesOptionArray as $scopeAttributesOption) {
            $scopeAttributes[] = $scopeAttributesOption['code'];
        }
        //------------------------------

        $missingAttributes = array_diff($attributes, $scopeAttributes);

        if (count($missingAttributes) == 0) {
            return array();
        }

        //------------------------------
        $attributesCollection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->addFieldToFilter('attribute_code', array('in' => $missingAttributes))
            ->addSetInfo(true);
        //------------------------------

        //------------------------------
        $attributeSets = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getFromProducts(
                $productIds,
                Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS
            );
        //------------------------------

        $missingAttributesSets = array();

        foreach ($attributesCollection->getItems() as $attribute) {
            foreach ($attributeSets as $setId) {
                if (!$attribute->isInSet($setId)) {
                    $missingAttributesSets[] = $setId;
                }
            }
        }

        return array_unique($missingAttributesSets);
    }

    // ################################
}