<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_Shipping getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    const SHIPPING_TYPE_FLAT                = 0;
    const SHIPPING_TYPE_CALCULATED          = 1;
    const SHIPPING_TYPE_FREIGHT             = 2;
    const SHIPPING_TYPE_LOCAL               = 3;
    const SHIPPING_TYPE_NO_INTERNATIONAL    = 4;

    const CROSS_BORDER_TRADE_NONE           = 0;
    const CROSS_BORDER_TRADE_NORTH_AMERICA  = 1;
    const CROSS_BORDER_TRADE_UNITED_KINGDOM = 2;

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
        return (int)$this->getData('dispatch_time');
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

    public function getLocalShippingDiscountProfileId($accountId)
    {
        $data = $this->getData('local_shipping_discount_profile_id');

        if (is_null($data)) {
            return NULL;
        }

        $data = json_decode($data, true);

        return !isset($data[$accountId]) ? NULL : $data[$accountId];
    }

    //---------------------------------------

    public function isClickAndCollectEnabled()
    {
        return (bool)$this->getData('click_and_collect_mode');
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

    public function getInternationalShippingDiscountProfileId($accountId)
    {
        $data = $this->getData('international_shipping_discount_profile_id');

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

    public function getCashOnDeliveryCost()
    {
        $tempData = $this->getData('cash_on_delivery_cost');

        if (!empty($tempData)) {
            return (float)$tempData;
        }

        return NULL;
    }

    //---------------------------------------

    public function getCrossBorderTrade()
    {
        return (int)$this->getData('cross_border_trade');
    }

    public function isCrossBorderTradeNone()
    {
        return $this->getCrossBorderTrade() == self::CROSS_BORDER_TRADE_NONE;
    }

    public function isCrossBorderTradeNorthAmerica()
    {
        return $this->getCrossBorderTrade() == self::CROSS_BORDER_TRADE_NORTH_AMERICA;
    }

    public function isCrossBorderTradeUnitedKingdom()
    {
        return $this->getCrossBorderTrade() == self::CROSS_BORDER_TRADE_UNITED_KINGDOM;
    }

    // #######################################

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
        $attributes = array();

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

            'dispatch_time' => 1,
            'cash_on_delivery_cost' => NULL,
            'global_shipping_program' => 0,
            'cross_border_trade' => self::CROSS_BORDER_TRADE_NONE,
            'excluded_locations' => json_encode(array()),

            'local_shipping_mode' =>  self::SHIPPING_TYPE_FLAT,
            'local_shipping_discount_mode' => 0,
            'local_shipping_discount_profile_id' => json_encode(array()),
            'local_shipping_rate_table_mode' => 0,
            'click_and_collect_mode' => 1,

            'international_shipping_mode' => self::SHIPPING_TYPE_NO_INTERNATIONAL,
            'international_shipping_discount_mode' => 0,
            'international_shipping_discount_profile_id' => json_encode(array()),
            'international_shipping_rate_table_mode' => 0,

            // CALCULATED SHIPPING
            //----------------------------------
            'measurement_system' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH,

            'package_size_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::PACKAGE_SIZE_CUSTOM_VALUE,
            'package_size_value' => 'None',
            'package_size_attribute' => '',

            'dimension_mode'   => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::DIMENSION_NONE,
            'dimension_width_value'  => '',
            'dimension_length_value' => '',
            'dimension_depth_value'  => '',
            'dimension_width_attribute'  => '',
            'dimension_length_attribute' => '',
            'dimension_depth_attribute'  => '',

            'weight_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_NONE,
            'weight_minor' => '',
            'weight_major' => '',
            'weight_attribute' => '',

            'local_handling_cost' => NULL,
            'international_handling_cost' => NULL,
            //----------------------------------

            //----------------------------------
            'services' => array()
            //----------------------------------
        );
    }

    // #######################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays, $columns
        );

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
                                                $asArrays, $columns
                                            );

            foreach ($tempListingsProducts as $listingProduct) {
                if (!isset($listingsProducts[$listingProduct['id']])) {
                    $listingsProducts[$listingProduct['id']] = $listingProduct;
                }
            }
        }

        return $listingsProducts;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
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