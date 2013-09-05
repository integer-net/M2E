<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing_Product
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product', 'id');
    }

    // ########################################

    public function getItemsWhereIsProduct($productId)
    {
        $listingTable = Mage::getResourceModel(
            'M2ePro/Listing'
        )->getMainTable();
        $listingProductVariationTable = Mage::getResourceModel(
            'M2ePro/Listing_Product_Variation'
        )->getMainTable();
        $listingProductVariationOptionTable = Mage::getResourceModel(
            'M2ePro/Listing_Product_Variation_Option'
        )->getMainTable();

        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('l' => $listingTable),
                   new Zend_Db_Expr('DISTINCT `lp`.`id`,
                                              `l`.`store_id`,
                                              `lp`.`component_mode`'))
            ->join(
                array('lp' => $this->getMainTable()),
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

        $resultItems = array();
        $currentItems = $this->_getWriteAdapter()->fetchAll($dbSelect);

        foreach ($currentItems as $currentItem) {
            $currentItem['id'] = (int)$currentItem['id'];
            $currentItem['store_id'] = (int)$currentItem['store_id'];
            $currentItem['object'] = Mage::helper('M2ePro/Component')->getComponentObject(
                $currentItem['component_mode'], 'Listing_Product', $currentItem['id']
            );
            $resultItems[] = $currentItem;
        }

        return $resultItems;
    }

    // ########################################

    public function getCatalogProductIds(array $listingProductIds)
    {
        $select = $this->getReadConnection()->select();
        $select->from(array('lp' => $this->getMainTable()));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(array('product_id'));
        $select->where('id IN (?)', $listingProductIds);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    // ########################################
}