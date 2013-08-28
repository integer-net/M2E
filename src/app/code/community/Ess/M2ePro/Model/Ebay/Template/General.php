<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_General getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Template_General extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const CATEGORIES_MODE_EBAY      = 0;
    const CATEGORIES_MODE_ATTRIBUTE = 1;

    const CONDITION_MODE_EBAY       = 0;
    const CONDITION_MODE_ATTRIBUTE  = 1;

    const MOTORS_SPECIFICS_VALUE_SEPARATOR = ',';

    const STORE_CATEGORY_NONE             = 0;
    const STORE_CATEGORY_EBAY_VALUE       = 1;
    const STORE_CATEGORY_CUSTOM_ATTRIBUTE = 2;

    const SKU_MODE_NO  = 0;
    const SKU_MODE_YES = 1;

    const SHIPPING_TYPE_FLAT                = 0;
    const SHIPPING_TYPE_CALCULATED          = 1;
    const SHIPPING_TYPE_FREIGHT             = 2;
    const SHIPPING_TYPE_LOCAL               = 3;
    const SHIPPING_TYPE_NO_INTERNATIONAL    = 4;

    const EBAY_SHIPPING_TYPE_FLAT       = "flat";
    const EBAY_SHIPPING_TYPE_CALCULATED = "calculated";
    const EBAY_SHIPPING_TYPE_FREIGHT    = "freight";
    const EBAY_SHIPPING_TYPE_LOCAL      = "local";

    const GALLERY_TYPE_EMPTY    = 4;
    const GALLERY_TYPE_NO       = 0;
    const GALLERY_TYPE_PICTURE  = 1;
    const GALLERY_TYPE_PLUS     = 2;
    const GALLERY_TYPE_FEATURED = 3;

    const INTERNATIONAL_TRADE_MODE_NO  = 0;
    const INTERNATIONAL_TRADE_MODE_YES = 1;

    const VARIATION_DISABLED = 0;
    const VARIATION_ENABLED  = 1;

    const VARIATION_IGNORE_DISABLED = 0;
    const VARIATION_IGNORE_ENABLED  = 1;

    const GET_IT_FAST_DISABLED     = 0;
    const GET_IT_FAST_ENABLED      = 1;

    const OPTION_NONE               = 0;
    const OPTION_CUSTOM_VALUE       = 1;
    const OPTION_CUSTOM_ATTRIBUTE   = 2;

    const PRODUCT_DETAIL_MODE_NONE = 0;
    const PRODUCT_DETAIL_MODE_CUSTOM_VALUE = 1;
    const PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE = 2;

    const CASH_ON_DELIVERY_COST_MODE_NONE             = 0;
    const CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE     = 1;
    const CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping
     */
    private $calculatedShippingModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_General');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $calculatedShippingObject = $this->getCalculatedShipping();
        !is_null($calculatedShippingObject) && $calculatedShippingObject->deleteInstance();

        $payments = $this->getPayments(true);
        foreach ($payments as $payment) {
            $payment->deleteInstance();
        }

        $shippings = $this->getShippings(true);
        foreach ($shippings as $shipping) {
            $shipping->deleteInstance();
        }

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }

        $this->calculatedShippingModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping
     */
    public function getCalculatedShipping()
    {
        if (is_null($this->calculatedShippingModel)) {
            $calculatedShippingObject = Mage::getModel('M2ePro/Ebay_Template_General_CalculatedShipping')
                ->load($this->getId());
            $calculatedShippingObject->getId() && $this->calculatedShippingModel = $calculatedShippingObject;
        }

        return $this->calculatedShippingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping $instance
     */
    public function setCalculatedShipping(Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping $instance)
    {
         $this->calculatedShippingModel = $instance;
    }

    //-----------------------------------------

    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    // ########################################

    public function getPayments($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Ebay_Template_General_Payment','template_general_id',$asObjects,$filters);
    }

    public function getShippings($asObjects = false, array $filters = array(),
                                 array $sort = array('priority'=>Varien_Data_Collection::SORT_ORDER_ASC))
    {
        return $this->getRelatedSimpleItems(
            'Ebay_Template_General_Shipping',
            'template_general_id',$asObjects,$filters,$sort
        );
    }

    public function getSpecifics($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Ebay_Template_General_Specific','template_general_id',$asObjects,$filters);
    }

    //-----------------------------------------

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject->getListings($asObjects,$filters);
    }

    // ########################################

    public function getGalleryType()
    {
        return (int)$this->getData('gallery_type');
    }

    public function isSkuEnabled()
    {
        return (int)$this->getData('sku_mode') == self::SKU_MODE_YES;
    }

    public function getEnhancements()
    {
        return $this->getData('enhancement') ? explode(',', $this->getData('enhancement')) : array();
    }

    public function getRefundOptions()
    {
        return array(
            'accepted'      => $this->getData('refund_accepted'),
            'option'        => $this->getData('refund_option'),
            'within'        => $this->getData('refund_within'),
            'description'   => $this->getData('refund_description'),
            'shippingcost'  => $this->getData('refund_shippingcost'),
            'restockingfee' => $this->getData('refund_restockingfee')
        );
    }

    //-------------------------------

    public function getCategoriesSource()
    {
        return array(
            'mode'                => $this->getData('categories_mode'),
            'main_value'          => $this->getData('categories_main_id'),
            'main_attribute'      => $this->getData('categories_main_attribute'),
            'secondary_value'     => $this->getData('categories_secondary_id'),
            'secondary_attribute' => $this->getData('categories_secondary_attribute'),
            'tax_value'           => $this->getData('tax_category'),
            'tax_attribute'       => $this->getData('tax_category_attribute')
        );
    }

    public function getStoreCategoriesSource()
    {
        return array(
            'main_mode'           => $this->getData('store_categories_main_mode'),
            'main_value'          => $this->getData('store_categories_main_id'),
            'main_attribute'      => $this->getData('store_categories_main_attribute'),
            'secondary_mode'      => $this->getData('store_categories_secondary_mode'),
            'secondary_value'     => $this->getData('store_categories_secondary_id'),
            'secondary_attribute' => $this->getData('store_categories_secondary_attribute'),
        );
    }

    //-------------------------------

    public function isVariationEnabled()
    {
        return (int)$this->getData('variation_enabled') == self::VARIATION_ENABLED;
    }

    public function isVariationIgnore()
    {
        return (int)$this->getData('variation_ignore') == self::VARIATION_IGNORE_ENABLED;
    }

    public function isVariationMode()
    {
        return $this->isVariationEnabled() && !$this->isVariationIgnore();
    }

    //-------------------------------

    public function getConditionMode()
    {
        return (int)$this->getData('condition_mode');
    }

    public function isConditionModeEbay()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_EBAY;
    }

    public function isConditionModeAttribute()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_ATTRIBUTE;
    }

    //-------------------------------

    public function getItemConditionSource()
    {
        return array(
            'mode'      => $this->getData('condition_mode'),
            'value'     => $this->getData('condition_value'),
            'attribute' => $this->getData('condition_attribute')
        );
    }

    public function getProductDetailSource($type)
    {
        if (!in_array($type, array('isbn', 'epid', 'upc', 'ean'))) {
            throw new InvalidArgumentException('Unknown product details name');
        }

        if ($this->getData('product_details') == '' || $this->getData('product_details') == json_encode(array())) {
            return NULL;
        }

        $tempProductsDetails = json_decode($this->getData('product_details'),true);

        if (!isset($tempProductsDetails["product_details_{$type}_mode"]) ||
            !isset($tempProductsDetails["product_details_{$type}_cv"]) ||
            !isset($tempProductsDetails["product_details_{$type}_ca"])) {
            return NULL;
        }

        return array(
            'mode'      => $tempProductsDetails["product_details_{$type}_mode"],
            'value'     => $tempProductsDetails["product_details_{$type}_cv"],
            'attribute' => $tempProductsDetails["product_details_{$type}_ca"]
        );
    }

    public function getMotorsSpecificsAttribute()
    {
        return $this->getData('motors_specifics_attribute');
    }

    //-------------------------------

    public function getCountry()
    {
        return $this->getData('country');
    }

    public function getPostalCode()
    {
        return $this->getData('postal_code');
    }

    public function getAddress()
    {
        return $this->getData('address');
    }

    public function isGetItFastEnabled()
    {
        return (int)$this->getData('get_it_fast') == self::GET_IT_FAST_ENABLED;
    }

    public function getDispatchTime()
    {
        return $this->getData('dispatch_time');
    }

    //-------------------------------

    public function isLocalShippingEnabled()
    {
        return true;
    }

    public function isLocalShippingFlatEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    public function isLocalShippingCalculatedEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }

    public function isLocalShippingFreightEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_FREIGHT;
    }

    public function isLocalShippingLocalEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_LOCAL;
    }

    //-------------------------------

    public function isInternationalShippingEnabled()
    {
        return (int)$this->getData('international_shipping_mode') != self::SHIPPING_TYPE_NO_INTERNATIONAL;
    }

    public function isInternationalShippingFlatEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    public function isInternationalShippingCalculatedEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }

    //-------------------------------

    public function isLocalShippingDiscountEnabled()
    {
        return (bool)$this->getData('local_shipping_discount_mode');
    }

    public function getLocalShippingCombinedDiscountProfileId()
    {
        return $this->getData('local_shipping_combined_discount_profile_id');
    }

    public function isInternationalShippingDiscountEnabled()
    {
        return (bool)$this->getData('international_shipping_discount_mode');
    }

    public function getInternationalShippingCombinedDiscountProfileId()
    {
        return $this->getData('international_shipping_combined_discount_profile_id');
    }

    //-------------------------------

    public function isUseEbayTaxTableEnabled()
    {
        return (bool)$this->getData('use_ebay_tax_table');
    }

    public function getVatPercent()
    {
        return (float)$this->getData('vat_percent');
    }

    public function isUseEbayLocalShippingRateTableEnabled()
    {
        return (bool)$this->getData('use_ebay_local_shipping_rate_table');
    }

    public function isUseEbayInternationalShippingRateTableEnabled()
    {
        return (bool)$this->getData('use_ebay_international_shipping_rate_table');
    }

    //-------------------------------

    public function getPayPalEmailAddress()
    {
        return $this->getData('pay_pal_email_address');
    }

    public function isPayPalImmediatePaymentEnabled()
    {
        return (bool)$this->getData('pay_pal_immediate_payment');
    }

    //-------------------------------

    public function getLocalShippingCashOnDeliveryCostMode()
    {
        return (int)$this->getData('local_shipping_cash_on_delivery_cost_mode');
    }

    public function isLocalShippingCashOnDeliveryEnabled()
    {
        return $this->getLocalShippingCashOnDeliveryCostMode() == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE ||
               $this->getLocalShippingCashOnDeliveryCostMode() == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getLocalShippingCashOnDeliverySource()
    {
        return array(
            'mode'      => $this->getData('local_shipping_cash_on_delivery_cost_mode'),
            'value'     => $this->getData('local_shipping_cash_on_delivery_cost_value'),
            'attribute' => $this->getData('local_shipping_cash_on_delivery_cost_attribute')
        );
    }

    // ########################################

    public function getPaymentMethods()
    {
        $return = array();

        $payments = $this->getPayments();

        foreach ($payments as $payment) {
            $return[] = $payment['payment_id'];
        }

        return $return;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array();
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_general');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_general');
        return parent::delete();
    }

    // ########################################
}