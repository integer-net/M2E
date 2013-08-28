<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const LISTING_TYPE_AUCTION      = 1;
    const LISTING_TYPE_FIXED        = 2;
    const LISTING_TYPE_ATTRIBUTE    = 3;

    const EBAY_LISTING_TYPE_AUCTION  = 'Chinese';
    const EBAY_LISTING_TYPE_FIXED    = 'FixedPriceItem';

    const LISTING_IS_PRIVATE_NO   = 0;
    const LISTING_IS_PRIVATE_YES  = 1;

    const DURATION_TYPE_EBAY       = 1;
    const DURATION_TYPE_ATTRIBUTE  = 2;

    const QTY_MODE_PRODUCT      = 1;
    const QTY_MODE_SINGLE       = 2;
    const QTY_MODE_NUMBER       = 3;
    const QTY_MODE_ATTRIBUTE    = 4;

    const QTY_MAX_POSTED_MODE_OFF = 0;
    const QTY_MAX_POSTED_MODE_ON = 1;

    const QTY_MAX_POSTED_DEFAULT_VALUE = 10;

    const PRICE_NONE      = 0;
    const PRICE_PRODUCT   = 1;
    const PRICE_SPECIAL   = 2;
    const PRICE_ATTRIBUTE = 3;
    const PRICE_FINAL     = 4;

    const PRICE_VARIATION_MODE_PARENT        = 1;
    const PRICE_VARIATION_MODE_CHILDREN      = 2;

    const BEST_OFFER_MODE_NO  = 0;
    const BEST_OFFER_MODE_YES = 1;

    const BEST_OFFER_ACCEPT_MODE_NO          = 0;
    const BEST_OFFER_ACCEPT_MODE_PERCENTAGE  = 1;
    const BEST_OFFER_ACCEPT_MODE_ATTRIBUTE   = 2;

    const BEST_OFFER_REJECT_MODE_NO          = 0;
    const BEST_OFFER_REJECT_MODE_PERCENTAGE  = 1;
    const BEST_OFFER_REJECT_MODE_ATTRIBUTE   = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_SellingFormat');
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject->getListings($asObjects,$filters);
    }

    // ########################################

    public function getListingType()
    {
        return (int)$this->getData('listing_type');
    }

    public function isListingTypeFixed()
    {
        return $this->getListingType() == self::LISTING_TYPE_FIXED;
    }

    public function isListingTypeAuction()
    {
        return $this->getListingType() == self::LISTING_TYPE_AUCTION;
    }

    public function isListingTypeAttribute()
    {
        return $this->getListingType() == self::LISTING_TYPE_ATTRIBUTE;
    }

    public function getListingTypeSource()
    {
        return array(
            'mode'      => $this->getListingType(),
            'attribute' => $this->getData('listing_type_attribute')
        );
    }

    public function getListingTypeAttributes()
    {
        $attributes = array();
        $src = $this->getListingTypeSource();

        if ($src['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getDurationMode()
    {
        return (int)$this->getData('duration_mode');
    }

    public function getDurationSource()
    {
        $tempSrc = $this->getListingTypeSource();

        $mode = self::DURATION_TYPE_EBAY;
        if ($tempSrc['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $mode = self::DURATION_TYPE_ATTRIBUTE;
        }

        return array(
            'mode'     => (int)$mode,
            'value'     => (int)$this->getDurationMode(),
            'attribute' => $this->getData('duration_attribute')
        );
    }

    public function getDurationAttributes()
    {
        $attributes = array();
        $src = $this->getDurationSource();

        if ($src['mode'] == self::DURATION_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function isPrivateListing()
    {
        return (bool)$this->getData('listing_is_private');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    //-------------------------

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

    public function getStartPriceMode()
    {
        return (int)$this->getData('start_price_mode');
    }

    public function isStartPriceModeNone()
    {
        return $this->getStartPriceMode() == self::PRICE_NONE;
    }

    public function isStartPriceModeProduct()
    {
        return $this->getStartPriceMode() == self::PRICE_PRODUCT;
    }

    public function isStartPriceModeSpecial()
    {
        return $this->getStartPriceMode() == self::PRICE_SPECIAL;
    }

    public function isStartPriceModeAttribute()
    {
        return $this->getStartPriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function isStartPriceModeFinal()
    {
        return $this->getStartPriceMode() == self::PRICE_FINAL;
    }

    public function getStartPriceCoefficient()
    {
        return $this->getData('start_price_coefficient');
    }

    public function getStartPriceSource()
    {
        return array(
            'mode'        => $this->getStartPriceMode(),
            'coefficient' => $this->getStartPriceCoefficient(),
            'attribute'   => $this->getData('start_price_custom_attribute')
        );
    }

    public function getStartPriceAttributes()
    {
        $attributes = array();
        $src = $this->getStartPriceSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getReservePriceMode()
    {
        return (int)$this->getData('reserve_price_mode');
    }

    public function isReservePriceModeNone()
    {
        return $this->getReservePriceMode() == self::PRICE_NONE;
    }

    public function isReservePriceModeProduct()
    {
        return $this->getReservePriceMode() == self::PRICE_PRODUCT;
    }

    public function isReservePriceModeSpecial()
    {
        return $this->getReservePriceMode() == self::PRICE_SPECIAL;
    }

    public function isReservePriceModeAttribute()
    {
        return $this->getReservePriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function isReservePriceModeFinal()
    {
        return $this->getReservePriceMode() == self::PRICE_FINAL;
    }

    public function getReservePriceCoefficient()
    {
        return $this->getData('reserve_price_coefficient');
    }

    public function getReservePriceSource()
    {
        return array(
            'mode'        => $this->getReservePriceMode(),
            'coefficient' => $this->getReservePriceCoefficient(),
            'attribute'   => $this->getData('reserve_price_custom_attribute')
        );
    }

    public function getReservePriceAttributes()
    {
        $attributes = array();
        $src = $this->getReservePriceSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getBuyItNowPriceMode()
    {
        return (int)$this->getData('buyitnow_price_mode');
    }

    public function isBuyItNowPriceModeNone()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_NONE;
    }

    public function isBuyItNowPriceModeProduct()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_PRODUCT;
    }

    public function isBuyItNowPriceModeSpecial()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_SPECIAL;
    }

    public function isBuyItNowPriceModeAttribute()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_ATTRIBUTE;
    }

    public function isBuyItNowPriceModeFinal()
    {
        return $this->getBuyItNowPriceMode() == self::PRICE_FINAL;
    }

    public function getBuyItNowPriceCoefficient()
    {
        return $this->getData('buyitnow_price_coefficient');
    }

    public function getBuyItNowPriceSource()
    {
        return array(
            'mode'      => $this->getBuyItNowPriceMode(),
            'coefficient' => $this->getBuyItNowPriceCoefficient(),
            'attribute' => $this->getData('buyitnow_price_custom_attribute')
        );
    }

    public function getBuyItNowPriceAttributes()
    {
        $attributes = array();
        $src = $this->getBuyItNowPriceSource();

        if ($src['mode'] == self::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function isBestOfferEnabled()
    {
        return (int)$this->getData('best_offer_mode') == self::BEST_OFFER_MODE_YES;
    }

    //-------------------------

    public function getBestOfferAcceptMode()
    {
        return (int)$this->getData('best_offer_accept_mode');
    }

    public function isBestOfferAcceptModeNo()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_NO;
    }

    public function isBestOfferAcceptModePercentage()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_PERCENTAGE;
    }

    public function isBestOfferAcceptModeAttribute()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE;
    }

    public function getBestOfferAcceptValue()
    {
        return $this->getData('best_offer_accept_value');
    }

    public function getBestOfferAcceptSource()
    {
        return array(
            'mode' => $this->getBestOfferAcceptMode(),
            'value' => $this->getBestOfferAcceptValue(),
            'attribute' => $this->getData('best_offer_accept_attribute')
        );
    }

    public function getBestOfferAcceptAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferAcceptSource();

        if ($src['mode'] == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getBestOfferRejectMode()
    {
        return (int)$this->getData('best_offer_reject_mode');
    }

    public function isBestOfferRejectModeNo()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_NO;
    }

    public function isBestOfferRejectModePercentage()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_PERCENTAGE;
    }

    public function isBestOfferRejectModeAttribute()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE;
    }

    public function getBestOfferRejectValue()
    {
        return $this->getData('best_offer_reject_value');
    }

    public function getBestOfferRejectSource()
    {
        return array(
            'mode' => $this->getBestOfferRejectMode(),
            'value' => $this->getBestOfferRejectValue(),
            'attribute' => $this->getData('best_offer_reject_attribute')
        );
    }

    public function getBestOfferRejectAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferRejectSource();

        if ($src['mode'] == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getCustomerGroupId()
    {
        return (int)$this->getData('customer_group_id');
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array_unique(array_merge(
            $this->getQtyAttributes(),
            $this->getStartPriceAttributes(),
            $this->getReservePriceAttributes(),
            $this->getBuyItNowPriceAttributes()
        ));
    }

    // #######################################

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

    // #######################################
}