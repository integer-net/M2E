<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
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
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getParentObject()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getParentObject()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getParentObject()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getParentObject()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getParentObject()->getSynchronizationTemplate();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Play_Listing
     */
    public function getPlayListing()
    {
        return $this->getListing()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product
     */
    public function getPlayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_General
     */
    public function getPlayGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_SellingFormat
     */
    public function getPlaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Description
     */
    public function getPlayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
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

        if (strlen($sku) <= Ess_M2ePro_Model_Play_Listing_Product::SKU_MAX_LENGTH) {
            return $sku;
        }

        $parentSku = $this->getListingProduct()->getChildObject()->getAddingBaseSku();

        if (strlen($parentSku) < (Ess_M2ePro_Model_Play_Listing_Product::SKU_MAX_LENGTH - 5)) {
            return $this->getListingProduct()->getChildObject()->createRandomSku($parentSku);
        }

        return $this->getListingProduct()->getChildObject()->createRandomSku(
            'SKU_' . $this->getListingProduct()->getProductId() . '_' . $this->getListingProduct()->getId()
        );
    }

    public function getQty()
    {
        $qty = 0;

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Grouped, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $qty = $option->getChildObject()->getQty();
                break;
            }

        // Bundle product
        } else {

            $optionsQtyList = array();
            foreach ($options as $option) {
               /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
               $optionsQtyList[] = $option->getChildObject()->getQty();
            }

            $qty = min($optionsQtyList);
        }

        //-- Check max posted QTY on channel
        $src = $this->getPlaySellingFormatTemplate()->getQtySource();
        if ($src['qty_max_posted_value'] > 0 && $qty > $src['qty_max_posted_value']) {
            $qty = $src['qty_max_posted_value'];
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ########################################

    public function getPriceGbr($includeShippingPrice = true)
    {
        $price = $this->getPrice(
            $this->getPlaySellingFormatTemplate()->getPriceGbrSource(),
            $includeShippingPrice
        );

        if ($includeShippingPrice) {
            $price += $this->getPlayListingProduct()->getShippingPriceGbr();
        }

        return round($price,2);
    }

    public function getPriceEuro($includeShippingPrice = true)
    {
        $price = $this->getPrice(
            $this->getPlaySellingFormatTemplate()->getPriceEuroSource(),
            $includeShippingPrice
        );

        if ($includeShippingPrice) {
            $price += $this->getPlayListingProduct()->getShippingPriceEuro();
        }

        return round($price,2);
    }

    // ########################################

    private function getPrice($src)
    {
        $price = 0;

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Bundle, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            if ($this->getPlaySellingFormatTemplate()->isPriceVariationModeParent() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

                // Base Price of Main product.
                $price = $this->getPlayListingProduct()->getBaseProductPrice(
                    $src['mode'],$src['attribute']
                );

                foreach ($options as $option) {
                    /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                    $price += $option->getChildObject()->getPrice($src);
                }

            } else {

                $isBundle = $this->getListingProduct()->getMagentoProduct()->isBundleType();

                foreach ($options as $option) {

                    /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                    if ($isBundle) {
                        $price += $option->getChildObject()->getPrice($src);
                        continue;
                    }

                    $price = $option->getChildObject()->getPrice($src);
                    break;
                }
            }

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $price = $option->getChildObject()->getPrice($src);
                break;
            }
        }

        $price < 0 && $price = 0;

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    // ########################################
}