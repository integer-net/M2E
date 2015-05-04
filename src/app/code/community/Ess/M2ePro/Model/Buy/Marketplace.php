<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
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

    public function getBuyItems($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Buy_Item','marketplace_id',$asObjects,$filters);
    }

    // ########################################

    public function getCurrency()
    {
        return Ess_M2ePro_Helper_Component_Buy::DEFAULT_CURRENCY;
    }

    // ########################################

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
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::delete();
    }

    // ########################################
}
