<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct extends Ess_M2ePro_Model_Component_Abstract
{
    private $coreTemplateModel = NULL;
    private $attributesTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_NewProduct');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::helper('M2ePro/Component_Buy')
                ->getCollection('Listing_Product')
                ->addFieldToFilter('template_new_product_id', $this->getId())
                ->addFieldToFilter('status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
                ->getSize();
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        /* @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingProductTable = Mage::getSingleton('core/resource')->getTableName('m2epro_buy_listing_product');

        $writeConnection->update(
                                $listingProductTable,
                                array('template_new_product_id' => null),
                                array('template_new_product_id = ?' => $this->getId())
                                );

        // -- delete attributes
        $attributes = $this->getAttributesTemplate();
        foreach ($attributes as $attribute) {
            $attribute->deleteInstance();
        }

        $this->getCoreTemplate()->deleteInstance();

        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            $attributeSet->deleteInstance();
        }

        $this->delete();

        $this->coreTemplateModel = NULL;
        $this->attributesTemplateModel = NULL;

        return true;
    }

    // ########################################

    public function getAttributeSets()
    {
        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter('object_type',
                                      Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_BUY_TEMPLATE_NEW_PRODUCT);
        $collection->addFieldToFilter('object_id',(int)$this->getId());

        return $collection->getItems();
    }

    public function getAttributeSetsIds()
    {
        $ids = array();
        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            /** @var $attributeSet Ess_M2ePro_Model_AttributeSet */
            $ids[] = $attributeSet->getAttributeSetId();
        }

        return $ids;
    }

    // ########################################

    public function getCoreTemplate()
    {
        if (is_null($this->coreTemplateModel)) {
            $this->coreTemplateModel = Mage::getModel('M2ePro/Buy_Template_NewProduct_Core')->loadInstance(
                $this->getId()
            );
        }

        return $this->coreTemplateModel;
    }

    public function getAttributesTemplate()
    {
        if (is_null($this->attributesTemplateModel)) {
            $this->attributesTemplateModel = Mage::getModel('M2ePro/Buy_Template_NewProduct_Attribute')
                ->getCollection()
                ->addFieldToFilter('template_new_product_id',$this->getId())
                ->getItems();
        }

        return $this->attributesTemplateModel;
    }

    // ########################################

    public function getCategoryPath()
    {
        return $this->getData('category_path');
    }

    public function getNodeTitle()
    {
        return $this->getData('node_title');
    }

    public function getCategoryId()
    {
        return $this->getData('category_id');
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Buy_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct_Source
     */
    public function getSource(Ess_M2ePro_Model_Buy_Listing_Product $listingProduct)
    {
        return Mage::getModel(
            'M2ePro/Buy_Template_NewProduct_Source',
            array(
                $listingProduct,
                $this
            )
        );
    }

    // ########################################

    public function map($listingProductIds)
    {
        if (count($listingProductIds) < 0) {
            return false;
        }

        $categoryAttributes = $this->getAttributeSetsIds();

        $listingAttributes = Mage::helper('M2ePro/Component_Buy')
                ->getObject('Listing_Product',reset($listingProductIds))
                ->getListing()
                ->getAttributeSetsIds();

        foreach ($listingAttributes as $listingAttribute) {
            if (array_search($listingAttribute,$categoryAttributes) === false) {
                return false;
            }
        }

        foreach ($listingProductIds as $listingProductId) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Buy')
                    ->getObject('Listing_Product',$listingProductId);

            $generalId = $listingProductInstance->getChildObject()->getData('general_id');

            if (!is_null($generalId)){
                continue;
            }

            $listingProductInstance->getChildObject()->setData('template_new_product_id',$this->getId())->save();
        }

        return true;
    }

    // ########################################
}