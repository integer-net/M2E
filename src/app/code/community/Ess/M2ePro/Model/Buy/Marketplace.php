<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Marketplace extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Marketplace');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Buy_Account')->getCollection()->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $categoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category');
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete($categoriesTable);

        $items = $this->getRelatedSimpleItems('Buy_Item','marketplace_id',true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $this->delete();
        return true;
    }

    public function isSynchronized()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category');

        $count = (int)$connRead->select()->from($table,'COUNT(*)')
                                         ->query()
                                         ->fetchColumn();

        return $count > 0;
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
