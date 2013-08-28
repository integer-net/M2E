<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Listing', 'id');
    }

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

    public function getListingsWhereIsProduct($productId)
    {
        $listingProductTable = Mage::getResourceModel(
            'M2ePro/Listing_Product'
        )->getMainTable();
        $listingProductVariationTable = Mage::getResourceModel(
            'M2ePro/Listing_Product_Variation'
        )->getMainTable();
        $listingProductVariationOptionTable = Mage::getResourceModel(
            'M2ePro/Listing_Product_Variation_Option'
        )->getMainTable();

        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('l' => $this->getMainTable()),
                new Zend_Db_Expr('DISTINCT `l`.`id`,
                                           `l`.`store_id`,
                                           `l`.`component_mode`'))
            ->join(
                array('lp' => $listingProductTable),
                '`l`.`id` = `lp`.`listing_id`',
                array()
            )
            ->joinLeft(
                array('lpv' => $listingProductVariationTable),
                '`lp`.`id` = `lpv`.`listing_product_id`',
                array()
            )
            ->joinLeft(
                array('lpvo' => $listingProductVariationOptionTable),
                '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                array()
            )
            ->where("`lp`.`product_id` = ?",(int)$productId)
            ->orWhere("`lpvo`.`product_id` IS NOT NULL AND `lpvo`.`product_id` = ?",(int)$productId);

        $newData = array();
        $oldData = $this->_getWriteAdapter()->fetchAll($dbSelect);

        $listingsIds = array();
        foreach ($oldData as $item) {
            if (in_array($item['id'],$listingsIds)) {
                continue;
            }
            $item['id'] = (int)$item['id'];
            $item['store_id'] = (int)$item['store_id'];
            $newData[] = $item;
        }

        return $newData;
    }
}