<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Shipping
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    const SHIPPING_TYPE_FLAT       = 'flat';
    const SHIPPING_TYPE_CALCULATED = 'calculated';
    const SHIPPING_TYPE_FREIGHT    = 'freight';
    const SHIPPING_TYPE_LOCAL      = 'local';

    const MEASUREMENT_SYSTEM_ENGLISH = 'English';
    const MEASUREMENT_SYSTEM_METRIC  = 'Metric';

    const CROSS_BORDER_TRADE_NONE           = 'None';
    const CROSS_BORDER_TRADE_NORTH_AMERICA  = 'North America';
    const CROSS_BORDER_TRADE_UNITED_KINGDOM = 'UK';

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplate = NULL;

    private $calculatedShippingData = NULL;

    // ########################################

    public function getData()
    {
        $data = array(
            'country' => $this->getShippingTemplate()->getCountry(),
            'address' => $this->getShippingTemplate()->getAddress(),
            'postal_code' => $this->getShippingTemplate()->getPostalCode()
        );

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled() ||
            $this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {

            $data['dispatch_time'] = $this->getShippingTemplate()->getDispatchTime();

            // there are permissions by marketplace (interface management)
            $data['cash_on_delivery_cost'] = $this->getShippingTemplate()->getCashOnDeliveryCost();

            // there are permissions by marketplace (interface management)
            if ($this->getShippingTemplate()->isCrossBorderTradeNorthAmerica()) {
                $data['cross_border_trade'] = self::CROSS_BORDER_TRADE_NORTH_AMERICA;
            } else if ($this->getShippingTemplate()->isCrossBorderTradeUnitedKingdom()) {
                $data['cross_border_trade'] = self::CROSS_BORDER_TRADE_UNITED_KINGDOM;
            } else {
                $data['cross_border_trade'] = self::CROSS_BORDER_TRADE_NONE;
            }

            foreach ($this->getShippingTemplate()->getExcludedLocations() as $location) {
                $data['excluded_locations'][] = $location['code'];
            }

            // there are permissions by marketplace (interface management)
            $data['global_shipping_program'] = $this->getShippingTemplate()->isGlobalShippingProgramEnabled();
        }

        return array(
            'shipping' => array_merge(
                $data,
                $this->getShippingData()
            )
        );
    }

    // ########################################

    public function getShippingData()
    {
        $shippingData = array();

        $shippingData['local'] = $this->getLocalShippingData();

        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled() ||
            $this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return $shippingData;
        }

        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            $shippingData['calculated'] = $this->getCalculatedData();
        }

        if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled() ||
            $this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {

            $shippingData['international'] = $this->getInternationalShippingData();

            if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
                if (!isset($shippingData['calculated'])) {
                    $shippingData['calculated'] = $this->getCalculatedData();
                }
            }
        }

        if (!isset($shippingData['calculated'])) {

            if (($this->getShippingTemplate()->isLocalShippingFlatEnabled() &&
                 $this->getShippingTemplate()->isLocalShippingRateTableEnabled()) ||
                ($this->getShippingTemplate()->isInternationalShippingFlatEnabled() &&
                 $this->getShippingTemplate()->isInternationalShippingRateTableEnabled())) {

                $calculatedData = $this->getCalculatedData();
                unset($calculatedData['package_size']);
                unset($calculatedData['dimensions']);
                $shippingData['calculated'] = $calculatedData;
            }
        }

        return $shippingData;
    }

    public function getCalculatedData()
    {
        if (!is_null($this->calculatedShippingData)) {
            return $this->calculatedShippingData;
        }

        $calculated = $this->getShippingTemplate()->getCalculatedShipping();

        if (is_null($calculated)) {
            return array();
        }

        $data = array(
            'package_size' => $calculated->getPackageSize(),
            'dimensions' => $calculated->getDimension(),
            'weight' => $calculated->getWeight()
        );

        switch ($calculated->getMeasurementSystem()) {
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH:
                $data['measurement_system'] = self::MEASUREMENT_SYSTEM_ENGLISH;
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_METRIC:
                $data['measurement_system'] = self::MEASUREMENT_SYSTEM_METRIC;
                break;
        }

        return $this->calculatedShippingData = $data;
    }

    // ########################################

    public function getLocalShippingData()
    {
        $data = array(
            'type' => $this->getLocalType()
        );

        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled() ||
            $this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return $data;
        }

        $data['discount_enabled'] = $this->getShippingTemplate()->isLocalShippingDiscountEnabled();
        $data['discount_profile_id'] = $this->getShippingTemplate()
                                            ->getLocalShippingDiscountProfileId(
                                                $this->getListingProduct()->getListing()->getAccountId()
                                            );

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled()) {
            // there are permissions by marketplace (interface management)
            $data['rate_table_enabled'] = $this->getShippingTemplate()->isLocalShippingRateTableEnabled();
        }

        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            $data['handing_cost'] = $this->getShippingTemplate()->getCalculatedShipping()->getLocalHandlingCost();
        }

        $data['methods'] = $this->getLocalServices();

        return $data;
    }

    // ----------------------------------------

    private function getLocalType()
    {
        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled()) {
            return self::SHIPPING_TYPE_LOCAL;
        }
        if ($this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return self::SHIPPING_TYPE_FREIGHT;
        }
        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled()) {
            return self::SHIPPING_TYPE_FLAT;
        }
        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            return self::SHIPPING_TYPE_CALCULATED;
        }
    }

    private function getLocalServices()
    {
        $services = array();

        foreach ($this->getShippingTemplate()->getServices(true) as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeLocal()) {
               continue;
            }

            $tempDataMethod = array(
                'service' => $service->getShippingValue()
            );

            if ($this->getShippingTemplate()->isLocalShippingFlatEnabled()) {

                $tempDataMethod['cost'] = $service->getCost();
                $tempDataMethod['cost_additional'] = $service->getCostAdditional();

                if (in_array($this->getShippingTemplate()->getMarketplaceId(), array(
                        Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_US,
                        Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS,
                    )) && preg_match('/(FedEx|UPS)/', $service->getShippingValue())) {

                    $tempDataMethod['cost_surcharge'] = $service->getCostSurcharge();
                }
            }

            if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
                $tempDataMethod['is_free'] = $service->isCostModeFree();
            }

            $services[] = $tempDataMethod;
        }

        return $services;
    }

    // ########################################

    public function getInternationalShippingData()
    {
        $data = array(
            'type' => $this->getInternationalType()
        );

        $data['discount_enabled'] = $this->getShippingTemplate()->isInternationalShippingDiscountEnabled();
        $data['discount_profile_id'] = $this->getShippingTemplate()
                                            ->getInternationalShippingDiscountProfileId(
                                                $this->getListingProduct()->getListing()->getAccountId()
                                            );

        if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
            // there are permissions by marketplace (interface management)
            $data['rate_table_enabled'] = $this->getShippingTemplate()->isInternationalShippingRateTableEnabled();
        }

        if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            $data['handing_cost'] = $this->getShippingTemplate()->getCalculatedShipping()
                                            ->getInternationalHandlingCost();
        }

        $data['methods'] = $this->getInternationalServices();

        return $data;
    }

    // ----------------------------------------

    private function getInternationalType()
    {
        if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
            return self::SHIPPING_TYPE_FLAT;
        }

        if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            return self::SHIPPING_TYPE_CALCULATED;
        }
    }

    private function getInternationalServices()
    {
        $services = array();

        foreach ($this->getShippingTemplate()->getServices(true) as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeInternational()) {
               continue;
            }

            $tempDataMethod = array(
                'service' => $service->getShippingValue(),
                'locations' => $service->getLocations()
            );

            if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
                $tempDataMethod['cost'] = $service->getCost();
                $tempDataMethod['cost_additional'] = $service->getCostAdditional();
            }

            $services[] = $tempDataMethod;
        }

        return $services;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private function getShippingTemplate()
    {
        if (is_null($this->shippingTemplate)) {
            $this->shippingTemplate = $this->getListingProduct()
                                           ->getChildObject()
                                           ->getShippingTemplate();
        }
        return $this->shippingTemplate;
    }

    // ########################################
}