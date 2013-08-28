<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing', 'listing_id');
        $this->_isPkAutoIncrement = false;
    }

    public function updateStatisticColumns()
    {
        $this->updateProductsSoldCount();
        $this->updateItemsActiveCount();
        $this->updateItemsSoldCount();
    }

    private function updateProductsSoldCount()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $dbSelect = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`listing_id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_SOLD);

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `products_sold_count` =  (".$dbSelect->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }

    private function updateItemsActiveCount()
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $ebayListingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();

        $dbExpr = new Zend_Db_Expr('SUM(`online_qty` - `online_qty_sold`)');
        $dbSelect = $this->_getWriteAdapter()
                             ->select()
                             ->from(array('lp' => $listingProductTable),$dbExpr)
                             ->join(array('elp' => $ebayListingProductTable),'lp.id = elp.listing_product_id',array())
                             ->where("`listing_id` = `{$listingTable}`.`id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $query = "UPDATE `{$listingTable}`
                  SET `items_active_count` =  (".$dbSelect->__toString().")
                  WHERE `component_mode` = 'ebay'";

        $this->_getWriteAdapter()->query($query);
    }

    private function updateItemsSoldCount()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $ebayListingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();

        $dbExpr = new Zend_Db_Expr('SUM(`online_qty_sold`)');
        $dbSelect = $this->_getWriteAdapter()
                             ->select()
                             ->from(array('lp' => $listingProductTable),$dbExpr)
                             ->join(array('elp' => $ebayListingProductTable),'lp.id = elp.listing_product_id',array())
                             ->where("`listing_id` = `{$this->getMainTable()}`.`listing_id`");

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `items_sold_count` =  (".$dbSelect->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }
}