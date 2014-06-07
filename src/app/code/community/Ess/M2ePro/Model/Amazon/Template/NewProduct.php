<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_NewProduct extends Ess_M2ePro_Model_Component_Abstract
{
    const WORLDWIDE_ID_MODE_NONE = 0;
    const WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const ITEM_PACKAGE_QUANTITY_MODE_NONE = 0;
    const ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_VALUE = 1;
    const ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE = 2;

    const NUMBER_OF_ITEMS_MODE_NONE = 0;
    const NUMBER_OF_ITEMS_MODE_CUSTOM_VALUE = 1;
    const NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_NewProduct');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::helper('M2ePro/Component_Amazon')
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

        $listingProductTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        $writeConnection->update(
            $listingProductTable,
            array('template_new_product_id' => null),
            array('template_new_product_id = ?' => $this->getId())
        );

        $this->deleteSpecifics();
        $this->getDescription()->deleteInstance();

        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            $attributeSet->deleteInstance();
        }

        $this->delete();

        return true;
    }

    // ########################################

    public function getAttributeSets()
    {
        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter(
            'object_type', Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_AMAZON_TEMPLATE_NEW_PRODUCT
        );
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

    public function getSpecifics()
    {
        return Mage::getModel('M2ePro/Amazon_Template_NewProduct_Specific')
            ->getCollection()
            ->addFieldToFilter('template_new_product_id',$this->getId())
            ->setOrder('id', Varien_Data_Collection::SORT_ORDER_ASC)
            ->getData();
    }

    public function deleteSpecifics()
    {
        $specifics = $this->getRelatedSimpleItems(
            'Amazon_Template_NewProduct_Specific', 'template_new_product_id', true
        );
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_NewProduct_Description
     */
    public function getDescription()
    {
        return Mage::getModel('M2ePro/Amazon_Template_NewProduct_Description')->loadInstance($this->getId());
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Template_NewProduct_Source
    */
    public function getSource(Ess_M2ePro_Model_Amazon_Listing_Product $listingProduct)
    {
        return Mage::getModel(
            'M2ePro/Amazon_Template_NewProduct_Source',
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
        $listingAttributes = $this->getNewProductAttributes($listingProductIds);

        foreach ($listingAttributes as $listingAttributeId) {
            if (array_search($listingAttributeId, $categoryAttributes) === false) {
                return false;
            }
        }

        $hasFailed = false;
        foreach ($listingProductIds as $listingProductId) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product',$listingProductId);

            $generalId = $listingProductInstance->getChildObject()->getData('general_id');
            $generalIdSearchStatus = $listingProductInstance->getChildObject()->getData('general_id_search_status');

            if (!is_null($generalId) ||
                $generalIdSearchStatus == Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING){
                $hasFailed = true;
                continue;
            }

            $listingProductInstance->getChildObject()->setData('template_new_product_id',$this->getId())->save();
        }

        return !$hasFailed;
    }

    private function getNewProductAttributes($listingProductIds)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_listing_product');

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($connRead->select()
                ->from($tableListingProduct, 'product_id')
                ->where('id in (?)', $listingProductIds));

        return Mage::helper('M2ePro/Magento_AttributeSet')
            ->getFromProducts($productsIds, Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS);
    }

    // ########################################

    public function getWorldwideIdMode()
    {
        return (int)$this->getData('worldwide_id_mode');
    }

    public function isWorldwideIdModeNone()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_NONE;
    }

    public function isWorldwideIdModeCustomAttribute()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getWorldwideIdSource()
    {
        return array(
            'mode'      => $this->getWorldwideIdMode(),
            'attribute' => $this->getData('worldwide_id_custom_attribute')
        );
    }

    // ---------------------------------------

    public function getItemPackageQuantityMode()
    {
        return (int)$this->getData('item_package_quantity_mode');
    }

    public function getItemPackageQuantityCustomValue()
    {
        return $this->getData('item_package_quantity_custom_value');
    }

    public function getItemPackageQuantityCustomAttribute()
    {
        return $this->getData('item_package_quantity_custom_attribute');
    }

    public function isItemPackageQuantityModeNone()
    {
        return $this->getItemPackageQuantityMode() == self::ITEM_PACKAGE_QUANTITY_MODE_NONE;
    }

    public function isItemPackageQuantityModeCustomValue()
    {
        return $this->getItemPackageQuantityMode() == self::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_VALUE;
    }

    public function isItemPackageQuantityModeCustomAttribute()
    {
        return $this->getItemPackageQuantityMode() == self::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getItemPackageQuantitySource()
    {
        return array(
            'mode'      => $this->getItemPackageQuantityMode(),
            'value'     => $this->getItemPackageQuantityCustomValue(),
            'attribute' => $this->getItemPackageQuantityCustomAttribute()
        );
    }

    // ---------------------------------------

    public function getNumberOfItemsMode()
    {
        return (int)$this->getData('number_of_items_mode');
    }

    public function getNumberOfItemsCustomValue()
    {
        return $this->getData('number_of_items_custom_value');
    }

    public function getNumberOfItemsCustomAttribute()
    {
        return $this->getData('number_of_items_custom_attribute');
    }

    public function isNumberOfItemsModeNone()
    {
        return $this->getNumberOfItemsMode() == self::NUMBER_OF_ITEMS_MODE_NONE;
    }

    public function isNumberOfItemsModeCustomValue()
    {
        return $this->getNumberOfItemsMode() == self::NUMBER_OF_ITEMS_MODE_CUSTOM_VALUE;
    }

    public function isNumberOfItemsModeCustomAttribute()
    {
        return $this->getNumberOfItemsMode() == self::NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getNumberOfItemsSource()
    {
        return array(
            'mode'      => $this->getNumberOfItemsMode(),
            'value'     => $this->getNumberOfItemsCustomValue(),
            'attribute' => $this->getNumberOfItemsCustomAttribute()
        );
    }

    // ---------------------------------------

    public function getCategoryPath()
    {
        return $this->getData('category_path');
    }

    // ---------------------------------------

    public function getNodeTitle()
    {
        return $this->getData('node_title');
    }

    // ---------------------------------------

    public function getCategoryIdentifiers()
    {
        $return = json_decode($this->getData('identifiers'),true);
        is_null($return) && $return = array('item_types' => null,'browsenode_id' => null);

        return $return;
    }

    // ---------------------------------------

    public function getRegisteredParameter()
    {
        return $this->getData('registered_parameter');
    }

    // ########################################
}