<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Magento_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    private $listingProductMode = false;

    // ########################################

    public function setListingProductModeOn()
    {
        $this->listingProductMode = true;

        $this->_setIdFieldName('id');

        return $this;
    }

    // ########################################

    public function getAllIds($limit = null, $offset = null)
    {
        if (!$this->listingProductMode) {
            return parent::getAllIds($limit, $offset);
        }

        // hack for selecting listing product ids instead entity ids
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect->columns('lp.' . $this->getIdFieldName());
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    // ########################################

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if ($this->listingProductMode) {
            $countSelect->columns('COUNT(lp.id)');
        } else {
            $countSelect->columns('COUNT(DISTINCT e.entity_id)');
            $countSelect->reset(Zend_Db_Select::GROUP);
        }

        $countSelect->resetJoinLeft();

        return $countSelect;
    }

    // ########################################

}