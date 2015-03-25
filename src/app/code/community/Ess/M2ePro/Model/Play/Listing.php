<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Play_Listing getResource()
 */
class Ess_M2ePro_Model_Play_Listing extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    const SKU_MODE_NOT_SET          = 0;
    const SKU_MODE_PRODUCT_ID       = 3;
    const SKU_MODE_DEFAULT          = 1;
    const SKU_MODE_CUSTOM_ATTRIBUTE = 2;

    const GENERATE_SKU_MODE_NO  = 0;
    const GENERATE_SKU_MODE_YES = 1;

    const GENERAL_ID_MODE_NOT_SET       = '';
    const GENERAL_ID_MODE_GENERAL_ID    = 'PlayID';
    const GENERAL_ID_MODE_ISBN          = 'ISBN';
    const GENERAL_ID_MODE_WORLDWIDE     = 'UPC/EAN';

    const SEARCH_BY_MAGENTO_TITLE_MODE_NONE = 0;
    const SEARCH_BY_MAGENTO_TITLE_MODE_YES  = 1;

    const DISPATCH_TO_MODE_NOT_SET            = 0;
    const DISPATCH_TO_MODE_DEFAULT            = 1;
    const DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE   = 2;

    const DISPATCH_TO_UK     = 'UK Only';
    const DISPATCH_TO_EUROPA = 'Europe Only (not inc. UK)';
    const DISPATCH_TO_BOTH   = 'UK, & Europe';

    const DISPATCH_FROM_MODE_NOT_SET            = 0;
    const DISPATCH_FROM_MODE_DEFAULT            = 1;

    const SHIPPING_PRICE_GBR_MODE_NONE              = 0;
    const SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE      = 1;
    const SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE  = 2;

    const SHIPPING_PRICE_EURO_MODE_NONE              = 0;
    const SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE      = 1;
    const SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE  = 2;

    const CONDITION_MODE_NOT_SET          = 0;
    const CONDITION_MODE_DEFAULT          = 1;
    const CONDITION_MODE_CUSTOM_ATTRIBUTE = 2;

    const CONDITION_NEW                    = 'New';
    const CONDITION_USED_LIKE_NEW          = 'Used; Like New';
    const CONDITION_USED_VERY_GOOD         = 'Used; Very Good';
    const CONDITION_USED_GOOD              = 'Used; Good';
    const CONDITION_USED_AVERAGE           = 'Used; Average';
    const CONDITION_COLLECTABLE_LIKE_NEW   = 'Collectable; Like New';
    const CONDITION_COLLECTABLE_VERY_GOOD  = 'Collectable; Very Good';
    const CONDITION_COLLECTABLE_GOOD       = 'Collectable; Good';
    const CONDITION_COLLECTABLE_AVERAGE    = 'Collectable; Average';
    const CONDITION_REFURBISHED            = 'Refurbished';

    const CONDITION_NOTE_MODE_NOT_SET          = 0;
    const CONDITION_NOTE_MODE_NONE             = 3;
    const CONDITION_NOTE_MODE_CUSTOM_VALUE     = 1;
    const CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Listing');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->sellingFormatTemplateModel = NULL;
        $temp && $this->synchronizationTemplateModel = NULL;
        return $temp;
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
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $this->sellingFormatTemplateModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Template_SellingFormat',
                $this->getData('template_selling_format_id'),NULL,
                array('template')
            );
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->sellingFormatTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->synchronizationTemplateModel)) {
            $this->synchronizationTemplateModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Template_Synchronization',
                $this->getData('template_synchronization_id'),NULL,
                array('template')
            );
        }

        return $this->synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->synchronizationTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Play_Template_SellingFormat
     */
    public function getPlaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Synchronization
     */
    public function getPlaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getAttributeSets()
    {
        return $this->getParentObject()->getAttributeSets();
    }

    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects,$filters);
    }

    public function getCategories($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getCategories($asObjects,$filters);
    }

    // ########################################

    public function getSkuMode()
    {
        return (int)$this->getData('sku_mode');
    }

    public function isSkuNotSetMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_NOT_SET;
    }

    public function isSkuProductIdMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_PRODUCT_ID;
    }

    public function isSkuDefaultMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_DEFAULT;
    }

    public function isSkuAttributeMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getSkuSource()
    {
        return array(
            'mode'      => $this->getSkuMode(),
            'attribute' => $this->getData('sku_custom_attribute')
        );
    }

    //-------------------------

    public function isGenerateSkuModeNo()
    {
        return (int)$this->getData('generate_sku_mode') == self::GENERATE_SKU_MODE_NO;
    }

    public function isGenerateSkuModeYes()
    {
        return (int)$this->getData('generate_sku_mode') == self::GENERATE_SKU_MODE_YES;
    }

    //-------------------------

    public function getGeneralIdMode()
    {
        return $this->getData('general_id_mode');
    }

    public function isGeneralIdNotSetMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_NOT_SET;
    }

    public function isGeneralIdWorldwideMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_WORLDWIDE;
    }

    public function isGeneralIdGeneralIdMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_GENERAL_ID;
    }

    public function isGeneralIdIsbnMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_ISBN;
    }

    public function getGeneralIdSource()
    {
        return array(
            'mode'      => $this->getGeneralIdMode(),
            'attribute' => $this->getData('general_id_custom_attribute')
        );
    }

    //-------------------------

    public function getSearchByMagentoTitleMode()
    {
        return (int)$this->getData('search_by_magento_title_mode');
    }

    public function isSearchByMagentoTitleModeEnabled()
    {
        return $this->getSearchByMagentoTitleMode() == self::SEARCH_BY_MAGENTO_TITLE_MODE_YES;
    }

    //-------------------------

    public function getDispatchToMode()
    {
        return (int)$this->getData('dispatch_to_mode');
    }

    public function isDispatchToNotSetMode()
    {
        return $this->getConditionMode() == self::DISPATCH_TO_MODE_NOT_SET;
    }

    public function isDispatchToDefaultMode()
    {
        return $this->getConditionMode() == self::DISPATCH_TO_MODE_DEFAULT;
    }

    public function isDispatchToAttributeMode()
    {
        return $this->getConditionMode() == self::DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getDispatchToSource()
    {
        return array(
            'mode'      => $this->getDispatchToMode(),
            'value'     => $this->getData('dispatch_to_value'),
            'attribute' => $this->getData('dispatch_to_custom_attribute')
        );
    }

    //-------------------------

    public function getDispatchFromMode()
    {
        return (int)$this->getData('dispatch_from_mode');
    }

    public function isDispatchFromNotSetMode()
    {
        return $this->getConditionMode() == self::DISPATCH_FROM_MODE_NOT_SET;
    }

    public function isDispatchFromDefaultMode()
    {
        return $this->getConditionMode() == self::DISPATCH_FROM_MODE_DEFAULT;
    }

    public function getDispatchFromSource()
    {
        return array(
            'mode'      => $this->getDispatchFromMode(),
            'value'     => $this->getData('dispatch_from_value'),
        );
    }

    //-------------------------

    public function getShippingPriceGbrMode()
    {
        return (int)$this->getData('shipping_price_gbr_mode');
    }

    public function isShippingPriceGbrNoneMode()
    {
        return $this->getShippingPriceGbrMode() == self::SHIPPING_PRICE_GBR_MODE_NONE;
    }

    public function isShippingPriceGbrValueMode()
    {
        return $this->getShippingPriceGbrMode() == self::SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE;
    }

    public function isShippingPriceGbrAttributeMode()
    {
        return $this->getShippingPriceGbrMode() == self::SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingPriceGbrSource()
    {
        return array(
            'mode'      => $this->getShippingPriceGbrMode(),
            'value'     => (float)$this->getData('shipping_price_gbr_value'),
            'attribute' => $this->getData('shipping_price_gbr_custom_attribute')
        );
    }

    public function getShippingPriceGbrAttributes()
    {
        $attributes = array();
        $src = $this->getShippingPriceGbrSource();

        if ($src['mode'] == self::SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getShippingPriceEuroMode()
    {
        return (int)$this->getData('shipping_price_euro_mode');
    }

    public function isShippingPriceEuroNoneMode()
    {
        return $this->getShippingPriceEuroMode() == self::SHIPPING_PRICE_EURO_MODE_NONE;
    }

    public function isShippingPriceEuroValueMode()
    {
        return $this->getShippingPriceEuroMode() == self::SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE;
    }

    public function isShippingPriceEuroAttributeMode()
    {
        return $this->getShippingPriceEuroMode() == self::SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingPriceEuroSource()
    {
        return array(
            'mode'      => $this->getShippingPriceEuroMode(),
            'value'     => (float)$this->getData('shipping_price_euro_value'),
            'attribute' => $this->getData('shipping_price_euro_custom_attribute')
        );
    }

    public function getShippingPriceEuroAttributes()
    {
        $attributes = array();
        $src = $this->getShippingPriceEuroSource();

        if ($src['mode'] == self::SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getConditionMode()
    {
        return (int)$this->getData('condition_mode');
    }

    public function isConditionNotSetMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_NOT_SET;
    }

    public function isConditionDefaultMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_DEFAULT;
    }

    public function isConditionAttributeMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getConditionSource()
    {
        return array(
            'mode'      => $this->getConditionMode(),
            'value'     => $this->getData('condition_value'),
            'attribute' => $this->getData('condition_custom_attribute')
        );
    }

    public function getConditionValues()
    {
        $temp = $this->getData('cache_condition_values');

        if (!empty($temp)) {
            return $temp;
        }

        $reflectionClass = new ReflectionClass (__CLASS__);
        $tempConstants = $reflectionClass->getConstants();

        $values = array();
        foreach ($tempConstants as $key => $value) {
            $prefixKey = strtolower(substr($key,0,14));
            if (substr($prefixKey,0,10) != 'condition_' ||
                in_array($prefixKey,array('condition_mode','condition_note'))) {
                continue;
            }
            $values[] = $value;
        }

        $this->setData('cache_condition_values',$values);

        return $values;
    }

    //-------------------------

    public function getConditionNoteMode()
    {
        return (int)$this->getData('condition_note_mode');
    }

    public function isConditionNoteNotSetMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_NOT_SET;
    }

    public function isConditionNoteNoneMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_NONE;
    }

    public function isConditionNoteValueMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_CUSTOM_VALUE;
    }

    public function isConditionNoteAttributeMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getConditionNoteSource()
    {
        return array(
            'mode'      => $this->getConditionNoteMode(),
            'value'     => $this->getData('condition_note_value'),
            'attribute' => $this->getData('condition_note_custom_attribute')
        );
    }

    // ########################################

    public function convertPriceFromStoreToMarketplace($price, $currency)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPrice(
            $price,
            $currency,
            $this->getParentObject()->getStoreId()
        );
    }

    public function addProductFromOther(Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
                                        $checkingMode = false,
                                        $checkHasProduct = true)
    {
        if (!$listingOtherProduct->getProductId()) {
            return false;
        }

        $productId = $listingOtherProduct->getProductId();
        $result = $this->getParentObject()->addProduct($productId, $checkingMode, $checkHasProduct);

        if ($checkingMode) {
            return $result;
        }

        if (!($result instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $listingProduct = $result;

        $listingProduct->getChildObject()
                       ->getPlayItem()
                       ->setData('store_id',$this->getParentObject()->getStoreId())
                       ->save();

        $dataForUpdate = array(
            'general_id' => $listingOtherProduct->getChildObject()->getGeneralId(),
            'play_listing_id' => $listingOtherProduct->getChildObject()->getPlayListingId(),
            'link_info' => $listingOtherProduct->getChildObject()->getLinkInfo(),
            'general_id_type' => $listingOtherProduct->getChildObject()->getGeneralIdType(),
            'sku' => $listingOtherProduct->getChildObject()->getSku(),
            'online_price_gbr' => $listingOtherProduct->getChildObject()->getOnlinePriceGbr(),
            'online_price_euro' => $listingOtherProduct->getChildObject()->getOnlinePriceEuro(),
            'online_shipping_price_gbr' => 0,
            'online_shipping_price_euro' => 0,
            'online_qty' => $listingOtherProduct->getChildObject()->getOnlineQty(),
            'condition' => $listingOtherProduct->getChildObject()->getCondition(),
            'condition_note' => $listingOtherProduct->getChildObject()->getConditionNote(),
            'dispatch_to' => $listingOtherProduct->getChildObject()->getDispatchTo(),
            'dispatch_from' => $listingOtherProduct->getChildObject()->getDispatchFrom(),
            'start_date' => $listingOtherProduct->getChildObject()->getStartDate(),
            'end_date' => $listingOtherProduct->getChildObject()->getEndDate(),
            'status' => $listingOtherProduct->getStatus(),
            'status_changer' => $listingOtherProduct->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate)->save();

        return $listingProduct;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array_unique(array_merge(
            $this->getShippingPriceGbrAttributes(),
            $this->getShippingPriceEuroAttributes(),
            $this->getSellingFormatTemplate()->getTrackingAttributes()
        ));
    }

    // ########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id', $this->getId());

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id', 'synch_status', 'synch_reasons'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('listing');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('listing');
        return parent::delete();
    }

    // ########################################
}