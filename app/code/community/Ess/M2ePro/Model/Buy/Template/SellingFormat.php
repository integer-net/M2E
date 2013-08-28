<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 */
class Ess_M2ePro_Model_Buy_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    const QTY_MODE_PRODUCT   = 1;
    const QTY_MODE_SINGLE    = 2;
    const QTY_MODE_NUMBER    = 3;
    const QTY_MODE_ATTRIBUTE = 4;

    const QTY_MAX_POSTED_MODE_OFF = 0;
    const QTY_MAX_POSTED_MODE_ON = 1;

    const QTY_MAX_POSTED_DEFAULT_VALUE = 10;

    const PRICE_NONE      = 0;
    const PRICE_PRODUCT   = 1;
    const PRICE_SPECIAL   = 2;
    const PRICE_ATTRIBUTE = 3;
    const PRICE_FINAL     = 4;

    const PRICE_VARIATION_MODE_PARENT   = 1;
    const PRICE_VARIATION_MODE_CHILDREN = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_SellingFormat');
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getListings($asObjects,$filters);
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
            'qty_max_posted_value'   => $this->getQtyMaxPostedValue()
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
        return $this->getPriceMode() == self::PRICE_PRODUCT;
    }

    public function isPriceModeSpecial()
    {
        return $this->getPriceMode() == self::PRICE_SPECIAL;
    }

    public function isPriceModeAttribute()
    {
        return $this->getPriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function isPriceModeFinal()
    {
        return $this->getPriceMode() == self::PRICE_FINAL;
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

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

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

    //-------------------------

    public function getCustomerGroupId()
    {
        return (int)$this->getData('customer_group_id');
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array_unique(array_merge(
            $this->getQtyAttributes(),
            $this->getPriceAttributes()
        ));
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_sellingformat');
        return parent::delete();
    }

    // ########################################
}