<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Play_Template_SellingFormat getResource()
 */
class Ess_M2ePro_Model_Play_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    const QTY_MODE_PRODUCT       = 1;
    const QTY_MODE_SINGLE        = 2;
    const QTY_MODE_NUMBER        = 3;
    const QTY_MODE_ATTRIBUTE     = 4;
    const QTY_MODE_PRODUCT_FIXED = 5;

    const QTY_MODIFICATION_MODE_OFF = 0;
    const QTY_MODIFICATION_MODE_ON = 1;

    const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    const QTY_MAX_POSTED_DEFAULT_VALUE = 10;

    const PRICE_NONE      = 0;
    const PRICE_PRODUCT   = 1;
    const PRICE_SPECIAL   = 2;
    const PRICE_ATTRIBUTE = 3;

    const PRICE_VARIATION_MODE_PARENT   = 1;
    const PRICE_VARIATION_MODE_CHILDREN = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Template_SellingFormat');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Play_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize();
    }

    // ########################################

    public function getAttributeSets()
    {
        return $this->getParentObject()->getAttributeSets();
    }

    public function getAttributeSetsIds()
    {
        return $this->getParentObject()->getAttributeSetsIds();
    }

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
        return $this->getQtyMode() == self::QTY_MODE_PRODUCT;
    }

    public function isQtyModeSingle()
    {
        return $this->getQtyMode() == self::QTY_MODE_SINGLE;
    }

    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == self::QTY_MODE_NUMBER;
    }

    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == self::QTY_MODE_ATTRIBUTE;
    }

    public function isQtyModeProductFixed()
    {
        return $this->getQtyMode() == self::QTY_MODE_PRODUCT_FIXED;
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

        if ($src['mode'] == self::QTY_MODE_ATTRIBUTE) {
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

    public function getPriceGbrMode()
    {
        return (int)$this->getData('price_gbr_mode');
    }

    public function isPriceGbrModeProduct()
    {
        return $this->getPriceGbrMode() == self::PRICE_PRODUCT;
    }

    public function isPriceGbrModeSpecial()
    {
        return $this->getPriceGbrMode() == self::PRICE_SPECIAL;
    }

    public function isPriceGbrModeAttribute()
    {
        return $this->getPriceGbrMode() == self::PRICE_ATTRIBUTE;
    }

    public function getPriceGbrCoefficient()
    {
        return $this->getData('price_gbr_coefficient');
    }

    public function getPriceGbrSource()
    {
        return array(
            'mode'        => $this->getPriceGbrMode(),
            'coefficient' => $this->getPriceGbrCoefficient(),
            'attribute'   => $this->getData('price_gbr_custom_attribute')
        );
    }

    public function getPriceGbrAttributes()
    {
        $attributes = array();
        $src = $this->getPriceGbrSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPriceEuroMode()
    {
        return (int)$this->getData('price_euro_mode');
    }

    public function isPriceEuroModeProduct()
    {
        return $this->getPriceEuroMode() == self::PRICE_PRODUCT;
    }

    public function isPriceEuroModeSpecial()
    {
        return $this->getPriceEuroMode() == self::PRICE_SPECIAL;
    }

    public function isPriceEuroModeAttribute()
    {
        return $this->getPriceEuroMode() == self::PRICE_ATTRIBUTE;
    }

    public function getPriceEuroCoefficient()
    {
        return $this->getData('price_euro_coefficient');
    }

    public function getPriceEuroSource()
    {
        return array(
            'mode'        => $this->getPriceEuroMode(),
            'coefficient' => $this->getPriceEuroCoefficient(),
            'attribute'   => $this->getData('price_euro_custom_attribute')
        );
    }

    public function getPriceEuroAttributes()
    {
        $attributes = array();
        $src = $this->getPriceEuroSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function usesProductOrSpecialPrice($currency = null)
    {
        if ($currency == Ess_M2ePro_Helper_Component_Play::CURRENCY_EUR || is_null($currency)) {
            if ($this->isPriceEuroModeProduct() || $this->isPriceEuroModeSpecial()) {
                return true;
            }
        }

        if ($currency == Ess_M2ePro_Helper_Component_Play::CURRENCY_GBP || is_null($currency)) {
            if ($this->isPriceGbrModeProduct() || $this->isPriceGbrModeSpecial()) {
                return true;
            }
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
        return array_unique(array_merge(
            $this->getQtyAttributes(),
            $this->getPriceGbrAttributes(),
            $this->getPriceEuroAttributes()
        ));
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getQtyAttributes(),
            $this->getPriceGbrAttributes(),
            $this->getPriceEuroAttributes()
        ));
    }

    // ########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Collection $listingCollection */
        $listingCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing');
        $listingCollection->addFieldToFilter('template_selling_format_id', $this->getId());
        $listingCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingCollection->getSelect()->columns('id');

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id',array('in' => $listingCollection->getSelect()));

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('template_sellingformat');
        return parent::delete();
    }

    // ########################################
}