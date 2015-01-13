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

    public function getProductIds(array $listingProductIds)
    {
        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(array('lp' => $this->getMainTable()))
                       ->reset(Zend_Db_Select::COLUMNS)
                       ->columns(array('product_id'))
                       ->where('id IN (?)', $listingProductIds);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getItemsByProductId($productId)
    {
        $listingTable   = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $variationTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable();
        $optionTable    = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();

        $simpleProductsSelect = $this->_getReadAdapter()
            ->select()
            ->from(
                array('l' => $listingTable),
                       array('lp.id',
                             'l.store_id',
                             'lp.component_mode')
            )
            ->join(
                array('lp' => $this->getMainTable()),
                '`l`.`id` = `lp`.`listing_id`',
                array()
            )
            ->where("`lp`.`product_id` = ?",(int)$productId);

        $variationsProductsSelect = $this->_getReadAdapter()
            ->select()
            ->from(
                array('l' => $listingTable),
                array('lp.id',
                      'l.store_id',
                      'lp.component_mode')
            )
            ->join(
                array('lp' => $this->getMainTable()),
                '`l`.`id` = `lp`.`listing_id`',
                array()
            )
            ->join(
                array('lpv' => $variationTable),
                '`lp`.`id` = `lpv`.`listing_product_id`',
                array()
            )
            ->join(
                array('lpvo' => $optionTable),
                '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                array()
            )
            ->where("`lpvo`.`product_id` = ?",(int)$productId);

        $unionSelect = $this->_getReadAdapter()->select()->union(array(
            $simpleProductsSelect,
            $variationsProductsSelect
        ));

        $result = array();

        foreach ($unionSelect->query()->fetchAll() as $item) {

            $item['id'] = (int)$item['id'];
            $item['store_id'] = (int)$item['store_id'];
            $item['object'] = Mage::helper('M2ePro/Component')->getComponentObject(
                $item['component_mode'], 'Listing_Product', $item['id']
            );

            $result[] = $item;
        }

        return $result;
    }

    // ########################################

    public function getChangedItems(array $attributes,
                                    $componentMode = NULL,
                                    $withStoreFilter = false,
                                    $dbSelectModifier = NULL)
    {
        $resultsByListingProduct = $this->getChangedItemsByListingProduct($attributes,
                                                                          $componentMode,
                                                                          $withStoreFilter,
                                                                          $dbSelectModifier);

        $resultsByVariationOption = $this->getChangedItemsByVariationOption($attributes,
                                                                            $componentMode,
                                                                            $withStoreFilter,
                                                                            $dbSelectModifier);

        $results = array();

        foreach ($resultsByListingProduct as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        foreach ($resultsByVariationOption as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    // ---------------------------------------

    public function getChangedItemsByListingProduct(array $attributes,
                                                    $componentMode = NULL,
                                                    $withStoreFilter = false,
                                                    $dbSelectModifier = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $listingsTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $limit = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from($productsChangesTable,'*')
                       ->order(array('id ASC'))
                       ->limit($limit);

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                          array('pc' => $select),
                          array(
                              'changed_attribute'=>'attribute',
                              'changed_to_value'=>'value_new',
                          )
                       )
                       ->join(
                          array('lp' => $this->getMainTable()),
                          '`pc`.`product_id` = `lp`.`product_id`',
                          'id'
                       )
                       ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                       ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        if ($withStoreFilter) {
            $select->join(array('l' => $listingsTable),'`lp`.`listing_id` = `l`.`id`',array());
            $select->where("`l`.`store_id` = `pc`.`store_id`");
        }

        !is_null($componentMode) && $select->where("`lp`.`component_mode` = ?",(string)$componentMode);
        is_callable($dbSelectModifier) && call_user_func($dbSelectModifier,$select);

        $results = array();

        foreach ($select->query()->fetchAll() as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    public function getChangedItemsByVariationOption(array $attributes,
                                                     $componentMode = NULL,
                                                     $withStoreFilter = false,
                                                     $dbSelectModifier = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $listingsTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $variationsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable();
        $optionsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();
        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $limit = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from($productsChangesTable,'*')
                       ->order(array('id ASC'))
                       ->limit($limit);

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                            array('pc' => $select),
                            array(
                                'changed_attribute'=>'attribute',
                                'changed_to_value'=>'value_new',
                            )
                     )
                     ->join(
                        array('lpvo' => $optionsTable),
                        '`pc`.`product_id` = `lpvo`.`product_id`',
                        array()
                     )
                     ->join(
                        array('lpv' => $variationsTable),
                        '`lpvo`.`listing_product_variation_id` = `lpv`.`id`',
                        array()
                     )
                     ->join(
                        array('lp' => $this->getMainTable()),
                        '`lpv`.`listing_product_id` = `lp`.`id`',
                        array('id')
                     )
                     ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                     ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        if ($withStoreFilter) {
            $select->join(array('l' => $listingsTable),'`lp`.`listing_id` = `l`.`id`',array());
            $select->where("`l`.`store_id` = `pc`.`store_id`");
        }

        !is_null($componentMode) && $select->where("`lpvo`.`component_mode` = ?",(string)$componentMode);
        is_callable($dbSelectModifier) && call_user_func($dbSelectModifier,$select);

        $results = array();

        foreach ($select->query()->fetchAll() as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    // ########################################
}