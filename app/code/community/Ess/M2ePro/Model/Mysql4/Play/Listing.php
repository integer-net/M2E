<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Play_Listing extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Play_Listing', 'listing_id');
        $this->_isPkAutoIncrement = false;
    }

    public function updateStatisticColumns()
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $buyListingProductTable = Mage::getResourceModel('M2ePro/Play_Listing_Product')->getMainTable();

        $dbExpr = new Zend_Db_Expr('SUM(`online_qty`)');
        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('lp' => $listingProductTable),$dbExpr)
            ->join(array('plp' => $buyListingProductTable),'lp.id = plp.listing_product_id',array())
            ->where("`listing_id` = `{$listingTable}`.`id`")
            ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $query = "UPDATE `{$listingTable}`
                  SET `items_active_count` =  (".$dbSelect->__toString().")
                  WHERE `component_mode` = 'play'";

        $this->_getWriteAdapter()->query($query);
    }
}