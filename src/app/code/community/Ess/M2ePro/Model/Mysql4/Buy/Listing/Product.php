<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Buy_Listing_Product
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    // ########################################

    protected $_isPkAutoIncrement = false;

    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Buy_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################

    public function getChangedItems(array $attributes,
                                    $withStoreFilter = false)
    {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItems(
            $attributes,
            Ess_M2ePro_Helper_Component_Buy::NICK,
            $withStoreFilter,
            array($this,'changedItemsSelectModifier')
        );
    }

    public function getChangedItemsByListingProduct(array $attributes,
                                                    $withStoreFilter = false)
    {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItemsByListingProduct(
            $attributes,
            Ess_M2ePro_Helper_Component_Buy::NICK,
            $withStoreFilter,
            array($this,'changedItemsSelectModifier')
        );
    }

    public function getChangedItemsByVariationOption(array $attributes,
                                                     $withStoreFilter = false)
    {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItemsByVariationOption(
            $attributes,
            Ess_M2ePro_Helper_Component_Buy::NICK,
            $withStoreFilter,
            array($this,'changedItemsSelectModifier')
        );
    }

    // --------------------------------------------------

    public function changedItemsSelectModifier(Varien_Db_Select $select) {

        $select->join(
            array('blp' => $this->getMainTable()),
            '`lp`.`id` = `blp`.`listing_product_id`',
            array()
        );

        $select->where(
            '`blp`.`is_variation_product` = 0
             OR
             (
                `blp`.`is_variation_product` = 1
                 AND
                 `blp`.`is_variation_matched` = 1
             )'
        );
    }

    // ########################################
}