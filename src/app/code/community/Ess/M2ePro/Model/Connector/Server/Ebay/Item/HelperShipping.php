<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_HelperShipping
{
    // ########################################

    public function getRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['shipping'] = array();

        /** @var $shippingTemplate Ess_M2ePro_Model_Ebay_Template_Shipping */
        $shippingTemplate = $listingProduct->getChildObject()->getShippingTemplate();
        $shippingTemplate->setMagentoProduct($listingProduct->getMagentoProduct());

        $this->addLocalShippingData($shippingTemplate,$requestData);
        $this->addInternationalShippingData($shippingTemplate,$requestData);

        if ($shippingTemplate->isLocalShippingFlatEnabled()
            && $shippingTemplate->isLocalShippingRateTableEnabled()
            && !$shippingTemplate->isInternationalShippingCalculatedEnabled()
            && !isset($requestData['shipping']['calculated'])
        ) {
            $calculatedData = $this->getCalculatedData($shippingTemplate, $listingProduct);
            unset($calculatedData['package_size']);
            unset($calculatedData['originating_postal_code']);
            unset($calculatedData['dimensions']);
            $requestData['shipping']['calculated'] = $calculatedData;
        }

        $this->addAdditionalData($shippingTemplate,$requestData);
        $this->addLocationData($shippingTemplate,$requestData);
        $this->addInternationalTradeData($shippingTemplate,$requestData);
    }

    // ########################################

    protected function getCalculatedData(Ess_M2ePro_Model_Ebay_Template_Shipping $shippingTemplate)
    {
        if (is_null($shippingTemplate->getCalculatedShipping())) {
            return array();
        }

        $measurementSystem = $shippingTemplate->getCalculatedShipping()->getMeasurementSystem();

        $calculatedData = array(
            'measurement_system' => $measurementSystem,
            'package_size' => $shippingTemplate->getCalculatedShipping()->getPackageSize(),
            'originating_postal_code' => $shippingTemplate->getCalculatedShipping()->getOriginatingPostalCode(),
            'dimensions' => $shippingTemplate->getCalculatedShipping()->getDimension(),
            'weight' => $shippingTemplate->getCalculatedShipping()->getWeight()
        );

        if ($measurementSystem ==
            Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH) {
            $calculatedData['measurement_system'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::EBAY_MEASUREMENT_SYSTEM_ENGLISH;
        }
        if ($measurementSystem ==
            Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_METRIC) {
            $calculatedData['measurement_system'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::EBAY_MEASUREMENT_SYSTEM_METRIC;
        }

        return $calculatedData;
    }

    //-----------------------------------------

    protected function addLocalShippingData(Ess_M2ePro_Model_Ebay_Template_Shipping $shippingTemplate,
                                            array &$requestData)
    {
        $requestData['use_local_shipping_rate_table'] =
            $shippingTemplate->isLocalShippingRateTableEnabled();

        $requestData['shipping']['local'] = array();

        if ($shippingTemplate->isLocalShippingFreightEnabled()) {
            $requestData['shipping']['local']['type'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping::EBAY_SHIPPING_TYPE_FREIGHT;
        }

        if ($shippingTemplate->isLocalShippingLocalEnabled()) {
            $requestData['shipping']['local']['type'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping::EBAY_SHIPPING_TYPE_LOCAL;
        }

        if ($shippingTemplate->isLocalShippingFlatEnabled()) {
            $requestData['shipping']['local']['type'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping::EBAY_SHIPPING_TYPE_FLAT;
        }

        if ($shippingTemplate->isLocalShippingCalculatedEnabled()) {

            $requestData['shipping']['local']['type'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping::EBAY_SHIPPING_TYPE_CALCULATED;

            $requestData['shipping']['local']['handing_fee'] =
                $shippingTemplate->getCalculatedShipping()->getLocalHandling();

            $requestData['shipping']['calculated'] = $this->getCalculatedData($shippingTemplate);
        }

        if (!$shippingTemplate->isLocalShippingFlatEnabled() &&
            !$shippingTemplate->isLocalShippingCalculatedEnabled()) {
            return;
        }

        $requestData['shipping']['get_it_fast'] = $shippingTemplate->isGetItFastEnabled();
        $requestData['shipping']['dispatch_time'] = $shippingTemplate->getDispatchTime();

        $requestData['shipping']['local']['cash_on_delivery'] =
            $shippingTemplate->isLocalShippingCashOnDeliveryEnabled();

        $requestData['shipping']['local']['cash_on_delivery_cost'] =
            $shippingTemplate->getLocalShippingCashOnDeliveryCost();

        $requestData['shipping']['local']['discount'] =
            $shippingTemplate->isLocalShippingDiscountEnabled();

        $requestData['shipping']['local']['combined_discount_profile'] =
            $shippingTemplate->getLocalShippingCombinedDiscountProfileId();

        $requestData['shipping']['local']['methods'] = array();

        $services = $shippingTemplate->getServices(true);

        foreach ($services as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeLocal()) {
               continue;
            }

            $tempDataMethod = array(
                'service' => $service->getShippingValue()
            );

            if ($shippingTemplate->isLocalShippingFlatEnabled()) {
                $tempDataMethod['cost'] = $service->getCost();
                $tempDataMethod['cost_additional'] = $service->getCostAdditional();
            }

            if ($shippingTemplate->isLocalShippingCalculatedEnabled()) {
                $tempDataMethod['is_free'] = $service->isCostModeFree();
            }

            $requestData['shipping']['local']['methods'][] = $tempDataMethod;
        }
    }

    protected function addInternationalShippingData(Ess_M2ePro_Model_Ebay_Template_Shipping $shippingTemplate,
                                                    array &$requestData)
    {
        if ($shippingTemplate->isInternationalShippingNoInternationalEnabled() ||
            $shippingTemplate->isLocalShippingFreightEnabled() ||
            $shippingTemplate->isLocalShippingLocalEnabled()) {
            return;
        }

        $requestData['use_international_shipping_rate_table'] =
            $shippingTemplate->isInternationalShippingRateTableEnabled();

        $requestData['shipping']['international'] = array();

        if ($shippingTemplate->isInternationalShippingFlatEnabled()) {
            $requestData['shipping']['international']['type'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping::EBAY_SHIPPING_TYPE_FLAT;
        }

        if ($shippingTemplate->isInternationalShippingCalculatedEnabled()) {

            $requestData['shipping']['international']['type'] =
                Ess_M2ePro_Model_Ebay_Template_Shipping::EBAY_SHIPPING_TYPE_CALCULATED;

            $requestData['shipping']['international']['handing_fee'] =
                $shippingTemplate->getCalculatedShipping()->getInternationalHandling();

            if (!isset($requestData['shipping']['calculated'])) {
                $requestData['shipping']['calculated'] = $this->getCalculatedData($shippingTemplate);
            }
        }

        $requestData['shipping']['international']['discount'] =
            $shippingTemplate->isInternationalShippingDiscountEnabled();

        $requestData['shipping']['international']['combined_discount_profile'] =
            $shippingTemplate->getInternationalShippingCombinedDiscountProfileId();

        $requestData['shipping']['international']['methods'] = array();

        $services = $shippingTemplate->getServices(true);

        foreach ($services as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeInternational()) {
               continue;
            }

            $tempDataMethod = array(
                'service' => $service->getShippingValue(),
                'locations' => $service->getLocations()
            );

            if ($shippingTemplate->isInternationalShippingFlatEnabled()) {
                $tempDataMethod['cost'] = $service->getCost();
                $tempDataMethod['cost_additional'] = $service->getCostAdditional();
            }

            $requestData['shipping']['international']['methods'][] = $tempDataMethod;
        }
    }

    // ########################################

    protected function addAdditionalData(Ess_M2ePro_Model_Ebay_Template_Shipping $shippingTemplate,
                                         array &$requestData)
    {
        $requestData['vat_percent'] = $shippingTemplate->getVatPercent();
        $requestData['use_tax_table'] = $shippingTemplate->isTaxTableEnabled();
    }

    protected function addLocationData(Ess_M2ePro_Model_Ebay_Template_Shipping $shippingTemplate,
                                       array &$requestData)
    {
        $requestData['country'] = $shippingTemplate->getCountry();
        $requestData['postal_code'] = $shippingTemplate->getPostalCode();
        $requestData['address'] = $shippingTemplate->getAddress();
    }

    protected function addInternationalTradeData(Ess_M2ePro_Model_Ebay_Template_Shipping $shippingTemplate,
                                                 array &$requestData)
    {
        $requestData['international_trade'] = 'None';

        if ($shippingTemplate->isInternationalTradeNorthAmerica()) {
            $requestData['international_trade'] = 'North America';
        }

        if ($shippingTemplate->isInternationalTradeUnitedKingdom()) {
            $requestData['international_trade'] = 'UK';
        }
    }

    // ########################################
}