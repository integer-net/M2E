<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing', 'id');
    }

    // ########################################

    public function updateStatisticColumns()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $productsTotalCount = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`");

        $productsActiveCount = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $productsInactiveCount = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` != ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `products_total_count` = (".$productsTotalCount->__toString()."),
                      `products_active_count` = (".$productsActiveCount->__toString()."),
                      `products_inactive_count` = (".$productsInactiveCount->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }

    // ########################################
}