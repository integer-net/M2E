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

    const INTERNATIONAL_TRADE_NONE           = 'None';
    const INTERNATIONAL_TRADE_NORTH_AMERICA  = 'North America';
    const INTERNATIONAL_TRADE_UNITED_KINGDOM = 'UK';

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
            'postal_code' => $this->getShippingTemplate()->getPostalCode(),
            'international_trade' => $this->getInternationalTrade()
        );

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled() ||
            $this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {

            $data['use_local_shipping_rate_table'] =
                $this->getShippingTemplate()->isLocalShippingRateTableEnabled();

            if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled() ||
                $this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {

                $data['use_international_shipping_rate_table'] =
                    $this->getShippingTemplate()->isInternationalShippingRateTableEnabled();
            }
        }

        $data['shipping'] = $this->getShippingData();

        return $data;
    }

    // ########################################

    public function getShippingData()
    {
        $shippingData = array();

        $shippingData['local'] = $this->getLocalShippingData();

        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            $shippingData['calculated'] = $this->getCalculatedData();
        }

        if (($this->getShippingTemplate()->isLocalShippingFlatEnabled() ||
             $this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) &&
            ($this->getShippingTemplate()->isInternationalShippingFlatEnabled() ||
             $this->getShippingTemplate()->isInternationalShippingCalculatedEnabled())) {

            $shippingData['international'] = $this->getInternationalShippingData();

            if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
                if (!isset($shippingData['calculated'])) {
                    $shippingData['calculated'] = $this->getCalculatedData();
                }
            }
        }

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled() ||
            $this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {

            $shippingData['get_it_fast'] = $this->getShippingTemplate()->isGetItFastEnabled();
            $shippingData['dispatch_time'] = $this->getShippingTemplate()->getDispatchTime();

            if ($shippingData['dispatch_time'] === '') {
                unset($shippingData['dispatch_time']);
            }
        }

        // get Measurement System and Weight Source if calculated is disabled
        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled() &&
            $this->getShippingTemplate()->isLocalShippingRateTableEnabled() &&
            !$this->getShippingTemplate()->isInternationalShippingCalculatedEnabled() &&
            !isset($shippingData['calculated'])
        ) {
            $calculatedData = $this->getCalculatedData();
            unset($calculatedData['package_size']);
            unset($calculatedData['originating_postal_code']);
            unset($calculatedData['dimensions']);
            $shippingData['calculated'] = $calculatedData;
        }

        $shippingData['excluded_locations'] = $this->getExcludedLocations();
        $shippingData['global_shipping_program'] = $this->getShippingTemplate()->isGlobalShippingProgramEnabled();

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
            'originating_postal_code' => $calculated->getOriginatingPostalCode(),
            'package_size' => $calculated->getPackageSize(),
            'dimensions' => $calculated->getDimension(),
            'weight' => $calculated->getWeight()
        );

        $measurementSystem = $calculated->getMeasurementSystem();

        if ($measurementSystem == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH) {
            $data['measurement_system'] = self::MEASUREMENT_SYSTEM_ENGLISH;
        }
        if ($measurementSystem == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_METRIC) {
            $data['measurement_system'] = self::MEASUREMENT_SYSTEM_METRIC;
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

        $data['discount'] = $this->getShippingTemplate()->isLocalShippingDiscountEnabled();
        $data['combined_discount_profile'] = $this->getShippingTemplate()
                                                  ->getLocalShippingCombinedDiscountProfileId(
                                                      $this->getListingProduct()->getListing()->getAccountId()
                                                  );

        $data['cash_on_delivery'] = $this->getShippingTemplate()->isLocalShippingCashOnDeliveryEnabled();
        $data['cash_on_delivery_cost'] = $this->getShippingTemplate()->getLocalShippingCashOnDeliveryCost();

        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            $data['handing_fee'] = $this->getShippingTemplate()->getCalculatedShipping()->getLocalHandling();
        }

        $data['methods'] = $this->getLocalServices();

        return $data;
    }

    // ----------------------------------------

    private function getLocalType()
    {
        if ($this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return self::SHIPPING_TYPE_FREIGHT;
        }
        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled()) {
            return self::SHIPPING_TYPE_LOCAL;
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
            'type' => $this->getInternationalType(),
            'discount' => $this->getShippingTemplate()->isInternationalShippingDiscountEnabled(),
            'combined_discount_profile' => $this->getShippingTemplate()
                                                ->getInternationalShippingCombinedDiscountProfileId(
                                                    $this->getListingProduct()->getListing()->getAccountId()
                                                )
        );

        if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            $data['handing_fee'] = $this->getShippingTemplate()->getCalculatedShipping()->getInternationalHandling();
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

    private function getInternationalTrade()
    {
        if ($this->getShippingTemplate()->isInternationalTradeNorthAmerica()) {
            $internationalTrade = self::INTERNATIONAL_TRADE_NORTH_AMERICA;
        } else if ($this->getShippingTemplate()->isInternationalTradeUnitedKingdom()) {
            $internationalTrade = self::INTERNATIONAL_TRADE_UNITED_KINGDOM;
        } else {
            $internationalTrade = self::INTERNATIONAL_TRADE_NONE;
        }

        return $internationalTrade;
    }

    private function getExcludedLocations()
    {
        $data = array();

        foreach ($this->getShippingTemplate()->getExcludedLocations() as $location) {
            $data[] = $location['code'];
        }

        return $data;
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