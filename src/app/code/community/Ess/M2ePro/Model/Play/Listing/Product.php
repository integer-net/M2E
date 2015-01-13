<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Play_Listing_Product extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    const IS_VARIATION_PRODUCT_NO   = 0;
    const IS_VARIATION_PRODUCT_YES  = 1;

    const IS_VARIATION_MATCHED_NO  = 0;
    const IS_VARIATION_MATCHED_YES = 1;

    const GENERAL_ID_SEARCH_STATUS_SET_MANUAL  = 1;
    const GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC  = 2;

    const SKU_MAX_LENGTH = 26;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Listing_Product');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getActualMagentoProduct()
    {
        if (!$this->isVariationsReady()) {
            return $this->getMagentoProduct();
        }

        if ($this->getMagentoProduct()->isConfigurableType() ||
            $this->getMagentoProduct()->isGroupedType()) {

            $variations = $this->getVariations(true);
            $variation  = reset($variations);
            $options    = $variation->getOptions(true);
            $option     = reset($options);

            return $option->getMagentoProduct();
        }

        return $this->getMagentoProduct();
    }

    //-----------------------------------------

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

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getPlayListing()->getSellingFormatTemplate();
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
        return $this->getPlayListing()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Synchronization
     */
    public function getPlaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    // ########################################

    public function isVariationProduct()
    {
        return (int)($this->getData('is_variation_product')) == self::IS_VARIATION_PRODUCT_YES;
    }

    public function isVariationMatched()
    {
        return (int)($this->getData('is_variation_matched')) == self::IS_VARIATION_MATCHED_YES;
    }

    public function isVariationsReady()
    {
        return $this->isVariationProduct() && $this->isVariationMatched();
    }

    // ########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    //-----------------------------------------

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    public function getGeneralIdType()
    {
        return $this->getData('general_id_type');
    }

    //-----------------------------------------

    public function getPlayListingId()
    {
        return (int)$this->getData('play_listing_id');
    }

    //-----------------------------------------

    public function getLinkInfo()
    {
        return $this->getData('link_info');
    }

    //-----------------------------------------

    public function getDispatchTo()
    {
        return $this->getData('dispatch_to');
    }

    public function getDispatchFrom()
    {
        return $this->getData('dispatch_from');
    }

    //-----------------------------------------

    public function getOnlinePriceGbr()
    {
        return (float)$this->getData('online_price_gbr');
    }

    public function getOnlinePriceEuro()
    {
        return (float)$this->getData('online_price_euro');
    }

    //-----------------------------------------

    public function getOnlineShippingPriceGbr()
    {
        return (float)$this->getData('online_shipping_price_gbr');
    }

    public function getOnlineShippingPriceEuro()
    {
        return (float)$this->getData('online_shipping_price_euro');
    }

    //-----------------------------------------

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function getCondition()
    {
        return $this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    //-----------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    //-----------------------------------------

    public function isIgnoreNextInventorySynch()
    {
        return (bool)$this->getData('ignore_next_inventory_synch');
    }

    // ########################################

    public function getGeneralIdSearchStatus()
    {
        return (int)$this->getData('general_id_search_status');
    }

    public function isGeneralIdSearchStatusSetManual()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_SET_MANUAL;
    }

    public function isGeneralIdSearchStatusSetAutomatic()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;
    }

    //-----------------------------------------

    public function getGeneralIdSearchSuggestData()
    {
        $temp = $this->getData('general_id_search_suggest_data');
        return is_null($temp) ? array() : json_decode($temp,true);
    }

    // ########################################

    public function getAddingSku()
    {
        $src = $this->getPlayListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_NOT_SET) {
            return NULL;
        }

        if ($this->isVariationsReady()) {
            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);
            return $variation->getChildObject()->getSku();
        }

        return $this->getAddingBaseSku();
    }

    public function getAddingBaseSku()
    {
        $temp = $this->getData('cache_adding_sku');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = trim($result);
        $this->setData('cache_adding_sku',$result);

        return $result;
    }

    public function createRandomSku($prefix = 'SKU')
    {
        return substr($prefix . '_' . sha1(rand(0,10000) . microtime(1)),0,self::SKU_MAX_LENGTH);
    }

    //-----------------------------------------

    public function getAddingGeneralId()
    {
        $temp = $this->getData('cache_adding_general_id');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 0;
        $src = $this->getPlayListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        } else {
            $result = (string)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        $this->setData('cache_adding_general_id',$result);

        return $result;
    }

    public function getAddingGeneralIdType()
    {
        $temp = $this->getData('cache_adding_general_id_type');

        if (!empty($temp)) {
            return $temp;
        }

        $result = $this->getPlayListing()->getGeneralIdMode();
        $result == Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_NOT_SET && $result = NULL;

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_general_id_type',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingDispatchTo()
    {
        $temp = $this->getData('cache_adding_dispatch_to');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayListing()->getDispatchToSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
            $result = $this->replaceDispatchToValue($result);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_dispatch_to',$result);

        return $result;
    }

    public function getAddingDispatchFrom()
    {
        $temp = $this->getData('cache_adding_dispatch_from');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayListing()->getDispatchFromSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_FROM_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_FROM_MODE_DEFAULT) {
            $result = $src['value'];
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_dispatch_from',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingCondition()
    {
        $temp = $this->getData('cache_adding_condition');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
            $result = $this->replaceConditionValue($result);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_condition',$result);

        return $result;
    }

    public function getAddingConditionNote()
    {
        $temp = $this->getData('cache_adding_condition_note');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getPlayListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_NOTE_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim(str_replace(array("\r","\n","\t"), '', $result));
        $this->setData('cache_adding_condition_note',$result);

        return $result;
    }

    // ########################################

    public function getShippingPriceGbr()
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_EUROPA) {
            return $price;
        }

        $src = $this->getPlayListing()->getShippingPriceGbrSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_NONE) {
            return $price;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE) {
            $price = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE) {
            $price = (float)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $price < 0 && $price = 0;

        return round($price,2);
    }

    public function getShippingPriceEuro()
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_UK) {
            return $price;
        }

        $src = $this->getPlayListing()->getShippingPriceEuroSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_NONE) {
            return $price;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE) {
            $price = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE) {
            $price = (float)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $price < 0 && $price = 0;

        return round($price,2);
    }

    // ########################################

    public function getPriceGbr($includeShippingPrice = true)
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_EUROPA) {
            return $price;
        }

        $src = $this->getPlaySellingFormatTemplate()->getPriceGbrSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        if ($this->isVariationsReady()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPriceGbr($includeShippingPrice);
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute'],
                                            Ess_M2ePro_Helper_Component_Play::CURRENCY_GBP);
        $price = Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);

        if ($includeShippingPrice) {
            $price += $this->getShippingPriceGbr();
        }

        return round($price,2);
    }

    public function getPriceEuro($includeShippingPrice = true)
    {
        $price = 0;

        $dispatchTo = $this->getAddingDispatchTo();
        is_null($dispatchTo) && $dispatchTo = $this->getDispatchTo();

        if (is_null($dispatchTo) || $dispatchTo == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_UK) {
            return $price;
        }

        $src = $this->getPlaySellingFormatTemplate()->getPriceEuroSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        if ($this->isVariationsReady()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPriceEuro($includeShippingPrice);
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute'],
                                            Ess_M2ePro_Helper_Component_Play::CURRENCY_EUR);
        $price = Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);

        if ($includeShippingPrice) {
            $price += $this->getShippingPriceEuro();
        }

        return round($price,2);
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '', $currency)
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice, $currency);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                    $price = $this->getPlayListing()->convertPriceFromStoreToMarketplace($price,$currency);
                }
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $productPrice = Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_PRODUCT;
                    $price = $this->getBaseGroupedProductPrice($productPrice, $currency);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                    $price = $this->getPlayListing()->convertPriceFromStoreToMarketplace($price,$currency);
                }
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType, $currency)
    {
        $price = 0;

        foreach ($this->getMagentoProduct()->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;

            /** @var $tempProduct Ess_M2ePro_Model_Magento_Product */
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    $tempPrice = $this->getPlayListing()->convertPriceFromStoreToMarketplace($tempPrice,$currency);
                    break;
                case Ess_M2ePro_Model_Play_Template_SellingFormat::PRICE_SPECIAL:
                    $tempPrice = $tempProduct->getSpecialPrice();
                    $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    $tempPrice = $this->getPlayListing()->convertPriceFromStoreToMarketplace($tempPrice,$currency);
                    break;
            }

            $tempPrice = (float)$tempPrice;

            if ($tempPrice < $price || $price == 0) {
                $price = $tempPrice;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getQty($magentoMode = false)
    {
        $qty = 0;
        $src = $this->getPlaySellingFormatTemplate()->getQtySource();

        if ($this->isVariationsReady()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getQty($magentoMode);
        }

        if ($magentoMode) {
            return (int)$this->getMagentoProduct()->getQty(true);
        }

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $qty = (int)$this->getMagentoProduct()->getQty(false);
                break;

            default:
            case Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty(true);
                break;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_ATTRIBUTE ||
            $src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $src['mode'] == Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT) {

            if ($qty > 0 && $src['qty_percentage'] > 0 && $src['qty_percentage'] < 100) {

                $roundingFunction = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/qty/percentage/','rounding_greater') ? 'ceil' : 'floor';

                $qty = (int)$roundingFunction(($qty/100)*$src['qty_percentage']);
            }

            if ($src['qty_max_posted_value_mode'] && $qty > $src['qty_max_posted_value']) {
                $qty = $src['qty_max_posted_value'];
            }
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    //-----------------------------------------

    protected function replaceConditionValue($value)
    {
        $value = (int)$value;
        $replacementCondition = array(
            1 => Ess_M2ePro_Model_Play_Listing::CONDITION_NEW,
            2 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_LIKE_NEW,
            3 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_VERY_GOOD,
            4 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_GOOD,
            5 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_AVERAGE,
            6 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_LIKE_NEW,
            7 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_VERY_GOOD,
            8 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_GOOD,
            9 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_AVERAGE,
            10 => Ess_M2ePro_Model_Play_Listing::CONDITION_REFURBISHED
        );
        return array_key_exists($value,$replacementCondition) ? $replacementCondition[$value] : $value;
    }

    protected function replaceDispatchToValue($value)
    {
        $value = strtolower(trim($value));
        $replacementDispatchTo = array(
            'uk' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_UK,
            'europe' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_EUROPA,
            'europe_uk' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_BOTH
        );
        return array_key_exists($value,$replacementDispatchTo) ? $replacementDispatchTo[$value] : $value;
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Play_Product_Dispatcher');
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    // ########################################

    public function getPlayItem()
    {
        return Mage::getModel('M2ePro/Play_Item')->getCollection()
                ->addFieldToFilter('account_id', $this->getListing()->getAccountId())
                ->addFieldToFilter('marketplace_id', $this->getListing()->getMarketplaceId())
                ->addFieldToFilter('sku', $this->getSku())
               ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getListing()->getTrackingAttributes();
    }

    // ########################################

    public function setMatchedVariation(array $options)
    {
        $dataForAdd = array(
            'listing_product_id' => $this->getId(),
        );
        $variationId = Mage::helper('M2ePro/Component_Play')->getModel('Listing_Product_Variation')
                                                            ->addData($dataForAdd)
                                                            ->save()
                                                            ->getId();

        foreach ($options as $optionData) {

            $dataForAdd = array(
                'listing_product_variation_id' => $variationId,
                'product_id' => $optionData['product_id'],
                'product_type' => $optionData['product_type'],
                'attribute' => $optionData['attribute'],
                'option' => $optionData['option']
            );
            Mage::helper('M2ePro/Component_Play')->getModel('Listing_Product_Variation_Option')
                                                 ->addData($dataForAdd)
                                                 ->save();
        }

        $this->updateVariationOptions($options);
        $this->setData('is_variation_matched',self::IS_VARIATION_MATCHED_YES)->save();

        if ($this->getParentObject()->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return;
        }

        $oldItems = Mage::getModel('M2ePro/Play_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Play_Item */
        foreach ($oldItems as $oldItem) {
            $oldItem->deleteInstance();
        }

        $dataForAdd = array(
            'account_id' => (int)$this->getListing()->getAccountId(),
            'marketplace_id' => (int)$this->getListing()->getMarketplaceId(),
            'sku' => $this->getSku(),
            'product_id' =>(int)$this->getParentObject()->getProductId(),
            'store_id' => (int)$this->getListing()->getStoreId(),
            'variation_options' => array()
        );

        foreach ($options as $optionData) {
            $dataForAdd['variation_options'][$optionData['attribute']] = $optionData['option'];
        }
        $dataForAdd['variation_options'] = json_encode($dataForAdd['variation_options']);

        Mage::getModel('M2ePro/Play_Item')->setData($dataForAdd)->save();
    }

    // ---------------------------------------

    public function unsetMatchedVariation()
    {
        /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
        foreach ($this->getVariations(true) as $variation) {
            $variation->deleteInstance();
        }

        $this->setData('is_variation_matched',self::IS_VARIATION_MATCHED_NO)
             ->save();

        if ($this->getParentObject()->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return true;
        }

        $oldItems = Mage::getModel('M2ePro/Play_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Play_Item */
        foreach ($oldItems as $oldItem) {
            $oldItem->deleteInstance();
        }

        return true;
    }

    // ########################################

    public function updateVariationOptions(array $options)
    {
        $variationOptions = array();

        foreach ($options as $option) {
            $variationOptions[$option['attribute']] = $option['option'];
        }

        $additionalData = $this->getData('additional_data');
        $additionalData = (array)json_decode($additionalData,true);
        $additionalData = array_filter($additionalData);
        $additionalData['variation_options'] = $variationOptions;

        return $this->getParentObject()->setData('additional_data',json_encode($additionalData))->save();
    }

    // ########################################
}