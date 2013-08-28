<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Marketplace extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const IS_MULTIVARIATION_NO  = 0;
    const IS_MULTIVARIATION_YES = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Marketplace');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $categoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($categoriesTable,array('marketplace_id = ?'=>$this->getId()));

        $marketplacesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($marketplacesTable,array('marketplace_id = ?'=>$this->getId()));

        $shippingsTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_shipping');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($shippingsTable,array('marketplace_id = ?'=>$this->getId()));

        $shippingsCategoriesTable  = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_ebay_dictionary_shipping_category');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($shippingsCategoriesTable,array('marketplace_id = ?'=>$this->getId()));

        $this->delete();
        return true;
    }

    // ########################################

    public function getCategoriesVersion()
    {
        return (int)$this->getData('categories_version');
    }

    public function isMultivariationEnabled()
    {
        return (int)$this->getData('is_multivariation') == self::IS_MULTIVARIATION_YES;
    }

    // ########################################

    public function getCategory($categoryId)
    {
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($tableCategories,'*')
                             ->where('`marketplace_id` = ?',(int)$this->getId())
                             ->where('`category_id` = ?',(int)$categoryId);

        $categories = Mage::getResourceModel('core/config')
                                ->getReadConnection()
                                ->fetchAll($dbSelect);

        return count($categories) > 0 ? $categories[0] : array();
    }

    public function getChildCategories($parentId)
    {
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($tableCategories,array('category_id','title','is_leaf'))
                             ->where('`marketplace_id` = ?',(int)$this->getId())
                             ->where('`parent_id` = ?',(int)$parentId)
                             ->order(array('title ASC'));

        $categories = Mage::getResourceModel('core/config')
                                ->getReadConnection()
                                ->fetchAll($dbSelect);

        return $categories;
    }

    // ########################################

    public static function getMultivariationObjects()
    {
        $collection = Mage::getModel('M2ePro/Ebay_Marketplace')->getCollection();
        $collection->addFieldToFilter('is_multivariation',self::IS_MULTIVARIATION_YES);
        return $collection->getItems();
    }

    public static function getMultivariationIds()
    {
        $result = array();
        $tempMarketplaces = Mage::getModel('M2ePro/Ebay_Marketplace')->getMultivariationObjects();
        foreach ($tempMarketplaces as $tempMarketplace) {
            $result[] = (int)$tempMarketplace->getId();
        }
        return $result;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('marketplace');
        return parent::delete();
    }

    // ########################################
}