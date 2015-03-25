<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Listing_Product extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const IS_VARIATION_PRODUCT_NO   = 0;
    const IS_VARIATION_PRODUCT_YES  = 1;

    const IS_VARIATION_MATCHED_NO  = 0;
    const IS_VARIATION_MATCHED_YES = 1;

    const IS_AFN_CHANNEL_NO  = 0;
    const IS_AFN_CHANNEL_YES = 1;

    const IS_ISBN_GENERAL_ID_NO  = 0;
    const IS_ISBN_GENERAL_ID_YES = 1;

    const IS_UPC_WORLDWIDE_ID_NO  = 0;
    const IS_UPC_WORLDWIDE_ID_YES = 1;

    const GENERAL_ID_SEARCH_STATUS_NONE  = 0;
    const GENERAL_ID_SEARCH_STATUS_PROCESSING  = 1;
    const GENERAL_ID_SEARCH_STATUS_SET_MANUAL  = 2;
    const GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC  = 3;

    const SKU_MAX_LENGTH = 40;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product');
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
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
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
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function getAmazonAccount()
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
     * @return Ess_M2ePro_Model_Amazon_Marketplace
     */
    public function getAmazonMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getAmazonListing()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getAmazonSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getAmazonListing()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Synchronization
     */
    public function getAmazonSynchronizationTemplate()
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

    //----------------------------------------

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

    //----------------------------------------

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    public function getWorldwideId()
    {
        return $this->getData('worldwide_id');
    }

    //-----------------------------------------

    public function getGeneralIdSearchStatus()
    {
        return (int)$this->getData('general_id_search_status');
    }

    public function isGeneralIdSearchStatusNone()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_NONE;
    }

    public function isGeneralIdSearchStatusProcessing()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_PROCESSING;
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

    //-----------------------------------------

    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    public function getOnlineSalePrice()
    {
        return $this->getData('online_sale_price');
    }

    public function getOnlineSalePriceStartDate()
    {
        return $this->getData('online_sale_price_start_date');
    }

    public function getOnlineSalePriceEndDate()
    {
        return $this->getData('online_sale_price_end_date');
    }

    //-----------------------------------------

    public function isAfnChannel()
    {
        return (int)$this->getData('is_afn_channel') == self::IS_AFN_CHANNEL_YES;
    }

    public function isIsbnGeneralId()
    {
        return (int)$this->getData('is_isbn_general_id') == self::IS_ISBN_GENERAL_ID_YES;
    }

    public function isUpcWorldwideId()
    {
        return (int)$this->getData('is_upc_worldwide_id') == self::IS_UPC_WORLDWIDE_ID_YES;
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

    public function isIgnoreNextInventorySynch()
    {
        return (bool)$this->getData('ignore_next_inventory_synch');
    }

    // ########################################

    public function getAddingSku()
    {
        $src = $this->getAmazonListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_NOT_SET) {
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
        $src = $this->getAmazonListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
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

        $result = '';
        $src = $this->getAmazonListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_general_id',$result);

        return $result;
    }

    public function getAddingWorldwideId()
    {
        $temp = $this->getData('cache_adding_worldwide_id');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getAmazonListing()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_worldwide_id',$result);

        return $result;
    }

    // ########################################

    public function getCondition()
    {
        $result = '';
        $src = $this->getAmazonListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getConditionNote()
    {
        $result = '';
        $src = $this->getAmazonListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getHandlingTime()
    {
        $result = 0;
        $src = $this->getAmazonListing()->getHandlingTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_NONE) {
            return $result;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = (int)$result;
        $result < 1  && $result = 1;
        $result > 30  && $result = 30;

        return $result;
    }

    public function getRestockDate()
    {
        $result = '';
        $src = $this->getAmazonListing()->getRestockDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    // ########################################

    public function getPrice($returnSalePrice = false)
    {
        $price = 0;

        if ($returnSalePrice) {
            $src = $this->getAmazonSellingFormatTemplate()->getSalePriceSource();
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getPriceSource();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        if ($this->isVariationsReady()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPrice($returnSalePrice);
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute'],$returnSalePrice);
        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    public function getSalePriceInfo()
    {
        $salePrice = $this->getPrice(true);

        if (is_null($salePrice)) {
            return array(
                'price' => null,
                'start_date' => null,
                'end_date' => null
            );
        }

        $startDate = $this->getSalePriceStartDate();
        $endDate = $this->getSalePriceEndDate();

        $startDateTimestamp = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);

        $currentTimestamp = strtotime(Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d 00:00:00'));

        if ($salePrice <= 0 || !$startDate || !$endDate ||
            $currentTimestamp > $endDateTimestamp || $currentTimestamp < $startDateTimestamp ||
            $startDateTimestamp >= $endDateTimestamp) {
            return array(
                'price' => 0,
                'start_date' => null,
                'end_date' => null
            );
        }

        return array(
            'price' => $salePrice,
            'start_date' => $startDate,
            'end_date' => $endDate
        );
    }

    //-----------------------------------------

    private function getSalePriceStartDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial() &&
            $this->getMagentoProduct()->isGroupedType()) {
            $magentoProduct = $this->getActualMagentoProduct();
        } else if ($this->getAmazonSellingFormatTemplate()->isPriceVariationModeParent()) {
            $magentoProduct = $this->getMagentoProduct();
        } else {
            $magentoProduct = $this->getActualMagentoProduct();
        }

        $date = null;

        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial()) {
            $date = $magentoProduct->getSpecialPriceFromDate();
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getSalePriceStartDateSource();

            $date = $src['value'];

            if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
                $date = $magentoProduct->getAttributeValue($src['attribute']);
            }
        }

        if (strtotime($date) === false) {
            return false;
        }

        return Mage::helper('M2ePro')->getDate($date,false,'Y-m-d 00:00:00');
    }

    private function getSalePriceEndDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial() &&
            $this->getMagentoProduct()->isGroupedType()) {
            $magentoProduct = $this->getActualMagentoProduct();
        } else if ($this->getAmazonSellingFormatTemplate()->isPriceVariationModeParent()) {
            $magentoProduct = $this->getMagentoProduct();
        } else {
            $magentoProduct = $this->getActualMagentoProduct();
        }

        $date = null;

        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial()) {

            $date = $magentoProduct->getSpecialPriceToDate();

            $tempDate = new DateTime($date, new DateTimeZone('UTC'));
            $tempDate->modify('-1 day');
            $date = Mage::helper('M2ePro')->getDate($tempDate->format('U'),false,'Y-m-d 00:00:00');

        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getSalePriceEndDateSource();

            $date = $src['value'];

            if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
                $date = $magentoProduct->getAttributeValue($src['attribute']);
            }

            $date = Mage::helper('M2ePro')->getDate($date,false,'Y-m-d 00:00:00');
        }

        if (strtotime($date) === false) {
            return false;
        }

        return $date;
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '',$returnSalePrice = false)
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice, $returnSalePrice);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    if (!$returnSalePrice) {
                        $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                    }
                    $price = $this->getAmazonListing()->convertPriceFromStoreToMarketplace($price);
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $productPrice = Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT;
                    $price = $this->getBaseGroupedProductPrice($productPrice, $returnSalePrice);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                    $price = $this->getAmazonListing()->convertPriceFromStoreToMarketplace($price);
                }
                break;

            default:
                throw new Exception('Unknown mode in database.');
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType, $returnSalePrice = false)
    {
        $price = 0;

        foreach ($this->getMagentoProduct()->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;

            /** @var $tempProduct Ess_M2ePro_Model_Magento_Product */
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    $tempPrice = $this->getAmazonListing()->convertPriceFromStoreToMarketplace($tempPrice);
                    break;
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:

                    $tempPrice = $tempProduct->getSpecialPrice();

                    if ($returnSalePrice) {
                        if ($tempPrice <= 0) {
                            return 0;
                        }
                    } else {
                        $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    }

                    $tempPrice = $this->getAmazonListing()->convertPriceFromStoreToMarketplace($tempPrice);
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
        $src = $this->getAmazonSellingFormatTemplate()->getQtySource();

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
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $qty = (int)$this->getMagentoProduct()->getQty(false);
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty(true);
                break;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE ||
            $src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT) {

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

    public function deleteAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_DELETE, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Product_Dispatcher');
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_NewProduct
    */
    public function getTemplateNewProduct()
    {
        return Mage::getModel('M2ePro/Amazon_Template_NewProduct')->loadInstance(
            (int)$this->getTemplateNewProductId()
        );
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_NewProduct_Source
    */
    public function getTemplateNewProductSource()
    {
        return $this->getTemplateNewProduct()->getSource($this);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Item
    */
    public function getAmazonItem()
    {
        return Mage::getModel('M2ePro/Amazon_Item')->getCollection()
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
            'listing_product_id' => $this->getId()
        );
        $variationId = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Product_Variation')
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
            Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Product_Variation_Option')
                                                   ->addData($dataForAdd)
                                                   ->save();
        }

        $this->updateVariationOptions($options);
        $this->setData('is_variation_matched',self::IS_VARIATION_MATCHED_YES)->save();

        if ($this->getParentObject()->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return;
        }

        $oldItems = Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Amazon_Item */
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

        Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();
    }

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

        $oldItems = Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Amazon_Item */
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