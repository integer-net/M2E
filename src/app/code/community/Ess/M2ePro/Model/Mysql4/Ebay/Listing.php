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
                  SET `items_active_count` =  IFNULL((".$dbSelect->__toString()."),0)
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

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $storeId = (int)$listing->getStoreId();

        $attributeValue = implode(',', $epids);

        $listingProductsCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $listingProductsCollection->addFieldToFilter('id', array('in' => $listingProductIds));
        $listingProductsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductsCollection->getSelect()->columns(array('product_id'));

        $productIds = $listingProductsCollection->getColumnValues('product_id');
        $motorsSpecificsAttribute = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/motor/','motors_specifics_attribute'
        );

        if ($overwrite) {
            Mage::getSingleton('catalog/product_action')->updateAttributes(
                $productIds,
                array($motorsSpecificsAttribute => $attributeValue),
                $storeId
            );
            return;
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->setStoreId($storeId);
        $productCollection->addFieldToFilter('entity_id', array('in' => $productIds));
        $productCollection->addAttributeToSelect($motorsSpecificsAttribute);

        foreach ($productCollection->getItems() as $itemId => $item) {

            $currentAttributeValue = $item->getData($motorsSpecificsAttribute);
            $newAttributeValue = $attributeValue;

            if (!empty($currentAttributeValue)) {
                $newAttributeValue = $currentAttributeValue . ',' . $attributeValue;
            }

            Mage::getSingleton('catalog/product_action')->updateAttributes(
                array($itemId),
                array($motorsSpecificsAttribute => $newAttributeValue),
                $storeId
            );
        }
    }

    // ########################################
}