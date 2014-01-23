<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing_Product
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    // ########################################

    protected $_isPkAutoIncrement = false;

    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################

    public function getTemplateCategoryIds(array $listingProductIds)
    {
        $select = $this->getReadConnection()->select();
        $select->from(array('elp' => $this->getMainTable()));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(array('template_category_id'));
        $select->where('listing_product_id IN (?)', $listingProductIds);
        $select->where('template_category_id IS NOT NULL');

        $ids = $select->query()->fetchAll(PDO::FETCH_COLUMN);

        return array_unique($ids);
    }

    public function getTemplateOtherCategoryIds(array $listingProductIds)
    {
        $select = $this->getReadConnection()->select();
        $select->from(array('elp' => $this->getMainTable()));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(array('template_other_category_id'));
        $select->where('listing_product_id IN (?)', $listingProductIds);
        $select->where('template_other_category_id IS NOT NULL');

        $ids = $select->query()->fetchAll(PDO::FETCH_COLUMN);

        return array_unique($ids);
    }

    // ########################################
}
