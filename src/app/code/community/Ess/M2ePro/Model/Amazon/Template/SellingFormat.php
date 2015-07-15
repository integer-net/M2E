<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Amazon_Template_SellingFormat getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const QTY_MODIFICATION_MODE_OFF = 0;
    const QTY_MODIFICATION_MODE_ON = 1;

    const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    const QTY_MAX_POSTED_DEFAULT_VALUE = 100;

    const PRICE_VARIATION_MODE_PARENT   = 1;
    const PRICE_VARIATION_MODE_CHILDREN = 2;

    const DATE_VALUE      = 0;
    const DATE_ATTRIBUTE  = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_SellingFormat');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Amazon_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize();
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getRelatedComponentItems('Listing','template_selling_format_id',$asObjects,$filters);
    }

    // ########################################

    public function getQtyMode()
    {
        return (int)$this->getData('qty_mode');
    }

    public function isQtyModeProduct()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT;
    }

    public function isQtyModeSingle()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_SINGLE;
    }

    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER;
    }

    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE;
    }

    public function isQtyModeProductFixed()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED;
    }

    public function getQtyNumber()
    {
        return (int)$this->getData('qty_custom_value');
    }

    public function getQtySource()
    {
        return array(
            'mode'      => $this->getQtyMode(),
            'value'     => $this->getQtyNumber(),
            'attribute' => $this->getData('qty_custom_attribute'),
            'qty_modification_mode'     => $this->getQtyModificationMode(),
            'qty_min_posted_value'      => $this->getQtyMinPostedValue(),
            'qty_max_posted_value'      => $this->getQtyMaxPostedValue(),
            'qty_percentage'            => $this->getQtyPercentage()
        );
    }

    public function getQtyAttributes()
    {
        $attributes = array();
        $src = $this->getQtySource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getQtyPercentage()
    {
        return (int)$this->getData('qty_percentage');
    }

    //-------------------------

    public function getQtyModificationMode()
    {
        return (int)$this->getData('qty_modification_mode');
    }

    public function isQtyModificationModeOn()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_ON;
    }

    public function isQtyModificationModeOff()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_OFF;
    }

    public function getQtyMinPostedValue()
    {
        return (int)$this->getData('qty_min_posted_value');
    }

    public function getQtyMaxPostedValue()
    {
        return (int)$this->getData('qty_max_posted_value');
    }

    //-------------------------

    public function getPriceMode()
    {
        return (int)$this->getData('price_mode');
    }

    public function isPriceModeProduct()
    {
        return $this->getPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isPriceModeSpecial()
    {
        return $this->getPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isPriceModeAttribute()
    {
        return $this->getPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getPriceCoefficient()
    {
        return $this->getData('price_coefficient');
    }

    public function getPriceSource()
    {
        return array(
            'mode'        => $this->getPriceMode(),
            'coefficient' => $this->getPriceCoefficient(),
            'attribute'   => $this->getData('price_custom_attribute')
        );
    }

    public function getPriceAttributes()
    {
        $attributes = array();
        $src = $this->getPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getMapPriceMode()
    {
        return (int)$this->getData('map_price_mode');
    }

    public function isMapPriceModeNone()
    {
        return $this->getMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isMapPriceModeProduct()
    {
        return $this->getMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isMapPriceModeSpecial()
    {
        return $this->getMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isMapPriceModeAttribute()
    {
        return $this->getMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getMapPriceSource()
    {
        return array(
            'mode'        => $this->getMapPriceMode(),
            'attribute'   => $this->getData('map_price_custom_attribute')
        );
    }

    public function getMapPriceAttributes()
    {
        $attributes = array();
        $src = $this->getMapPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getSalePriceMode()
    {
        return (int)$this->getData('sale_price_mode');
    }

    public function isSalePriceModeNone()
    {
        return $this->getSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isSalePriceModeProduct()
    {
        return $this->getSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isSalePriceModeSpecial()
    {
        return $this->getSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isSalePriceModeAttribute()
    {
        return $this->getSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getSalePriceCoefficient()
    {
        return $this->getData('sale_price_coefficient');
    }

    public function getSalePriceSource()
    {
        return array(
            'mode'        => $this->getSalePriceMode(),
            'coefficient' => $this->getSalePriceCoefficient(),
            'attribute'   => $this->getData('sale_price_custom_attribute')
        );
    }

    public function getSalePriceAttributes()
    {
        $attributes = array();
        $src = $this->getSalePriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getSalePriceStartDateMode()
    {
        return (int)$this->getData('sale_price_start_date_mode');
    }

    public function isSalePriceStartDateModeValue()
    {
        return $this->getSalePriceStartDateMode() == self::DATE_VALUE;
    }

    public function isSalePriceStartDateModeAttribute()
    {
        return $this->getSalePriceStartDateMode() == self::DATE_ATTRIBUTE;
    }

    public function getSalePriceStartDateValue()
    {
        return $this->getData('sale_price_start_date_value');
    }

    public function getSalePriceStartDateSource()
    {
        return array(
            'mode'        => $this->getSalePriceStartDateMode(),
            'value'       => $this->getSalePriceStartDateValue(),
            'attribute'   => $this->getData('sale_price_start_date_custom_attribute')
        );
    }

    public function getSalePriceStartDateAttributes()
    {
        $attributes = array();
        $src = $this->getSalePriceStartDateSource();

        if ($src['mode'] == self::DATE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getSalePriceEndDateMode()
    {
        return (int)$this->getData('sale_price_end_date_mode');
    }

    public function isSalePriceEndDateModeValue()
    {
        return $this->getSalePriceEndDateMode() == self::DATE_VALUE;
    }

    public function isSalePriceEndDateModeAttribute()
    {
        return $this->getSalePriceEndDateMode() == self::DATE_ATTRIBUTE;
    }

    public function getSalePriceEndDateValue()
    {
        return $this->getData('sale_price_end_date_value');
    }

    public function getSalePriceEndDateSource()
    {
        return array(
            'mode'        => $this->getSalePriceEndDateMode(),
            'value'       => $this->getSalePriceEndDateValue(),
            'attribute'   => $this->getData('sale_price_end_date_custom_attribute')
        );
    }

    public function getSalePriceEndDateAttributes()
    {
        $attributes = array();
        $src = $this->getSalePriceEndDateSource();

        if ($src['mode'] == self::DATE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function usesProductOrSpecialPrice()
    {
        if ($this->isPriceModeProduct() || $this->isPriceModeSpecial()) {
            return true;
        }

        if ($this->isSalePriceModeProduct() || $this->isSalePriceModeSpecial()) {
            return true;
        }

        return false;
    }

    // ########################################

    public function getPriceVariationMode()
    {
        return (int)$this->getData('price_variation_mode');
    }

    public function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    public function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getUsedAttributes();
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getQtyAttributes(),
            $this->getPriceAttributes(),
            $this->getMapPriceAttributes(),
            $this->getSalePriceAttributes(),
            $this->getSalePriceStartDateAttributes(),
            $this->getSalePriceEndDateAttributes()
        ));
    }

    // ########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @param bool $onlyPhysicalUnits
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*', $onlyPhysicalUnits = false)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Collection $listingCollection */
        $listingCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing');
        $listingCollection->addFieldToFilter('template_selling_format_id', $this->getId());
        $listingCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingCollection->getSelect()->columns('id');

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id',array('in' => $listingCollection->getSelect()));

        if ($onlyPhysicalUnits) {
            $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        }

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'), true);
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::delete();
    }

    // ########################################
}