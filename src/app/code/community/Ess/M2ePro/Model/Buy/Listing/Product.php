<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing_Product extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    const IS_VARIATION_PRODUCT_NO   = 0;
    const IS_VARIATION_PRODUCT_YES  = 1;

    const IS_VARIATION_MATCHED_NO  = 0;
    const IS_VARIATION_MATCHED_YES = 1;

    const GENERAL_ID_SEARCH_STATUS_SET_MANUAL  = 1;
    const GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC  = 2;

    const SKU_MAX_LENGTH = 30;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Product');
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
     * @return Ess_M2ePro_Model_Buy_Listing
     */
    public function getBuyListing()
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
     * @return Ess_M2ePro_Model_Buy_Account
     */
    public function getBuyAccount()
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
     * @return Ess_M2ePro_Model_Buy_Marketplace
     */
    public function getBuyMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getBuyListing()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_SellingFormat
     */
    public function getBuySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getBuyListing()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Synchronization
     */
    public function getBuySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    // ########################################

    public function getTemplateNewProductId()
    {
        return $this->getData('template_new_product_id');
    }

    //-----------------------------------------

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

    //-----------------------------------------

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getGeneralId()
    {
        return (int)$this->getData('general_id');
    }

    //-----------------------------------------

    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function getCondition()
    {
        return (int)$this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    //-----------------------------------------

    public function getShippingStandardRate()
    {
        return $this->getData('shipping_standard_rate');
    }

    public function getShippingExpeditedMode()
    {
        return (int)$this->getData('shipping_expedited_mode');
    }

    public function getShippingExpeditedRate()
    {
        return $this->getData('shipping_expedited_rate');
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
        $src = $this->getBuyListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_NOT_SET) {
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
        $src = $this->getBuyListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
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

        $result = '';
        $src = $this->getBuyListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        } else {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);

            $temp = array(Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_ISBN,
                          Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_WORLDWIDE);

            if (in_array($src['mode'], $temp)) {
                $result = str_replace('-','',$result);
            }
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_general_id',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingCondition()
    {
        $temp = $this->getData('cache_adding_condition');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 1;
        $src = $this->getBuyListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_DEFAULT) {
            $result = (int)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = (int)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result)) {
            $result < 0  && $result = 0;
            $result > 10  && $result = 10;
        }

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
        $src = $this->getBuyListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_NOTE_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim(str_replace(array("\r","\n","\t"), '', $result));
        $this->setData('cache_adding_condition_note',$result);

        return $result;
    }

    // ########################################

    public function getAddingShippingExpeditedMode()
    {
        $temp = $this->getData('cache_adding_shipping_expedited_mode');

        if (!empty($temp)) {
            return $temp;
        }

        $src = $this->getBuyListing()->getShippingExpeditedModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        } else {
            $result = (int)($src['mode'] != Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED);
        }

        $this->setData('cache_adding_shipping_expedited_mode',$result);

        return $result;
    }

    public function getAddingShippingOneDayMode()
    {
        $temp = $this->getData('cache_adding_shipping_one_day_mode');

        if (!empty($temp)) {
            return $temp;
        }

        $src = $this->getBuyListing()->getShippingOneDayModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        } else {
            $result = (int)($src['mode'] != Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED);
        }

        $this->setData('cache_adding_shipping_one_day_mode',$result);

        return $result;
    }

    public function getAddingShippingTwoDayMode()
    {
        $temp = $this->getData('cache_adding_shipping_two_day_mode');

        if (!empty($temp)) {
            return $temp;
        }

        $src = $this->getBuyListing()->getShippingTwoDayModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        } else {
            $result = (int)($src['mode'] != Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED);
        }

        $this->setData('cache_adding_shipping_two_day_mode',$result);

        return $result;
    }

    //-----------------------------------------

    public function getAddingShippingStandardRate()
    {
        $temp = $this->getData('cache_adding_shipping_standard_rate');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 0;
        $src = $this->getBuyListing()->getShippingStandardModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        $this->setData('cache_adding_shipping_standard_rate',$result);

        return $result;
    }

    public function getAddingShippingExpeditedRate()
    {
        $temp = $this->getData('cache_adding_shipping_expedited_rate');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 0;
        $src = $this->getBuyListing()->getShippingExpeditedModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        $this->setData('cache_adding_shipping_expedited_rate',$result);

        return $result;
    }

    public function getAddingShippingOneDayRate()
    {
        $temp = $this->getData('cache_adding_shipping_one_day_rate');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 0;
        $src = $this->getBuyListing()->getShippingOneDayModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        $this->setData('cache_adding_shipping_one_day_rate',$result);

        return $result;
    }

    public function getAddingShippingTwoDayRate()
    {
        $temp = $this->getData('cache_adding_shipping_two_day_rate');

        if (!empty($temp)) {
            return $temp;
        }

        $result = 0;
        $src = $this->getBuyListing()->getShippingTwoDayModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        $this->setData('cache_adding_shipping_two_day_rate',$result);

        return $result;
    }

    // ########################################

    public function getPrice()
    {
        $price = 0;

        $src = $this->getBuySellingFormatTemplate()->getPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        if ($this->isVariationsReady()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPrice();
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);
        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '')
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                    $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                }
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $productPrice = Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_PRODUCT;
                    $price = $this->getBaseGroupedProductPrice($productPrice);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                    $price = $this->getBuyListing()->convertPriceFromStoreToMarketplace($price);
                }
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType)
    {
        $price = 0;

        foreach ($this->getMagentoProduct()->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;

            /** @var $tempProduct Ess_M2ePro_Model_Magento_Product */
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    $tempPrice = $this->getBuyListing()->convertPriceFromStoreToMarketplace($tempPrice);
                    break;
                case Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_SPECIAL:
                    $tempPrice = $tempProduct->getSpecialPrice();
                    $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    $tempPrice = $this->getBuyListing()->convertPriceFromStoreToMarketplace($tempPrice);
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
        $src = $this->getBuySellingFormatTemplate()->getQtySource();

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
            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $qty = (int)$this->getMagentoProduct()->getQty(false);
                break;

            default:
            case Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty(true);
                break;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT) {

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
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Product_Dispatcher');
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct
    */
    public function getTemplateNewProduct()
    {
        return Mage::getModel('M2ePro/Buy_Template_NewProduct')->loadInstance((int)$this->getTemplateNewProductId());
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct_Source
    */
    public function getTemplateNewProductSource()
    {
        return $this->getTemplateNewProduct()->getSource($this);
    }

    // ########################################

    public function getBuyItem()
    {
        return Mage::getModel('M2ePro/Buy_Item')->getCollection()
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
        $variationId = Mage::helper('M2ePro/Component_Buy')->getModel('Listing_Product_Variation')
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
            Mage::helper('M2ePro/Component_Buy')->getModel('Listing_Product_Variation_Option')
                                                   ->addData($dataForAdd)
                                                   ->save();
        }

        $this->updateVariationOptions($options);
        $this->setData('is_variation_matched',self::IS_VARIATION_MATCHED_YES)->save();

        if ($this->getParentObject()->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return;
        }

        $oldItems = Mage::getModel('M2ePro/Buy_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Buy_Item */
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

        Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save();
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

        $oldItems = Mage::getModel('M2ePro/Buy_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Buy_Item */
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
