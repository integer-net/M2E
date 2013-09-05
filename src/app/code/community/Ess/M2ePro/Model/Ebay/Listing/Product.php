<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Item
     */
    protected $ebayItemModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Manager[]
     */
    private $templateManagers = array();

    //-----------------------------------------

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Description
     */
    private $descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment|Ess_M2ePro_Model_Ebay_Template_Policy
     */
    private $paymentTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return|Ess_M2ePro_Model_Ebay_Template_Policy
     */
    private $returnTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping|Ess_M2ePro_Model_Ebay_Template_Policy
     */
    private $shippingTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->ebayItemModel = NULL;
        $this->categoryTemplateModel = NULL;
        $this->templateManagers = array();
        $this->sellingFormatTemplateModel = NULL;
        $this->synchronizationTemplateModel = NULL;
        $this->descriptionTemplateModel = NULL;
        $this->paymentTemplateModel = NULL;
        $this->returnTemplateModel = NULL;
        $this->shippingTemplateModel = NULL;

        $this->delete();
        return true;
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

    //------------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplateModel)) {

            try {
                $this->categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getTemplateCategoryId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->categoryTemplateModel;
            }

            $this->categoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
        }

        return $this->categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->categoryTemplateModel = $instance;
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

    // ########################################

    /**
     * @param $template
     * @return Ess_M2ePro_Model_Ebay_Template_Manager
     */
    public function getTemplateManager($template)
    {
        if (!isset($this->templateManagers[$template])) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setOwnerObject($this);
            $this->templateManagers[$template] = $manager->setTemplate($template);
        }

        return $this->templateManagers[$template];
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
            $this->sellingFormatTemplateModel = $this->getTemplateManager($template)->getResultObject();
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
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
            $this->synchronizationTemplateModel = $this->getTemplateManager($template)->getResultObject();
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
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
            $this->descriptionTemplateModel = $this->getTemplateManager($template)->getResultObject();
            if ($this->getTemplateManager($template)->isResultObjectTemplate()) {
                $this->descriptionTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Ebay_Template_Description $instance)
    {
         $this->descriptionTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if (is_null($this->paymentTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
            $this->paymentTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
         $this->paymentTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        if (is_null($this->returnTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
            $this->returnTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->returnTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Return $instance
     */
    public function setReturnTemplate(Ess_M2ePro_Model_Ebay_Template_Return $instance)
    {
         $this->returnTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $this->shippingTemplateModel = $this->getTemplateManager($template)->getResultObject();
            if ($this->getTemplateManager($template)->isResultObjectTemplate()) {
                $this->shippingTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
         $this->shippingTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
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

    public function getTemplateCategoryId()
    {
        return $this->getData('template_category_id');
    }

    public function isSetCategoryTemplate()
    {
        return !is_null($this->getTemplateCategoryId());
    }

    //-----------------------------------------

    public function getOnlineSku()
    {
        return $this->getData('online_sku');
    }

    public function getOnlineTitle()
    {
        return $this->getData('online_title');
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

    public function getOnlineCategory()
    {
        return $this->getData('online_category');
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

    public function isM2eProListedItem()
    {
        return (bool)$this->getData('is_m2epro_listed_item');
    }

    // ########################################

    public function getSku()
    {
        return $this->getMagentoProduct()->getSku();
    }

    public function getDuration()
    {
        $src = $this->getEbaySellingFormatTemplate()->getDurationSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::DURATION_TYPE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
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

        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    public function getReservePrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getEbaySellingFormatTemplate()->getReservePriceSource();
        $price = $this->getBaseProductPrice($src['mode'],$src['attribute']);

        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
    }

    public function getBuyItNowPrice()
    {
        if (is_null($this->getTemplateCategoryId())) {
            return 0;
        }

        if ($this->isListingTypeFixed() &&
            $this->getCategoryTemplate()->isVariationMode() &&
            $this->getMagentoProduct()->isProductWithVariations()) {

            $filters = array('delete' => 0);
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

        return Mage::helper('M2ePro')->parsePrice($price, $src['coefficient']);
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
                    $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
                }
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            default:
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $price = $this->getBaseGroupedProductPrice(
                        Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT
                    );
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                    $price = $this->getEbayListing()->convertPriceFromStoreToMarketplace($price);
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
                    $tempPrice = $this->getEbayListing()->convertPriceFromStoreToMarketplace($tempPrice);
                    break;
                case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_SPECIAL:
                    $tempPrice = $tempProduct->getSpecialPrice();
                    $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    $tempPrice = $this->getEbayListing()->convertPriceFromStoreToMarketplace($tempPrice);
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
        if (is_null($this->getTemplateCategoryId())) {
            return 0;
        }

        if ($this->isListingTypeAuction()) {
            if ($productMode) {
                return $this->_getProductGeneralQty();
            }
            return 1;
        }

        if ($this->getCategoryTemplate()->isVariationMode() &&
            $this->getMagentoProduct()->isProductWithVariations()) {

            $filters = array('delete' => 0);
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
        if ($src['qty_max_posted_value_mode'] && $qty > $src['qty_max_posted_value']) {
            $qty = $src['qty_max_posted_value'];
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    //-----------------------------------------

    protected function _getProductGeneralQty()
    {
        if (is_null($this->getTemplateCategoryId())) {
            return 0;
        }

        if ($this->getMagentoProduct()->isStrictVariationProduct() &&
            !$this->getCategoryTemplate()->isVariationMode()) {
            return $this->getParentObject()->_getOnlyVariationProductQty();
        }

        return (int)floor($this->getMagentoProduct()->getQty());
    }

    // ########################################

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

    public function getTrackingAttributes()
    {
        $attributes = $this->getListing()->getTrackingAttributes();

        $categoryTemplateObject = $this->getCategoryTemplate();
        if (!is_null($categoryTemplateObject)) {
            $attributes = array_merge($attributes,$categoryTemplateObject->getTrackingAttributes());
        }

        foreach (Mage::getModel('M2ePro/Ebay_Template_Manager')->getTrackingAttributesTemplates() as $template) {
            $templateManager = $this->getTemplateManager($template);
            $resultObjectTemp = $templateManager->getResultObject();
            if ($resultObjectTemp && $templateManager->isResultObjectTemplate()) {
                $attributes = array_merge($attributes,$resultObjectTemp->getTrackingAttributes());
            }
        }

        return array_unique($attributes);
    }

    // ########################################

    public function setIsNeedSynchronize($newData, $oldData)
    {
        $changedTemplates = $this->getChangedTemplates($newData,$oldData);

        if (empty($changedTemplates)) {
            return;
        }

        foreach ($changedTemplates as &$template) {
            if ($template == Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT) {
                $template = 'sellingFormatTemplate';
            } else {
                $template .= 'Template';
            }
        }
        unset($template);

        $synchReasons = $this->getParentObject()->getSynchReasons();
        $synchReasons = array_unique(array_merge($synchReasons,$changedTemplates));

        $this->getParentObject()->setData('is_need_synchronize', 1);
        $this->getParentObject()->setData('synch_reasons',implode(',',$synchReasons));

        $this->getParentObject()->save();
    }

    // ########################################

    private function getChangedTemplates($newData, $oldData)
    {
        $changedTemplates = Mage::getModel('M2ePro/Ebay_Template_Manager')->getChangedTemplates($newData,$oldData);

        if ($newData['template_category_id'] == $oldData['template_category_id']) {
            return $changedTemplates;
        }

        if (!$oldData['template_category_id']) {
            $changedTemplates[] = 'category';
            return $changedTemplates;
        }

        $helper = Mage::helper('M2ePro');

        $newTemplateCategory = $helper->getCachedObject('Ebay_Template_Category',$newData['template_category_id']);
        $oldTemplateCategory = $helper->getCachedObject('Ebay_Template_Category',$oldData['template_category_id']);

        if (Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->isDifferent(
            $newTemplateCategory->getDataSnapshot(), $oldTemplateCategory->getDataSnapshot()
        )) {
            $changedTemplates[] = 'category';
        }

        return $changedTemplates;
    }

    // ########################################
}