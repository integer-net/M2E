<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
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

    const TRIED_TO_LIST_YES = 1;
    const TRIED_TO_LIST_NO  = 0;

    const SKU_MAX_LENGTH = 40;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product');
    }

    // ########################################

    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

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
        if (!$this->isVariationProduct() || !$this->isVariationMatched()) {
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

    public function getGeneralTemplate()
    {
        return $this->getParentObject()->getGeneralTemplate();
    }

    public function getSellingFormatTemplate()
    {
        return $this->getParentObject()->getSellingFormatTemplate();
    }

    public function getDescriptionTemplate()
    {
        return $this->getParentObject()->getDescriptionTemplate();
    }

    public function getSynchronizationTemplate()
    {
        return $this->getParentObject()->getSynchronizationTemplate();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_General
     */
    public function getAmazonGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getAmazonSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
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

    public function getOnlineSalePrice()
    {
        return $this->getData('online_sale_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
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

    //-----------------------------------------

    public function isTriedToList()
    {
        return $this->getData('tried_to_list') == self::TRIED_TO_LIST_YES;
    }

    // ########################################

    public function getAddingSku()
    {
        $src = $this->getAmazonGeneralTemplate()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_NOT_SET) {
            return NULL;
        }

        if ($this->isVariationProduct() && $this->isVariationMatched()) {
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
        $src = $this->getAmazonGeneralTemplate()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_PRODUCT_ID) {
            $result = $this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_CUSTOM_ATTRIBUTE) {
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

    // ---------------------------------------

    public function getAddingGeneralId()
    {
        $temp = $this->getData('cache_adding_general_id');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
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
        $src = $this->getAmazonGeneralTemplate()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::WORLDWIDE_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_worldwide_id',$result);

        return $result;
    }

    // ########################################

    public function getCondition()
    {
        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_MODE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getConditionNote()
    {
        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_NOTE_MODE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getHandlingTime()
    {
        $result = 0;
        $src = $this->getAmazonGeneralTemplate()->getHandlingTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::HANDLING_TIME_MODE_NONE) {
            return $result;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::HANDLING_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
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
        $src = $this->getAmazonGeneralTemplate()->getRestockDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
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

        if ($this->isVariationProduct() && $this->isVariationMatched()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPrice($returnSalePrice);
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute'],$returnSalePrice);
        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    public function getSalePrice()
    {
        return $this->getPrice(true);
    }

    //-----------------------------------------

    public function getSalePriceStartDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial()) {
            return $this->getActualMagentoProduct()->getSpecialPriceFromDate();
        }

        $src = $this->getAmazonSellingFormatTemplate()->getSalePriceStartDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
            return $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getSalePriceEndDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial()) {

            $date = $this->getActualMagentoProduct()->getSpecialPriceToDate();

            $tempDate = new DateTime($date, new DateTimeZone('UTC'));
            $tempDate->modify('-1 day');
            $date = Mage::helper('M2ePro')->getDate($tempDate->format('U'));

            return $date;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getSalePriceEndDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
            return $this->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '',$returnSalePrice = false)
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice, $returnSalePrice);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    if (!$returnSalePrice) {
                        $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                    }
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_FINAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(
                        Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_FINAL,
                        $returnSalePrice
                    );
                } else {
                    $customerGroupId = $this->getAmazonSellingFormatTemplate()->getCustomerGroupId();
                    $price = $this->getMagentoProduct()->getFinalPrice($customerGroupId);
                }
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $productPrice = Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT;
                    $price = $this->getBaseGroupedProductPrice($productPrice, $returnSalePrice);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                }
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType, $returnSalePrice = false)
    {
        $price = 0;

        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;

            /** @var $tempProduct Ess_M2ePro_Model_Magento_Product */
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:
                    if ($returnSalePrice) {
                        $tempPrice = $tempProduct->getProduct()->getSpecialPrice();
                    } else {
                        $tempPrice = $tempProduct->getSpecialPrice();
                        $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    }
                    break;
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_FINAL:
                    $tempProduct = Mage::getModel('M2ePro/Magento_Product')
                                            ->setProductId($tempProduct->getProductId())
                                            ->setStoreId($this->getListing()->getStoreId());
                    $customerGroupId = $this->getAmazonSellingFormatTemplate()->getCustomerGroupId();
                    $tempPrice = $tempProduct->getFinalPrice($customerGroupId);
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

    public function getQty($productMode = false)
    {
        if ($this->isVariationMatched() && $this->isVariationProduct()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return (int)floor($variation->getChildObject()->getQty());
        }

        $qty = 0;
        $src = $this->getAmazonSellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_SINGLE:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = 1;
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_NUMBER:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = $src['value'];
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = $this->_getProductGeneralQty();
                break;
        }

        //-- Check max posted QTY on channel
        if ($src['qty_max_posted_value'] > 0 && $qty > $src['qty_max_posted_value']) {
            $qty = $src['qty_max_posted_value'];
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    //-----------------------------------------

    protected function _getProductGeneralQty()
    {
        if ($this->getMagentoProduct()->isStrictVariationProduct()) {
            return $this->getParentObject()->_getOnlyVariationProductQty();
        }
        return (int)floor($this->getMagentoProduct()->getQty());
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_STOP, $params);
    }

    public function deleteAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_DELETE, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getProductDispatcher();
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

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_NewProduct_Source
    */
    public function getTemplateNewProductSource()
    {
        return $this->getTemplateNewProduct()->getSource($this);
    }

    // ########################################

    public function getChangedItems(array $attributes,
                                    $withStoreFilter = false)
    {
        return Mage::getModel('M2ePro/Listing_Product')->getChangedItems(
            $attributes,
            $this->getComponentMode(),
            $withStoreFilter,
            array($this,'dbSelectModifier')
        );
    }

    public function getChangedItemsByListingProduct(array $attributes,
                                                    $withStoreFilter = false)
    {
        return Mage::getModel('M2ePro/Listing_Product')->getChangedItemsByListingProduct(
            $attributes,
            $this->getComponentMode(),
            $withStoreFilter,
            array($this,'dbSelectModifier')
        );
    }

    public function getChangedItemsByVariationOption(array $attributes,
                                                     $withStoreFilter = false)
    {
        return Mage::getModel('M2ePro/Listing_Product')->getChangedItemsByVariationOption(
            $attributes,
            $this->getComponentMode(),
            $withStoreFilter,
            array($this,'dbSelectModifier')
        );
    }

    // --------------------------------------------------

    public function dbSelectModifier(Varien_Db_Select $dbSelect) {

        $dbSelect->join(
            array('alp' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable()),
            '`lp`.`id` = `alp`.`listing_product_id`',
            array()
        );

        $dbSelect->where(
            '`alp`.`is_variation_product` = '.self::IS_VARIATION_PRODUCT_NO.
            ' OR ('.
                '`alp`.`is_variation_product` = '.self::IS_VARIATION_PRODUCT_YES.
                ' AND `alp`.`is_variation_matched` = '.self::IS_VARIATION_MATCHED_YES.
            ')'
        );

    }

    // ########################################

    public function setMatchedVariation(array $options)
    {
        $dataForAdd = array(
            'listing_product_id' => $this->getId(),
            'add' => Ess_M2ePro_Model_Listing_Product_Variation::ADD_NO,
            'delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO,
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
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

        $this->setData('is_variation_matched',self::IS_VARIATION_MATCHED_YES)->save();
        $this->updateVariationOptions($options);

        if ($this->getParentObject()->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return;
        }

        $oldItems = Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getGeneralTemplate()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getGeneralTemplate()->getMarketplaceId())
                                ->addFieldToFilter('sku',$this->getSku())
                                ->addFieldToFilter('product_id',$this->getParentObject()->getProductId())
                                ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                                ->getItems();

        /* @var $oldItem Ess_M2ePro_Model_Amazon_Item */
        foreach ($oldItems as $oldItem) {
            $oldItem->deleteInstance();
        }

        $dataForAdd = array(
            'account_id' => (int)$this->getListing()->getGeneralTemplate()->getAccountId(),
            'marketplace_id' => (int)$this->getListing()->getGeneralTemplate()->getMarketplaceId(),
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

        $this->setData('is_variation_matched',self::IS_VARIATION_MATCHED_NO)->save();

        if ($this->getParentObject()->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return true;
        }

        $oldItems = Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                                ->addFieldToFilter('account_id',$this->getGeneralTemplate()->getAccountId())
                                ->addFieldToFilter('marketplace_id',$this->getGeneralTemplate()->getMarketplaceId())
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

        return $this->setData('additional_data',json_encode($additionalData))->save();
    }

    // ########################################
}