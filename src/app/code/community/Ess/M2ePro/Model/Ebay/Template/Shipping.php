<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    const SHIPPING_TYPE_FLAT                = 0;
    const SHIPPING_TYPE_CALCULATED          = 1;
    const SHIPPING_TYPE_FREIGHT             = 2;
    const SHIPPING_TYPE_LOCAL               = 3;
    const SHIPPING_TYPE_NO_INTERNATIONAL    = 4;

    const CASH_ON_DELIVERY_COST_MODE_NONE             = 0;
    const CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE     = 1;
    const CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE = 2;

    const INTERNATIONAL_TRADE_NONE           = 0;
    const INTERNATIONAL_TRADE_NORTH_AMERICA  = 1;
    const INTERNATIONAL_TRADE_UNITED_KINGDOM = 2;

    const DISPATCH_TIME_CUSTOM_VALUE     = 0;
    const DISPATCH_TIME_CUSTOM_ATTRIBUTE = 1;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    private $calculatedShippingModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping');
    }

    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_shipping_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_shipping_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_shipping_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_shipping_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $calculatedShippingObject = $this->getCalculatedShipping();
        if (!is_null($calculatedShippingObject)) {
            $calculatedShippingObject->deleteInstance();
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }

        $this->marketplaceModel = NULL;
        $this->magentoProductModel = NULL;
        $this->calculatedShippingModel = NULL;

        $this->delete();
        return true;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    //---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    //---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    public function getCalculatedShipping()
    {
        if (is_null($this->calculatedShippingModel)) {

            try {
                $this->calculatedShippingModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Shipping_Calculated', $this->getId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->calculatedShippingModel;
            }

            if (!is_null($this->getMagentoProduct())) {
                $this->calculatedShippingModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->calculatedShippingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance
     */
    public function setCalculatedShipping(Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance)
    {
         $this->calculatedShippingModel = $instance;
    }

    // #######################################

    public function getServices($asObjects = false, array $filters = array(),
                                array $sort = array('priority'=>Varien_Data_Collection::SORT_ORDER_ASC))
    {
        $services = $this->getRelatedSimpleItems('Ebay_Template_Shipping_Service','template_shipping_id',
                                                 $asObjects, $filters, $sort);

        if ($asObjects) {
            foreach ($services as $service) {
                /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */
                if (!is_null($this->getMagentoProduct())) {
                    $service->setMagentoProduct($this->getMagentoProduct());
                }
            }
        }

        return $services;
    }

    // #######################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    //---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    // #######################################

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

    //---------------------------------------

    public function isGetItFastEnabled()
    {
        return (bool)$this->getData('get_it_fast');
    }

    public function isGlobalShippingProgramEnabled()
    {
        return (bool)$this->getData('global_shipping_program');
    }

    //---------------------------------------

    public function isLocalShippingRateTableEnabled()
    {
        return (bool)$this->getData('local_shipping_rate_table_mode');
    }

    public function isInternationalShippingRateTableEnabled()
    {
        return (bool)$this->getData('international_shipping_rate_table_mode');
    }

    // #######################################

    public function getDispatchTime()
    {
        $src = $this->getDispatchTimeSource();

        if ($src['mode'] == self::DISPATCH_TIME_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return (int)$src['value'];
    }

    public function getDispatchTimeSource()
    {
        return array(
            'mode'      => $this->getData('dispatch_time_mode'),
            'value'     => $this->getData('dispatch_time_value'),
            'attribute' => $this->getData('dispatch_time_attribute')
        );
    }

    // #######################################

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

    //---------------------------------------

    public function isLocalShippingDiscountEnabled()
    {
        return (bool)$this->getData('local_shipping_discount_mode');
    }

    public function getLocalShippingCombinedDiscountProfileId($accountId)
    {
        $data = $this->getData('local_shipping_combined_discount_profile_id');

        if (is_null($data)) {
            return NULL;
        }

        $data = json_decode($data, true);

        return !isset($data[$accountId]) ? NULL : $data[$accountId];
    }

    // #######################################

    public function isLocalShippingCashOnDeliveryEnabled()
    {
        $tempData = (int)$this->getData('local_shipping_cash_on_delivery_cost_mode');

        return $tempData == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE ||
               $tempData == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getLocalShippingCashOnDeliverySource()
    {
        return array(
            'mode'      => (int)$this->getData('local_shipping_cash_on_delivery_cost_mode'),
            'value'     => $this->getData('local_shipping_cash_on_delivery_cost_value'),
            'attribute' => $this->getData('local_shipping_cash_on_delivery_cost_attribute')
        );
    }

    public function getLocalShippingCashOnDeliveryAttributes()
    {
        $attributes = array();
        $src = $this->getLocalShippingCashOnDeliverySource();

        if ($src['mode'] == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // #######################################

    public function isInternationalShippingNoInternationalEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_NO_INTERNATIONAL;
    }

    public function isInternationalShippingFlatEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    public function isInternationalShippingCalculatedEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }

    //---------------------------------------

    public function isInternationalShippingDiscountEnabled()
    {
        return (bool)$this->getData('international_shipping_discount_mode');
    }

    public function getInternationalShippingCombinedDiscountProfileId($accountId)
    {
        $data = $this->getData('international_shipping_combined_discount_profile_id');

        if (is_null($data)) {
            return NULL;
        }

        $data = json_decode($data, true);

        return !isset($data[$accountId]) ? NULL : $data[$accountId];
    }

    //---------------------------------------

    public function getExcludedLocations()
    {
        $excludedLocations = $this->getData('excluded_locations');
        is_string($excludedLocations) && $excludedLocations = json_decode($excludedLocations,true);
        return is_array($excludedLocations) ? $excludedLocations : array();
    }

    //---------------------------------------

    public function getInternationalTrade()
    {
        return (int)$this->getData('international_trade');
    }

    public function isInternationalTradeNone()
    {
        return $this->getInternationalTrade() == self::INTERNATIONAL_TRADE_NONE;
    }

    public function isInternationalTradeNorthAmerica()
    {
        return $this->getInternationalTrade() == self::INTERNATIONAL_TRADE_NORTH_AMERICA;
    }

    public function isInternationalTradeUnitedKingdom()
    {
        return $this->getInternationalTrade() == self::INTERNATIONAL_TRADE_UNITED_KINGDOM;
    }

    // #######################################

    public function getLocalShippingCashOnDeliveryCost()
    {
        $src = $this->getLocalShippingCashOnDeliverySource();

        if ($src['mode'] == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE) {
            return (float)$src['value'];
        }

        if ($src['mode'] == self::CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE) {
            return (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return NULL;
    }

    //---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service[]
     */
    public function getLocalShippingServices()
    {
        $returns = array();

        $services = $this->getServices(true);
        foreach ($services as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if ($service->isShippingTypeLocal()) {
                $returns[] = $service;
            }
        }

        return $returns;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service[]
     */
    public function getInternationalShippingServices()
    {
        $returns = array();

        $services = $this->getServices(true);
        foreach ($services as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if ($service->isShippingTypeInternational()) {
                $returns[] = $service;
            }
        }

        return $returns;
    }

    // #######################################

    public function getTrackingAttributes()
    {
        $attributes = array();

        $calculatedShippingObject = $this->getCalculatedShipping();
        if (!is_null($calculatedShippingObject)) {
            $attributes = array_merge($attributes,$calculatedShippingObject->getTrackingAttributes());
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge($attributes,$service->getTrackingAttributes());
        }

        return array_unique($attributes);
    }

    public function getUsedAttributes()
    {
        $attributes = $this->getLocalShippingCashOnDeliveryAttributes();

        $calculatedShippingObject = $this->getCalculatedShipping();
        if (!is_null($calculatedShippingObject)) {
            $attributes = array_merge($attributes,$calculatedShippingObject->getUsedAttributes());
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge($attributes,$service->getUsedAttributes());
        }

        return array_unique($attributes);
    }

    // #######################################

    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();

        $data['services'] = $this->getServices();
        $data['calculated_shipping'] = $this->getCalculatedShipping()?$this->getCalculatedShipping()->getData():array();

        foreach ($data['services'] as &$serviceData) {
            foreach ($serviceData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }
        unset($value);

        foreach ($data['calculated_shipping'] as &$value) {
            !is_null($value) && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    public function getDefaultSettingsSimpleMode()
    {
        return $this->getDefaultSettingsAdvancedMode();
    }

    public function getDefaultSettingsAdvancedMode()
    {
        return array(
            'country' => 'US',
            'postal_code' => '',
            'address' => '',

            'local_shipping_rate_table_mode' => 0,
            'international_shipping_rate_table_mode' => 0,

            'get_it_fast' => 0,
            'dispatch_time_mode' => self::DISPATCH_TIME_CUSTOM_VALUE,
            'dispatch_time_value' => 1,
            'dispatch_time_attribute' => '',

            'global_shipping_program' => 0,
            'local_shipping_mode' =>  self::SHIPPING_TYPE_FLAT,
            'local_shipping_discount_mode' => 0,
            'local_shipping_combined_discount_profile_id' => '',

            'local_shipping_cash_on_delivery_cost_mode' => self::CASH_ON_DELIVERY_COST_MODE_NONE,
            'local_shipping_cash_on_delivery_cost_value' => '',
            'local_shipping_cash_on_delivery_cost_attribute' => '',

            'international_shipping_mode' => self::SHIPPING_TYPE_NO_INTERNATIONAL,
            'international_shipping_discount_mode' => 0,
            'international_shipping_combined_discount_profile_id' => '',

            // CALCULATED SHIPPING
            //----------------------------------
            'measurement_system' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH,
            'originating_postal_code' => '',

            'package_size_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::PACKAGE_SIZE_CUSTOM_VALUE,
            'package_size_value' => 'None',
            'package_size_attribute' => '',

            'dimension_mode'   => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::DIMENSION_NONE,
            'dimension_width_value'  => '',
            'dimension_height_value' => '',
            'dimension_depth_value'  => '',
            'dimension_width_attribute'  => '',
            'dimension_height_attribute' => '',
            'dimension_depth_attribute'  => '',

            'weight_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_NONE,
            'weight_minor' => '',
            'weight_major' => '',
            'weight_attribute' => '',

            'local_handling_cost_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_NONE,
            'local_handling_cost_value' => '',
            'local_handling_cost_attribute' => '',

            'international_handling_cost_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_NONE,
            'international_handling_cost_value' => '',
            'international_handling_cost_attribute' => '',

            'excluded_locations' => json_encode(array()),

            'international_trade' => self::INTERNATIONAL_TRADE_NONE,
            //----------------------------------

            //----------------------------------
            'services' => array()
            //----------------------------------
        );
    }

    // #######################################

    public function getAffectedListingProducts($asObjects = false, $key = NULL)
    {
        if (is_null($this->getId())) {
            throw new LogicException('Method require loaded instance first');
        }

        $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;

        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate($template);

        $listingProducts = $templateManager->getAffectedItems(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT,
            $this->getId(), array(), $asObjects, $key
        );

        $ids = array();
        foreach ($listingProducts as $listingProduct) {
            $ids[] = is_null($key) ? $listingProduct['id'] : $listingProduct;
        }

        $listingProducts && $listingProducts = array_combine($ids, $listingProducts);

        $listings = $templateManager->getAffectedItems(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING,
            $this->getId()
        );

        foreach ($listings as $listing) {

            $tempListingProducts = $listing->getChildObject()
                                           ->getAffectedListingProducts($template,$asObjects,$key);

            foreach ($tempListingProducts as $listingProduct) {
                $id = is_null($key) ? $listingProduct['id'] : $listingProduct;
                !isset($listingProducts[$id]) && $listingProducts[$id] = $listingProduct;
            }
        }

        return array_values($listingProducts);
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        if (!$this->getResource()->isDifferent($newData,$oldData)) {
            return;
        }

        $ids = $this->getAffectedListingProducts(false, 'id');

        if (empty($ids)) {
            return;
        }

        $templates = array('shippingTemplate');

        Mage::getSingleton('core/resource')->getConnection('core_read')->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing_Product'),
            array(
                'synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED,
                'synch_reasons' => new Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',',$templates)."',
                        CONCAT(synch_reasons,'".','.implode(',',$templates)."')
                    )"
                )
            ),
            array('id IN ('.implode(',', $ids).')')
        );
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_shipping');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_shipping');
        return parent::delete();
    }

    // #######################################
}