<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    // ########################################

    protected $_isPkAutoIncrement = false;

    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing', 'listing_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################

    public function updateStatisticColumns()
    {
        $this->updateProductsSoldCount();
        $this->updateItemsActiveCount();
        $this->updateItemsSoldCount();
    }

    //-----------------------------------------

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

    // ########################################

    public function getCatalogProductCollection($listingId)
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id'
            ),
            '{{table}}.listing_id='.(int)$listingId
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id'
            )
        );

        return $collection;
    }

    // ########################################

    public function updateMotorsSpecificsAttributesData(
        $listingId,
        array $listingProductIds,
        $epids,
        $overwrite = false
    ) {
        if (count($listingProductIds) == 0) {
            return;
        }

        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $attributeSeparator = Ess_M2ePro_Model_Ebay_Template_Category::MOTORS_SPECIFICS_VALUE_SEPARATOR;
        $attributeValue = implode($attributeSeparator, $epids);

        $listingProductsCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $listingProductsCollection->addFieldToFilter('id', array('in' => $listingProductIds));
        $listingProductsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductsCollection->getSelect()->columns(array('product_id'));

        $productIds = $listingProductsCollection->getColumnValues('product_id');

        $attributesData = Mage::getResourceModel('M2ePro/Ebay_Listing')
            ->getMotorsSpecificsAttributesData(
                $listingId,
                $listingProductIds
            );
        $attributeCodes = array_keys($attributesData);

        if ($overwrite) {
            foreach ($attributesData as $attributeCode => $attributeData) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(
                        $attributeData['product_ids'],
                        array($attributeCode => $attributeValue),
                        $storeId
                    );
            }
            return;
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addFieldToFilter('entity_id', array('in' => $productIds));

        foreach ($attributeCodes as $attributeCode) {
            $productCollection->addAttributeToSelect($attributeCode);
        }

        foreach ($attributesData as $attributeCode => $attributeData) {
            foreach ($attributeData['product_ids'] as $productId) {
                $product = $productCollection->getItemByColumnValue('entity_id', $productId);

                if (is_null($product)) {
                    continue;
                }

                $product->setStoreId($storeId);
                /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
                $magentoProduct->setProduct($product);
                $magentoProduct->saveAttribute($attributeCode, $attributeValue, $overwrite, $attributeSeparator);
            }
        }
    }

    // ########################################

    public function getMotorsSpecificsAttributesData($listingId, array $listingProductIds = array())
    {
        $resource = Mage::getSingleton('core/resource');

        $dbSelect = $resource
            ->getConnection('core_read')
                ->select()
                ->from(
                    array('lp' => $resource->getTableName('M2ePro/Listing_Product')),
                    array('product_id')
                )
                ->where('listing_id = ?', $listingId);

        if (count($listingProductIds) > 0) {
            $dbSelect->where('lp.id IN (?)', $listingProductIds);
        }

        $dbSelect->joinLeft(
            array('elp' => $resource->getTableName('M2ePro/Ebay_Listing_Product')),
            'lp.id = elp.listing_product_id',
            array('')
        );

        $dbSelect->joinLeft(
            array('etc' => $resource->getTableName('M2ePro/Ebay_Template_Category')),
            'etc.id = elp.template_category_id',
            'motors_specifics_attribute'
        );

        $query = $dbSelect->query();

        $data = array();

        while (($row = $query->fetch()) !== false) {
            if ($row['motors_specifics_attribute'] == '') {
                continue;
            }

            $attribute = $row['motors_specifics_attribute'];

            if (!array_key_exists($attribute, $data)) {
                $attributeObject = Mage::getResourceModel('catalog/product')->getAttribute($attribute);

                if (!$attributeObject || $attributeObject->getBackendType() != 'text') {
                    $data[$attribute] = NULL;
                } else {
                    $data[$attribute] = array(
                        'attribute_id' => (int)$attributeObject->getId(),
                        'product_ids' => array()
                    );
                }
            }

            if (is_null($data[$attribute])) {
                continue;
            }

            $data[$attribute]['product_ids'][] = (int)$row['product_id'];
        }

        return array_filter($data);
    }

    // ########################################

    public function hasEmptyMotorsSpecificsAttributes($listingId, array $listingProductIds = array())
    {
        $resource = Mage::getSingleton('core/resource');

        $dbSelect = $resource
            ->getConnection('core_read')
                ->select()
                ->from(
                    array('lp' => $resource->getTableName('M2ePro/Listing_Product')),
                    array('')
                )
                ->where('listing_id = ?', $listingId);

        if (count($listingProductIds) > 0) {
            $dbSelect->where('lp.id IN (?)', $listingProductIds);
        }

        $dbSelect->joinLeft(
            array('elp' => $resource->getTableName('M2ePro/Ebay_Listing_Product')),
            'lp.id = elp.listing_product_id',
            array('')
        );

        $dbSelect->joinLeft(
            array('etc' => $resource->getTableName('M2ePro/Ebay_Template_Category')),
            'etc.id = elp.template_category_id',
            array('')
        );

        $dbSelect->where(
            'elp.template_category_id IS NULL'
            . ' OR etc.motors_specifics_attribute IS NULL'
            . ' OR etc.motors_specifics_attribute = \'\''
        );
        $dbSelect->columns(new Zend_Db_Expr('COUNT(*)'));

        return $dbSelect->query()->fetchColumn() != 0;
    }

    // ########################################
}