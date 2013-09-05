<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Category extends Ess_M2ePro_Model_Component_Abstract
{
    const CATEGORY_MODE_NONE       = 0;
    const CATEGORY_MODE_EBAY       = 1;
    const CATEGORY_MODE_ATTRIBUTE  = 2;

    const STORE_CATEGORY_MODE_NONE       = 0;
    const STORE_CATEGORY_MODE_EBAY       = 1;
    const STORE_CATEGORY_MODE_ATTRIBUTE  = 2;

    const TAX_CATEGORY_MODE_NONE      = 0;
    const TAX_CATEGORY_MODE_VALUE     = 1;
    const TAX_CATEGORY_MODE_ATTRIBUTE = 2;

    const MOTORS_SPECIFICS_VALUE_SEPARATOR = ',';

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }

        $this->magentoProductModel = NULL;

        $this->delete();
        return true;
    }

    public function duplicateInstance()
    {
        $tempData = $this->getData();
        unset($tempData['id']);

        /** @var Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplateDestination */
        $categoryTemplateNew = Mage::getModel('M2ePro/Ebay_Template_Category')
                                            ->setData($tempData)->save();

        $categorySpecifics = $this->getSpecifics(true);
        foreach ($categorySpecifics as $categorySpecific) {

            /** @var Ess_M2ePro_Model_Ebay_Template_Category_Specific $categorySpecific */

            $tempData = $categorySpecific->getData();
            unset($tempData['id']);
            $tempData['template_category_id'] = $categoryTemplateNew->getId();

            Mage::getModel('M2ePro/Ebay_Template_Category_Specific')->setData($tempData)->save();
        }

        return $categoryTemplateNew;
    }

    // #######################################

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

    public function getSpecifics($asObjects = false, array $filters = array())
    {
        $specifics = $this->getRelatedSimpleItems('Ebay_Template_Category_Specific','template_category_id',
                                                  $asObjects, $filters);

        if ($asObjects) {
            foreach ($specifics as $specific) {
                /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */
                if (!is_null($this->getMagentoProduct())) {
                    $specific->setMagentoProduct($this->getMagentoProduct());
                }
            }
        }

        return $specifics;
    }

    // #######################################

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    // #######################################

    public function isVariationEnabled()
    {
        return (bool)$this->getData('variation_enabled');
    }

    public function isVariationMode()
    {
        return $this->isVariationEnabled();
    }

    // #######################################

    public function getMotorsSpecificsAttribute()
    {
        return $this->getData('motors_specifics_attribute');
    }

    // #######################################

    public function getCategoryMainSource()
    {
        return array(
            'mode'           => $this->getData('category_main_mode'),
            'value'          => $this->getData('category_main_id'),
            'path'          => $this->getData('category_main_path'),
            'attribute'      => $this->getData('category_main_attribute')
        );
    }

    public function getCategorySecondarySource()
    {
        return array(
            'mode'      => $this->getData('category_secondary_mode'),
            'value'     => $this->getData('category_secondary_id'),
            'path'     => $this->getData('category_secondary_path'),
            'attribute' => $this->getData('category_secondary_attribute')
        );
    }

    public function getStoreCategoryMainSource()
    {
        return array(
            'mode'           => $this->getData('store_category_main_mode'),
            'value'          => $this->getData('store_category_main_id'),
            'path'          => $this->getData('store_category_main_path'),
            'attribute'      => $this->getData('store_category_main_attribute')
        );
    }

    public function getStoreCategorySecondarySource()
    {
        return array(
            'mode'      => $this->getData('store_category_secondary_mode'),
            'value'     => $this->getData('store_category_secondary_id'),
            'path'     => $this->getData('store_category_secondary_path'),
            'attribute' => $this->getData('store_category_secondary_attribute')
        );
    }

    //----------------------------------------

    public function getTaxCategorySource()
    {
        return array(
            'mode'      => $this->getData('tax_category_mode'),
            'value'     => $this->getData('tax_category_value'),
            'attribute' => $this->getData('tax_category_attribute')
        );
    }

    // #######################################

    public function getMotorsSpecifics()
    {
        $attributeCode  = $this->getMotorsSpecificsAttribute();
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

        if (empty($attributeValue)) {
            return array();
        }

        $epids = explode(self::MOTORS_SPECIFICS_VALUE_SEPARATOR, $attributeValue);

        return Mage::getModel('M2ePro/Ebay_Motor_Specific')
            ->getCollection()
            ->addFieldToFilter('epid', array('in' => $epids))
            ->getItems();
    }

    // #######################################

    public function getMainCategory()
    {
        $src = $this->getCategoryMainSource();

        if ($src['mode'] == self::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getSecondaryCategory()
    {
        $src = $this->getCategorySecondarySource();

        if ($src['mode'] == self::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == self::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //----------------------------------------

    public function getStoreCategoryMain()
    {
        $src = $this->getStoreCategoryMainSource();

        if ($src['mode'] == self::STORE_CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == self::STORE_CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getStoreCategorySecondary()
    {
        $src = $this->getStoreCategorySecondarySource();

        if ($src['mode'] == self::STORE_CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == self::STORE_CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //----------------------------------------

    public function getTaxCategory()
    {
        $src = $this->getTaxCategorySource();

        if ($src['mode'] == self::TAX_CATEGORY_MODE_NONE) {
            return '';
        }

        if ($src['mode'] == self::TAX_CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // #######################################

    public function fillCategoriesPaths(array &$data, $marketplaceId, $accountId)
    {
        if (isset($data['category_main_mode']) && empty($data['category_main_path'])) {
            switch ($data['category_main_mode']) {
                case self::CATEGORY_MODE_EBAY:
                    $data['category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                        ->getPathById(
                            $data['category_main_id'],
                            $marketplaceId
                        );
                    break;
                case self::CATEGORY_MODE_ATTRIBUTE:
                    $attributeCode  = $data['category_main_attribute'];
                    $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode);
                    $data['category_main_path'] = 'Magento Attribute' . ' -> ' . $attributeLabel;
                    break;
            }
        }

        if (isset($data['category_secondary_mode']) && empty($data['category_secondary_path'])) {
            switch ($data['category_secondary_mode']) {
                case self::CATEGORY_MODE_EBAY:
                    $data['category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                        ->getPathById(
                            $data['category_secondary_id'],
                            $marketplaceId
                        );
                    break;
                case self::CATEGORY_MODE_ATTRIBUTE:
                    $attributeCode  = $data['category_secondary_attribute'];
                    $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode);
                    $data['category_secondary_path'] = 'Magento Attribute' . ' -> ' . $attributeLabel;
                    break;
            }
        }

        if (isset($data['store_category_main_mode']) && empty($data['store_category_main_path'])) {
            switch ($data['store_category_main_mode']) {
                case self::STORE_CATEGORY_MODE_EBAY:
                    $data['store_category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                        ->getStorePathById(
                            $data['store_category_main_id'],
                            $accountId
                        );
                    break;
                case self::STORE_CATEGORY_MODE_ATTRIBUTE:
                    $attributeCode  = $data['store_category_main_attribute'];
                    $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode);
                    $data['store_category_main_path'] = 'Magento Attribute' . ' -> ' . $attributeLabel;
                    break;
            }
        }

        if (isset($data['store_category_secondary_mode']) && empty($data['store_category_secondary_path'])) {
            switch ($data['store_category_secondary_mode']) {
                case self::STORE_CATEGORY_MODE_EBAY:
                    $data['store_category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                        ->getStorePathById(
                            $data['store_category_secondary_id'],
                            $accountId
                        );
                    break;
                case self::STORE_CATEGORY_MODE_ATTRIBUTE:
                    $attributeCode  = $data['store_category_secondary_attribute'];
                    $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode);
                    $data['store_category_secondary_path'] = 'Magento Attribute' . ' -> ' . $attributeLabel;
                    break;
            }
        }
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_category');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_category');
        return parent::delete();
    }

    // #######################################

    public function getDefaultSettings()
    {
        return array(

            'category_main_id' => 0,
            'category_main_path' => '',
            'category_main_mode' => self::CATEGORY_MODE_EBAY,
            'category_main_attribute' => '',

            'category_secondary_id' => 0,
            'category_secondary_path' => '',
            'category_secondary_mode' => self::CATEGORY_MODE_NONE,
            'category_secondary_attribute' => '',

            'store_category_main_id' => 0,
            'store_category_main_path' => '',
            'store_category_main_mode' => self::STORE_CATEGORY_MODE_NONE,
            'store_category_main_attribute' => '',

            'store_category_secondary_id' => 0,
            'store_category_secondary_path' => '',
            'store_category_secondary_mode' => self::STORE_CATEGORY_MODE_NONE,
            'store_category_secondary_attribute' => '',

            'tax_category_mode' => 0,
            'tax_category_value' => '',
            'tax_category_attribute' => '',

            'variation_enabled' => 1,
            'motors_specifics_attribute' => ''
        );
    }

    // #######################################

    public static function isTaxCategoryShow()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/ebay/template/category/', 'show_tax_category');
    }

    // #######################################

    public function getAffectedListingProducts($asObjects = false)
    {
        if (is_null($this->getId())) {
            throw new LogicException('Method require loaded instance first');
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('template_category_id', $this->getId());

        return $asObjects ? $collection->getItems() : $collection->getData();
    }

    // #######################################

    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();
        $data['specifics'] = $this->getSpecifics();

        foreach ($data['specifics'] as &$specificData) {
            foreach ($specificData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }

        return $data;
    }

    // #######################################

    public function setIsNeedSynchronize($newData, $oldData)
    {
        if (!$this->getResource()->isDifferent($newData,$oldData)) {
            return;
        }

        $ids = array();
        foreach ($this->getAffectedListingProducts() as $listingProduct) {
            $ids[] = (int)$listingProduct['id'];
        }

        if (empty($ids)) {
            return;
        }

        $templates = array('categoryTemplate');

        Mage::getSingleton('core/resource')->getConnection('core_read')->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing_Product'),
            array(
                'is_need_synchronize' => 1,
                'synch_reasons' => new Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',',$templates)."',
                        CONCAT(synch_reasons,'".','.implode(',',$templates)."')
                    )"
                )
            ),
            array('id IN ('.implode(',', $ids).')')
        );
    }

    // #######################################
}