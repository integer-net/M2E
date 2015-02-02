<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product_Variation');
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
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    public function getEbayListing()
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
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    public function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
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
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    public function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getEbayListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getEbayListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getEbayListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        return $this->getEbayListingProduct()->getPaymentTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        return $this->getEbayListingProduct()->getReturnTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        return $this->getEbayListingProduct()->getShippingTemplate();
    }

    // ########################################

    public function getOptions($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getOptions($asObjects,$filters);
    }

    // ########################################

    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    public function getOnlineQtySold()
    {
        return (int)$this->getData('online_qty_sold');
    }

    //----------------------------------------

    public function isAdd()
    {
        return (bool)$this->getData('add');
    }

    public function isDelete()
    {
        return (bool)$this->getData('delete');
    }

    //----------------------------------------

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
    }

    public function isUnknown()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
    }

    public function isBlocked()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
    }

    //-----------------------------------------

    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    public function isHidden()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
    }

    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
    }

    // ########################################

    public function getSku()
    {
        if ($this->isDelete()) {
            return '';
        }

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

        if (strlen($sku) >= 80) {
            $sku = 'RANDOM_'.sha1($sku);
        }

        return $sku;
    }

    public function getQty()
    {
        $qty = 0;

        if ($this->isDelete()) {
            return $qty;
        }

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
            $optionsQtyArray = array();

            // grouping qty by product id
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $optionsQtyArray[$option->getProductId()][] = $option->getChildObject()->getQty();
            }

            foreach ($optionsQtyArray as $optionQty) {
                $optionsQtyList[] = floor($optionQty[0]/count($optionQty));
            }

            $qty = min($optionsQtyList);
        }

        $src = $this->getEbaySellingFormatTemplate()->getQtySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE ||
            $src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT) {

            if ($qty > 0 && $src['qty_percentage'] > 0 && $src['qty_percentage'] < 100) {

                $roundingFunction = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/qty/percentage/','rounding_greater') ? 'ceil' : 'floor';

                $qty = (int)$roundingFunction(($qty/100)*$src['qty_percentage']);
            }

            if ($src['qty_modification_mode'] && $qty < $src['qty_min_posted_value']) {
                $qty = 0;
            }

            if ($src['qty_modification_mode'] && $qty > $src['qty_max_posted_value']) {
                $qty = $src['qty_max_posted_value'];
            }
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ########################################

    public function getPrice()
    {
        $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();
        $price = $this->getBaseProductPrice($src);

        $price = $this->getEbayListingProduct()->increasePriceByVatPercent($price);
        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    public function getPriceDiscountStp()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountStpSource();
        $price = $this->getBaseProductPrice($src);

        return $this->getEbayListingProduct()->increasePriceByVatPercent($price);
    }

    public function getPriceDiscountMap()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapSource();
        $price = $this->getBaseProductPrice($src);

        return $this->getEbayListingProduct()->increasePriceByVatPercent($price);
    }

    // ----------------------------------------

    public function getBaseProductPrice($src)
    {
        $price = 0;

        if ($this->isDelete()) {
            return $price;
        }

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Bundle, Simple with options product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
            $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            if ($this->getEbaySellingFormatTemplate()->isPriceVariationModeParent() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

                // Base Price of Main product.
                $price = $this->getEbayListingProduct()->getBaseProductPrice($src);

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

        return $price;
    }

    // ########################################

    public function hasSales()
    {
        $currentSpecifics = array();

        $options = $this->getOptions(true);
        foreach ($options as $option) {
            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
            $currentSpecifics[$option->getAttribute()] = $option->getOption();
        }

        ksort($currentSpecifics);
        $variationKeys = array_map('trim', array_keys($currentSpecifics));
        $variationValues = array_map('trim', array_values($currentSpecifics));

        $realEbayItemId = $this->getEbayListingProduct()->getEbayItem()->getItemId();

        $tempOrdersItemsCollection = Mage::getModel('M2ePro/Ebay_Order_Item')->getCollection();
        $tempOrdersItemsCollection->addFieldToFilter('item_id', $realEbayItemId);
        $ordersItems = $tempOrdersItemsCollection->getItems();

        $findOrderItem = false;

        foreach ($ordersItems as $orderItem) {

            $variationOrder = $orderItem->getVariation();

            if (empty($variationOrder)) {
                continue;
            }

            ksort($variationOrder);
            $orderItemVariationKeys = array_map('trim', array_keys($variationOrder));
            $orderItemVariationValues = array_map('trim', array_values($variationOrder));

            if (count($currentSpecifics) == count($variationOrder) &&
                count(array_diff($variationKeys,$orderItemVariationKeys)) <= 0 &&
                count(array_diff($variationValues,$orderItemVariationValues)) <= 0) {
                $findOrderItem = true;
                break;
            }
        }

        return $findOrderItem;
    }

    // ########################################
}