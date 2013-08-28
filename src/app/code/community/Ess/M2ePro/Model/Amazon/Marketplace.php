<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Marketplace extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Marketplace');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        $accounts = Mage::getModel('M2ePro/Amazon_Account')->getCollection()->getItems();
        foreach ($accounts as $account) {
            /** @var $account Ess_M2ePro_Model_Amazon_Account */
            if ($account->isExistMarketplaceItem($this->getId())) {
                return true;
            }
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $categoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($categoriesTable,array('marketplace_id = ?'=>$this->getId()));

        $marketplacesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($marketplacesTable,array('marketplace_id = ?'=>$this->getId()));

        $items = $this->getRelatedSimpleItems('Amazon_Item','marketplace_id',true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $newProductTemplates = $this->getRelatedSimpleItems('Amazon_Template_NewProduct','marketplace_id',true);
        foreach ($newProductTemplates as $newProductTemplate) {
            $newProductTemplate->deleteInstance();
        }

        $this->delete();
        return true;
    }

    // ########################################

    public function getDeveloperKey()
    {
        return $this->getData('developer_key');
    }

    public function getDefaultCurrency()
    {
        return $this->getData('default_currency');
    }

    // ########################################

    public function isNewAsinAvailable()
    {
        $newAsinNotImplementedMarketplaces = array(
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_CA,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_JP,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_CN,
        );

        return !in_array((int)$this->getId(),$newAsinNotImplementedMarketplaces);
    }

    // ########################################

    public function isSynchronized()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');

        $count = (int)$connRead->select()->from($table,'COUNT(*)')
                               ->where('marketplace_id=?',$this->getId())
                               ->query()
                               ->fetchColumn();

        return (int)($count > 0);
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