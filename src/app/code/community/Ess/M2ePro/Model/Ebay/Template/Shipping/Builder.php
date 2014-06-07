<?php

class Ess_M2ePro_Model_Ebay_Template_Shipping_Builder
    extends Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    // ########################################

    public function build(array $data)
    {
        if (empty($data)) {
            return NULL;
        }

        // validate input data
        //------------------------------
        $this->validate($data);
        //------------------------------

        // prepare input data
        //------------------------------
        $generalData = $this->prepareGeneralData($data);
        //------------------------------

        //------------------------------
        $marketplace = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            'Marketplace',
            $generalData['marketplace_id']
        );
        //------------------------------

        // create template
        //------------------------------
        $template = Mage::getModel('M2ePro/Ebay_Template_Shipping');

        if (isset($generalData['id'])) {
            $template->load($generalData['id']);
        }

        $template->addData($generalData);
        $template->save();
        $template->setMarketplace($marketplace);
        //------------------------------

        // create calculated
        //------------------------------
        $calculatedData = $this->prepareCalculatedData($template->getId(), $data);
        $this->createCalculated($template->getId(), $calculatedData);
        //------------------------------

        // create shipping methods
        //------------------------------
        $servicesData = $this->prepareServicesData($template->getId(), $data);
        $this->createServices($template->getId(), $servicesData);
        //------------------------------

        return $template;
    }

    // ########################################

    protected function validate(array $data)
    {
        //------------------------------
        if (empty($data['marketplace_id'])) {
            throw new LogicException('Marketplace ID is empty.');
        }
        //------------------------------

        //------------------------------
        if (empty($data['country'])) {
            throw new LogicException('Country is empty.');
        }
        //------------------------------

        parent::validate($data);
    }

    // ########################################

    protected function prepareGeneralData(array &$data)
    {
        $prepared = parent::prepareData($data);

        //------------------------------
        $prepared['marketplace_id'] = (int)$data['marketplace_id'];
        //------------------------------

        //------------------------------
        $keys = array(
            'country',
            'postal_code',
            'address',
            'dispatch_time_mode',
            'dispatch_time_value',
            'dispatch_time_attribute',
            'global_shipping_program',
            'local_shipping_rate_table_mode',
            'international_shipping_rate_table_mode',
            'local_shipping_mode',
            'local_shipping_discount_mode',
            'local_shipping_cash_on_delivery_cost_mode',
            'local_shipping_cash_on_delivery_cost_value',
            'local_shipping_cash_on_delivery_cost_attribute',
            'international_shipping_mode',
            'international_shipping_discount_mode',
            'international_trade',
        );

        foreach ($keys as $key) {
            $prepared[$key] = isset($data[$key]) ? $data[$key] : '';
        }

        if (isset($data['local_shipping_combined_discount_profile_id'])) {
            $prepared['local_shipping_combined_discount_profile_id'] =
                json_encode(array_diff($data['local_shipping_combined_discount_profile_id'], array('')));
        }

        if (isset($data['international_shipping_combined_discount_profile_id'])) {
            $prepared['international_shipping_combined_discount_profile_id'] =
                json_encode(array_diff($data['international_shipping_combined_discount_profile_id'], array('')));
        }

        if (isset($data['excluded_locations'])) {
            $prepared['excluded_locations'] = $data['excluded_locations'];
        }

        $key = 'local_shipping_cash_on_delivery_cost_value';
        if ($prepared[$key] !== '') {
            $prepared[$key] = str_replace(',', '.', $prepared[$key]);
        }

        $modes = array(
            'local_shipping_rate_table_mode',
            'international_shipping_rate_table_mode',
            'local_shipping_mode',
            'local_shipping_discount_mode',
            'local_shipping_cash_on_delivery_cost_mode',
            'international_shipping_mode',
            'international_shipping_discount_mode',
            'international_trade'
        );

        foreach ($modes as $mode) {
            $prepared[$mode] = (int)$prepared[$mode];
        }
        //------------------------------

        return $prepared;
    }

    // ########################################

    private function prepareCalculatedData($templateShippingId, array $data)
    {
        // flat local shipping with enabled rate table allows to send measurement & weight data to eBay
        if ($data['local_shipping_mode'] != Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED
            && $data['international_shipping_mode'] != Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED
            && ($data['local_shipping_mode'] != Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT
                ||
                empty($data['local_shipping_rate_table_mode'])
            )
        ) {
            return array();
        }

        $prepared = array('template_shipping_id' => $templateShippingId);

        $keys = array(
            'measurement_system',
            'originating_postal_code',

            'package_size_mode',
            'package_size_value',
            'package_size_attribute',

            'dimension_mode',
            'dimension_width_value',
            'dimension_height_value',
            'dimension_depth_value',
            'dimension_width_attribute',
            'dimension_height_attribute',
            'dimension_depth_attribute',

            'weight_mode',
            'weight_minor',
            'weight_major',
            'weight_attribute',

            'local_handling_cost_mode',
            'local_handling_cost_value',
            'local_handling_cost_attribute',

            'international_handling_cost_mode',
            'international_handling_cost_value',
            'international_handling_cost_attribute'
        );

        foreach ($keys as $key) {
            $prepared[$key] = isset($data[$key]) ? $data[$key] : '';
        }

        return $prepared;
    }

    private function createCalculated($templateShippingId, array $data)
    {
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        $connWrite->delete(
            Mage::getResourceModel('M2ePro/Ebay_Template_Shipping_Calculated')->getMainTable(),
            array(
                'template_shipping_id = ?' => (int)$templateShippingId
            )
        );

        if (empty($data)) {
            return;
        }

        Mage::getModel('M2ePro/Ebay_Template_Shipping_Calculated')->setData($data)->save();
    }

    // ########################################

    private function prepareServicesData($templateShippingId, array $data)
    {
        //------------------------------
        if (isset($data['shipping_type']['%i%'])) {
            unset($data['shipping_type']['%i%']);
        }

        if (isset($data['cost_mode']['%i%'])) {
            unset($data['cost_mode']['%i%']);
        }

        if (isset($data['shipping_priority']['%i%'])) {
            unset($data['shipping_priority']['%i%']);
        }

        if (isset($data['shipping_cost_value']['%i%'])) {
            unset($data['shipping_cost_value']['%i%']);
        }

        if (isset($data['shipping_cost_additional_value']['%i%'])) {
            unset($data['shipping_cost_additional_value']['%i%']);
        }
        //------------------------------

        $services = array();
        foreach ($data['cost_mode'] as $i => $costMode) {

            $locations = array();
            if (isset($data['shippingLocation'][$i])) {
                foreach ($data['shippingLocation'][$i] as $location) {
                    $locations[] = $location;
                }
            }

            $shippingType = $data['shipping_type'][$i] == 'local'
                ? Ess_M2ePro_Model_Ebay_Template_Shipping_Service::SHIPPING_TYPE_LOCAL
                : Ess_M2ePro_Model_Ebay_Template_Shipping_Service::SHIPPING_TYPE_INTERNATIONAL;

            if ($costMode == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {

                $cost = isset($data['shipping_cost_attribute'][$i])
                    ? $data['shipping_cost_attribute'][$i]
                    : '';

                $costAdditional = isset($data['shipping_cost_additional_attribute'][$i])
                    ? $data['shipping_cost_additional_attribute'][$i]
                    : '';
            } else {

                $cost = isset($data['shipping_cost_value'][$i])
                    ? $data['shipping_cost_value'][$i]
                    : '';

                $costAdditional = isset($data['shipping_cost_additional_value'][$i])
                    ? $data['shipping_cost_additional_value'][$i]
                    : '';
            }

            $services[] = array(
                'template_shipping_id'  => $templateShippingId,
                'cost_mode'             => $costMode,
                'cost_value'            => $cost,
                'shipping_value'        => $data['shipping_service'][$i],
                'shipping_type'         => $shippingType,
                'cost_additional_value' => $costAdditional,
                'priority'              => $data['shipping_priority'][$i],
                'locations'             => json_encode($locations)
            );
        }

        return $services;
    }

    private function createServices($templateShippingId, array $data)
    {
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        $connWrite->delete(
            Mage::getResourceModel('M2ePro/Ebay_Template_Shipping_Service')->getMainTable(),
            array(
                'template_shipping_id = ?' => (int)$templateShippingId
            )
        );

        if (empty($data)) {
            return;
        }

        $connWrite->insertMultiple(
            $coreRes->getTableName('M2ePro/Ebay_Template_Shipping_Service'), $data
        );
    }

    // ########################################
}