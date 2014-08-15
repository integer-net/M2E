<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    const LISTING_TYPE_AUCTION  = 'Chinese';
    const LISTING_TYPE_FIXED    = 'FixedPriceItem';

    const PRICE_DISCOUNT_MAP_EXPOSURE_NONE             = 'None';
    const PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT  = 'DuringCheckout';
    const PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT     = 'PreCheckout';

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplate = NULL;

    // ########################################

    public function getData()
    {
        $data = array();

        if ($this->getConfigurator()->isGeneral()) {

            $data = array_merge(
                $this->getGeneralData(),
                $this->getVatTaxData(),
                $this->getCharityData()
            );
        }

        return array_merge(
            $data,
            $this->getQtyData(),
            $this->getPriceData(),
            $this->getPriceDiscountStpData(),
            $this->getPriceDiscountMapData()
        );
    }

    // ########################################

    public function getGeneralData()
    {
        $data = array(
            'duration' => $this->getEbayListingProduct()->getDuration(),
            'is_private' => $this->getEbaySellingFormatTemplate()->isPrivateListing(),
            'currency' => $this->getEbayMarketplace()->getCurrency(),
            'out_of_stock_control' => $this->getEbaySellingFormatTemplate()->getOutOfStockControl()
        );

        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
            $data['listing_type'] = self::LISTING_TYPE_FIXED;
        } else {
            $data['listing_type'] = self::LISTING_TYPE_AUCTION;
        }

        return $data;
    }

    public function getVatTaxData()
    {
        $data = array(
            'tax_category' => $this->getEbaySellingFormatTemplate()->getTaxCategory()
        );

        if ($this->getEbayMarketplace()->isVatEnabled()) {
            $data['vat_percent'] = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        if ($this->getEbayMarketplace()->isTaxTableEnabled()) {
            $data['use_tax_table'] = $this->getEbaySellingFormatTemplate()->isTaxTableEnabled();
        }

        return $data;
    }

    public function getCharityData()
    {
        $charity = $this->getEbaySellingFormatTemplate()->getCharity();

        if (is_null($charity)) {
            return array();
        }

        return array(
            'charity_id' => $charity['id'],
            'charity_percent' => $charity['percentage']
        );
    }

    // ----------------------------------------

    public function getQtyData()
    {
        if (!$this->getConfigurator()->isQty() ||
            $this->getIsVariationItem()) {
            return array();
        }

        return array(
            'qty' => $this->getEbayListingProduct()->getQty()
        );
    }

    public function getPriceData()
    {
        if (!$this->getConfigurator()->isPrice() ||
            $this->getIsVariationItem()) {
            return array();
        }

        $data = array();

        if ($this->getEbayListingProduct()->isListingTypeFixed()) {

            $data['price_fixed'] = $this->getEbayListingProduct()->getBuyItNowPrice();

            $data['bestoffer_mode'] = $this->getEbaySellingFormatTemplate()->isBestOfferEnabled();

            if ($data['bestoffer_mode']) {
                $data['bestoffer_accept_price'] = $this->getEbayListingProduct()->getBestOfferAcceptPrice();
                $data['bestoffer_reject_price'] = $this->getEbayListingProduct()->getBestOfferRejectPrice();
            }

        } else {
            $data['price_start'] = $this->getEbayListingProduct()->getStartPrice();
            $data['price_reserve'] = $this->getEbayListingProduct()->getReservePrice();
            $data['price_buyitnow'] = $this->getEbayListingProduct()->getBuyItNowPrice();
        }

        return $data;
    }

    public function getPriceDiscountStpData()
    {
        if (!$this->getConfigurator()->isPrice() ||
            $this->getIsVariationItem()) {
            return array();
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getEbayListingProduct()->isPriceDiscountStp()) {
            return array();
        }

        $data = array(
            'original_retail_price' => $this->getEbayListingProduct()->getPriceDiscountStp()
        );

        if ($this->getEbayMarketplace()->isStpAdvancedEnabled()) {
            $data = array_merge(
                $data,
                $this->getEbaySellingFormatTemplate()->getPriceDiscountStpAdditionalFlags()
            );
        }

        return array('price_discount_stp' => $data);
    }

    public function getPriceDiscountMapData()
    {
        if (!$this->getConfigurator()->isPrice() ||
            $this->getIsVariationItem()) {
            return array();
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getEbayListingProduct()->isPriceDiscountMap()) {
            return array();
        }

        $data = array(
            'minimum_advertised_price' => $this->getEbayListingProduct()->getPriceDiscountMap(),
        );

        $exposure = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapExposureType();
        $data['minimum_advertised_price_exposure'] =
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::
                getPriceDiscountMapExposureType($exposure);

        return array('price_discount_map' => $data);
    }

    public static function getPriceDiscountMapExposureType($type)
    {
        switch ($type) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_NONE:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT;

            default:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    private function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplate)) {
            $this->sellingFormatTemplate = $this->getListingProduct()
                                                ->getChildObject()
                                                ->getSellingFormatTemplate();
        }
        return $this->sellingFormatTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    private function getEbaySellingFormatTemplate()
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_SellingFormat $object */
        $object = $this->getSellingFormatTemplate()->getChildObject();
        $object->setMagentoProduct($this->getMagentoProduct());
        return $object;
    }

    // ########################################
}