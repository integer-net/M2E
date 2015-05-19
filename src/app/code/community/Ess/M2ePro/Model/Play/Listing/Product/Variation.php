<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Play_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Listing_Product_Variation');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Account
     */
    public function getPlayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Marketplace
     */
    public function getPlayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing
     */
    public function getPlayListing()
    {
        return $this->getListing()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getParentObject()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product
     */
    public function getPlayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getPlayListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_SellingFormat
     */
    public function getPlaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getPlayListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Synchronization
     */
    public function getPlaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getOptions($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getOptions($asObjects,$filters);
    }

    // ########################################

    public function getSku()
    {
        $sku = '';

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Grouped product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku = $option->getChildObject()->getSku();
                break;
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku != '' && $sku .= '-';
                $sku .= $option->getChildObject()->getSku();
            }

        // Simple with options product
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku != '' && $sku .= '-';
                $tempSku = $option->getChildObject()->getSku();
                if ($tempSku == '') {
                    $sku .= Mage::helper('M2ePro')->convertStringToSku($option->getOption());
                } else {
                    $sku .= $tempSku;
                }
            }
        }

        return $sku;
    }

    public function getQty($magentoMode = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Play_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Play_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getListingProduct());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getVariationValue($this->getParentObject());
    }

    // ########################################

    public function getPriceGbr($includeShippingPrice = true)
    {
        $src = $this->getPlaySellingFormatTemplate()->getPriceGbrSource();
        $price = $this->getCalculatedPrice($src, Ess_M2ePro_Helper_Component_Play::CURRENCY_GBP, true);

        if ($includeShippingPrice) {
            $price += $this->getPlayListingProduct()->getListingSource()->getShippingPriceGbr();
        }

        return round($price,2);
    }

    public function getPriceEuro($includeShippingPrice = true)
    {
        $src = $this->getPlaySellingFormatTemplate()->getPriceEuroSource();
        $price = $this->getCalculatedPrice($src, Ess_M2ePro_Helper_Component_Play::CURRENCY_EUR, true);

        if ($includeShippingPrice) {
            $price += $this->getPlayListingProduct()->getListingSource()->getShippingPriceEuro();
        }

        return round($price,2);
    }

    // ----------------------------------------

    private function getCalculatedPrice($src, $currency, $modifyByCoefficient = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Play_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Play_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setCurrency($currency)->setModifyByCoefficient($modifyByCoefficient);

        return $calculator->getVariationValue($this->getParentObject());
    }

    // ########################################
}