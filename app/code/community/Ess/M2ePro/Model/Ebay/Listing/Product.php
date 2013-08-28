<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const IS_M2EPRO_LISTED_ITEM_NO  = 0;
    const IS_M2EPRO_LISTED_ITEM_YES = 1;

    const GALLERY_IMAGES_COUNT_MAX = 11;

    const TRIED_TO_LIST_YES = 1;
    const TRIED_TO_LIST_NO  = 0;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Item
     */
    protected $ebayItemModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->ebayItemModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    public function getEbayItem()
    {
        if (is_null($this->ebayItemModel)) {
            $this->ebayItemModel = Mage::getModel('M2ePro/Ebay_Item')->loadInstance($this->getData('ebay_item_id'));
        }

        return $this->ebayItemModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Item $instance
     */
    public function setEbayItem(Ess_M2ePro_Model_Ebay_Item $instance)
    {
         $this->ebayItemModel = $instance;
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
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
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
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    public function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_General
     */
    public function getEbayGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    // ########################################

    public function getEbayItemIdReal()
    {
        return $this->getEbayItem()->getItemId();
    }

    public function getParentInstanceByEbayItem($ebayItem)
    {
        // Get listing product
        //-----------------------------
        $ebayItem = $this->getResource()->getReadConnection()->quoteInto('?', $ebayItem);

        /** @var $collection Ess_M2ePro_Model_Mysql4_Listing_Product_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('mei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
            '(second_table.ebay_item_id = mei.id AND mei.item_id = ' . $ebayItem . ')',
            array()
        );
        //-----------------------------

        if ($collection->getSize() == 0) {
            return NULL;
        }

        return $collection->getFirstItem();
    }

    // ########################################

    public function getEbayItemId()
    {
        return (int)$this->getData('ebay_item_id');
    }

    //-----------------------------------------

    public function getOnlineStartPrice()
    {
        return (float)$this->getData('online_start_price');
    }

    public function getOnlineReservePrice()
    {
        return (float)$this->getData('online_reserve_price');
    }

    public function getOnlineBuyItNowPrice()
    {
        return (float)$this->getData('online_buyitnow_price');
    }

    //-----------------------------------------

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    public function getOnlineQtySold()
    {
        return (int)$this->getData('online_qty_sold');
    }

    public function getOnlineBids()
    {
        return (int)$this->getData('online_bids');
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

    public function getIsM2eProListedItem()
    {
        return (int)$this->getData('is_m2epro_listed_item');
    }

    public function isM2eProListedItem()
    {
        return $this->getIsM2eProListedItem() == self::IS_M2EPRO_LISTED_ITEM_YES;
    }

    //-----------------------------------------

    public function isTriedToList()
    {
        return $this->getData('tried_to_list') == self::TRIED_TO_LIST_YES;
    }

    // ########################################

    public function getSku()
    {
        if ($this->getEbayGeneralTemplate()->isSkuEnabled()) {
            return $this->getMagentoProduct()->getSku();
        }
        return '';
    }

    public function getDuration()
    {
        $src = $this->getEbaySellingFormatTemplate()->getDurationSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::DURATION_TYPE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getItemCondition()
    {
        $src = $this->getEbayGeneralTemplate()->getItemConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::CONDITION_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getMotorsSpecifics()
    {
        $attributeCode  = $this->getEbayGeneralTemplate()->getMotorsSpecificsAttribute();
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

        if (empty($attributeValue)) {
            return array();
        }

        $epids = explode(Ess_M2ePro_Model_Ebay_Template_General::MOTORS_SPECIFICS_VALUE_SEPARATOR, $attributeValue);

        return Mage::getModel('M2ePro/Ebay_Motor_Specific')
            ->getCollection()
            ->addFieldToFilter('epid', array('in' => $epids))
            ->getItems();
    }

    public function getProductDetail($type)
    {
        $src = $this->getEbayGeneralTemplate()->getProductDetailSource($type);

        if (is_null($src) || $src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::OPTION_NONE) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::OPTION_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getLocalShippingCashOnDeliveryCost()
    {
        $src = $this->getEbayGeneralTemplate()->getLocalShippingCashOnDeliverySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE) {
            return (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE) {
            return (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return NULL;
    }

    //-----------------------------------------

    public function getTitle()
    {
        $title = '';
        $src = $this->getEbayDescriptionTemplate()->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_CUSTOM:
                $title = Mage::getSingleton('M2ePro/Template_Description_Parser')
                    ->parseTemplate($src['template'], $this->getMagentoProduct()->getProduct());
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        if ($this->getEbayDescriptionTemplate()->isCutLongTitles()) {
            $title = $this->getEbayDescriptionTemplate()->cutLongTitles($title);
        }

        return $title;
    }

    public function getSubTitle()
    {
        $subTitle = '';
        $src = $this->getEbayDescriptionTemplate()->getSubTitleSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Description::SUBTITLE_MODE_CUSTOM) {
            $subTitle = Mage::getSingleton('M2ePro/Template_Description_Parser')
                ->parseTemplate($src['template'], $this->getMagentoProduct()->getProduct());
            if ($this->getEbayDescriptionTemplate()->isCutLongTitles()) {
                $subTitle = $this->getEbayDescriptionTemplate()->cutLongTitles($subTitle, 55);
            }
        }

        return $subTitle;
    }

    public function getDescription()
    {
        $description = '';
        $src = $this->getEbayDescriptionTemplate()->getDescriptionSource();
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_PRODUCT:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::getSingleton('M2ePro/Template_Description_Parser')
                    ->parseTemplate($src['template'], $this->getMagentoProduct()->getProduct());
                break;

            default:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    //-----------------------------------------

    public function getListingType()
    {
        $src = $this->getEbaySellingFormatTemplate()->getListingTypeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE) {
            $ebayStringType = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            switch ($ebayStringType) {
                case Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_FIXED:
                    return Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
                case Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_AUCTION:
                    return Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
            }
            throw new LogicException('Invalid listing type in attribute.');
        }

        return $src['mode'];
    }

    public function isListingTypeFixed()
    {
        return $this->getListingType() == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
    }

    public function isListingTypeAuction()
    {
        return $this->getListingType() == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
    }

    // ########################################

    public function getStartPrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getEbaySellingFormatTemplate()->getStartPriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    public function getReservePrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getEbaySellingFormatTemplate()->getReservePriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    public function getBuyItNowPrice()
    {
        if ($this->isListingTypeFixed() &&
            $this->getEbayGeneralTemplate()->isVariationMode() &&
            $this->getMagentoProduct()->isProductWithVariations()) {

            $filters = array('delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO);
            $variations = $this->getVariations(true, $filters);

            if (count($variations) > 0) {

                $pricesList = array();
                foreach ($variations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                    $pricesList[] = $variation->getChildObject()->getPrice();
                }

                return count($pricesList) > 0 ? min($pricesList) : 0;
            }
        }

        $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '')
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE:
                $price = 0;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(
                        Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_SPECIAL
                    );
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                }
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_FINAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(
                        Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_FINAL
                    );
                } else {
                    $customerGroupId = $this->getEbaySellingFormatTemplate()->getCustomerGroupId();
                    $price = $this->getMagentoProduct()->getFinalPrice($customerGroupId);
                }
                break;

            default:
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(
                        Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT
                    );
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                }
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType)
    {
        $price = 0;

        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;

            /** @var $tempProduct Ess_M2ePro_Model_Magento_Product */
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_SPECIAL:
                    $tempPrice = $tempProduct->getSpecialPrice();
                    $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_FINAL:
                    $tempProduct = Mage::getModel('M2ePro/Magento_Product')
                                            ->setProductId($tempProduct->getProductId())
                                            ->setStoreId($this->getListing()->getStoreId());
                    $customerGroupId = $this->getEbaySellingFormatTemplate()->getCustomerGroupId();
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
        if ($this->isListingTypeAuction()) {
            if ($productMode) {
                return $this->_getProductGeneralQty();
            }
            return 1;
        }

        if ($this->getEbayGeneralTemplate()->isVariationMode() &&
            $this->getMagentoProduct()->isProductWithVariations()) {

            $filters = array('delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO);
            $variations = $this->getVariations(true, $filters);

            if (count($variations) > 0) {

                $totalQty = 0;
                foreach ($variations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                    $totalQty += $variation->getChildObject()->getQty();
                }

                return (int)floor($totalQty);
            }
        }

        $qty = 0;
        $src = $this->getEbaySellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_SINGLE:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = 1;
                }
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_NUMBER:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = $src['value'];
                }
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT:
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
        if ($this->getMagentoProduct()->isStrictVariationProduct() &&
            !$this->getEbayGeneralTemplate()->isVariationMode()) {
            return $this->getParentObject()->_getOnlyVariationProductQty();
        }
        return (int)floor($this->getMagentoProduct()->getQty());
    }

    // ########################################

    public function getMainCategory()
    {
        $src = $this->getEbayGeneralTemplate()->getCategoriesSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::CATEGORIES_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['main_attribute']);
        }

        return $src['main_value'];
    }

    public function getSecondaryCategory()
    {
        $src = $this->getEbayGeneralTemplate()->getCategoriesSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::CATEGORIES_MODE_ATTRIBUTE) {
            return $src['secondary_attribute']
                ? $this->getMagentoProduct()->getAttributeValue($src['secondary_attribute']) : 0;
        }

        return $src['secondary_value'];
    }

    public function getTaxCategory()
    {
        $src = $this->getEbayGeneralTemplate()->getCategoriesSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General::CATEGORIES_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['tax_attribute']);
        }

        return $src['tax_value'];
    }

    //-----------------------------------------

    public function getMainStoreCategory()
    {
        $src = $this->getEbayGeneralTemplate()->getStoreCategoriesSource();

        $category = 0;
        switch ($src['main_mode']) {
            case Ess_M2ePro_Model_Ebay_Template_General::STORE_CATEGORY_EBAY_VALUE:
                $category = $src['main_value'];
                break;

            case Ess_M2ePro_Model_Ebay_Template_General::STORE_CATEGORY_CUSTOM_ATTRIBUTE:
                $category = $this->getMagentoProduct()->getAttributeValue($src['main_attribute']);
                break;
        }

        return $category;
    }

    public function getSecondaryStoreCategory()
    {
        $src = $this->getEbayGeneralTemplate()->getStoreCategoriesSource();

        $category = 0;
        switch ($src['secondary_mode']) {
            case Ess_M2ePro_Model_Ebay_Template_General::STORE_CATEGORY_EBAY_VALUE:
                $category = $src['secondary_value'];
                break;

            case Ess_M2ePro_Model_Ebay_Template_General::STORE_CATEGORY_CUSTOM_ATTRIBUTE:
                $category = $this->getMagentoProduct()->getAttributeValue($src['secondary_attribute']);
                break;
        }

        return $category;
    }

    //-----------------------------------------

    public function getBestOfferAcceptPrice()
    {
        if (!$this->isListingTypeFixed()) {
            return 0;
        }

        if (!$this->getEbaySellingFormatTemplate()->isBestOfferEnabled()) {
            return 0;
        }

        if ($this->getEbaySellingFormatTemplate()->isBestOfferAcceptModeNo()) {
            return 0;
        }

        $src = $this->getEbaySellingFormatTemplate()->getBestOfferAcceptSource();

        $price = 0;
        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_PERCENTAGE:
                $price = $this->getBuyItNowPrice() * (float)$src['value'] / 100;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE:
                $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;
        }

        return round($price, 2);
    }

    public function getBestOfferRejectPrice()
    {
        if (!$this->isListingTypeFixed()) {
            return 0;
        }

        if (!$this->getEbaySellingFormatTemplate()->isBestOfferEnabled()) {
            return 0;
        }

        if ($this->getEbaySellingFormatTemplate()->isBestOfferRejectModeNo()) {
            return 0;
        }

        $src = $this->getEbaySellingFormatTemplate()->getBestOfferRejectSource();

        $price = 0;
        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_PERCENTAGE:
                $price = $this->getBuyItNowPrice() * (float)$src['value'] / 100;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_ATTRIBUTE:
                $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;
        }

        return round($price, 2);
    }

    //-----------------------------------------

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getEbayDescriptionTemplate()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }

        if ($this->getEbayDescriptionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getEbayDescriptionTemplate()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        if (empty($imageLink)) {
            return $imageLink;
        }

        return $this->getEbayDescriptionTemplate()->addWatermarkIfNeed($imageLink);
    }

    public function getImagesForEbay()
    {
        if ($this->getEbayDescriptionTemplate()->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        $mainImage = array($mainImage);

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeNone()) {
            return $mainImage;
        }

        $galleryImages = array();
        $gallerySource = $this->getEbayDescriptionTemplate()->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks((int)$gallerySource['limit']+1);
        }

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeAttribute()) {
            $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;
            $galleryImagesTemp = $this->getMagentoProduct()->getAttributeValue($gallerySource['attribute']);
            $galleryImagesTemp = (array)explode(',', $galleryImagesTemp);
            foreach ($galleryImagesTemp as $tempImageLink) {
                $tempImageLink = trim($tempImageLink);
                if (!empty($tempImageLink)) {
                    $galleryImages[] = $tempImageLink;
                }
            }
        }

        $galleryImages = array_unique($galleryImages);

        if (count($galleryImages) <= 0) {
            return $mainImage;
        }

        foreach ($galleryImages as &$image) {
            $image = $this->getEbayDescriptionTemplate()->addWatermarkIfNeed($image);
        }

        $mainImagePosition = array_search($mainImage[0], $galleryImages);
        if ($mainImagePosition !== false) {
            unset($galleryImages[$mainImagePosition]);
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);
        return array_merge($mainImage, $galleryImages);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_General_Specific[]
     */
    public function getSpecifics()
    {
        $returns = array();

        $items = $this->getEbayGeneralTemplate()->getSpecifics(true);
        foreach ($items as $item) {

            /** @var $item Ess_M2ePro_Model_Ebay_Template_General_Specific */
            $item->setMagentoProduct($this->getMagentoProduct());
            $returns[] = $item;
        }

        return $returns;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_General_Shipping[]
     */
    public function getLocalShippingMethods()
    {
        $returns = array();

        $items = $this->getEbayGeneralTemplate()->getShippings(true);
        foreach ($items as $item) {

            /** @var $item Ess_M2ePro_Model_Ebay_Template_General_Shipping */
            if ($item->isShippingTypeLocal()) {
                $item->setMagentoProduct($this->getMagentoProduct());
                $returns[] = $item;
            }
        }

        return $returns;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_General_Shipping[]
     */
    public function getInternationalShippingMethods()
    {
        $returns = array();

        $items = $this->getEbayGeneralTemplate()->getShippings(true);
        foreach ($items as $item) {

            /** @var $item Ess_M2ePro_Model_Ebay_Template_General_Shipping */
            if (!$item->isShippingTypeInternational()) {
                $item->setMagentoProduct($this->getMagentoProduct());
                $returns[] = $item;
            }
        }

        return $returns;
    }

    // ########################################

    public function getLocalHandling()
    {
        $src = $this->getEbayGeneralTemplate()->getCalculatedShipping()->getLocalHandlingSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::HANDLING_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::HANDLING_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getInternationalHandling()
    {
        $src = $this->getEbayGeneralTemplate()->getCalculatedShipping()->getInternationalHandlingSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::HANDLING_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::HANDLING_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //-----------------------------------------

    public function getPackageSize()
    {
        $src = $this->getEbayGeneralTemplate()->getCalculatedShipping()->getPackageSizeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::PACKAGE_SIZE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getDimensions()
    {
        $src = $this->getEbayGeneralTemplate()->getCalculatedShipping()->getDimensionsSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::DIMENSIONS_NONE) {
            return array();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::DIMENSIONS_CUSTOM_ATTRIBUTE) {
            return array(
                'width' => $this->getMagentoProduct()->getAttributeValue($src['width_attribute']),
                'height' => $this->getMagentoProduct()->getAttributeValue($src['height_attribute']),
                'depth' => $this->getMagentoProduct()->getAttributeValue($src['depth_attribute'])
            );
        }

        return array(
            'width' => $src['width_value'],
            'height' => $src['height_value'],
            'depth' => $src['depth_value']
        );
    }

    public function getWeight()
    {
        $src = $this->getEbayGeneralTemplate()->getCalculatedShipping()->getWeightSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::WEIGHT_CUSTOM_ATTRIBUTE) {

            $weightValue = $this->getMagentoProduct()->getAttributeValue($src['weight_attribute']);
            $weightValue = str_replace(',', '.', $weightValue);
            $weightArray = explode('.', $weightValue);

            $minor = $major = 0;
            if (count($weightArray) >= 2) {
                list($major, $minor) = $weightArray;

                if ($minor > 0 &&
                    $this->getEbayGeneralTemplate()->getCalculatedShipping()->isMeasurementSystemEnglish()) {
                    $minor = ($minor / pow(10, strlen($minor))) * 16;
                    $minor = ceil($minor);
                    if ($minor == 16) {
                        $major += 1;
                        $minor = 0;
                    }
                }

                if ($minor > 0 &&
                    $this->getEbayGeneralTemplate()->getCalculatedShipping()->isMeasurementSystemMetric()) {
                    $minor = ($minor / pow(10, strlen($minor))) * 1000;
                    $minor = ceil($minor);
                    if ($minor == 1000) {
                        $major += 1;
                        $minor = 0;
                    }
                }

                $minor < 0 && $minor = 0;
            } else {
                $major = (int)$weightValue;
            }

            return array(
                'minor' => (float)$minor,
                'major' => (int)$major
            );
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::WEIGHT_NONE) {
            return array(
                'minor' => 0,
                'major' => 0
            );
        }

        return array(
            'minor' => (float)$src['weight_minor'],
            'major' => (int)$src['weight_major']
        );
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        return Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Dispatcher')
            ->process($action, $this->getId(), $params);
    }

    // ########################################
}