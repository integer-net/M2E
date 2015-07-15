<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Synchronization_Builder
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
        $data = $this->prepareData($data);
        //------------------------------

        // create template
        //------------------------------
        $template = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_Synchronization');

        if (isset($data['id'])) {
            $template->load($data['id']);
        }

        $template->addData($data);
        $template->save();
        //------------------------------

        return $template;
    }

    // ########################################

    protected function prepareData(array &$data)
    {
        $prepared = parent::prepareData($data);

        //------------------------------
        $isSimpleMode = Mage::helper('M2ePro/View_Ebay')->isSimpleMode();

        $defaultData = $isSimpleMode
            ? Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getDefaultSettingsSimpleMode()
            : Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getDefaultSettingsAdvancedMode();

        $defaultData['schedule_interval_settings'] = json_decode($defaultData['schedule_interval_settings'], true);
        $defaultData['schedule_week_settings'] = json_decode($defaultData['schedule_week_settings'], true);

        $data = Mage::helper('M2ePro')->arrayReplaceRecursive($defaultData, $data);
        //------------------------------

        $prepared = array_merge(
            $prepared,
            $this->prepareListData($data),
            $this->prepareReviseData($data),
            $this->prepareRelistData($data),
            $this->prepareStopData($data),
            $this->prepareScheduleData($data)
        );

        return $prepared;
    }

    //------------------------------

    private function prepareListData(array $data)
    {
        $prepared = array();

        if (isset($data['list_mode'])) {
            $prepared['list_mode'] = (int)$data['list_mode'];
        }

        if (isset($data['list_status_enabled'])) {
            $prepared['list_status_enabled'] = (int)$data['list_status_enabled'];
        }

        if (isset($data['list_is_in_stock'])) {
            $prepared['list_is_in_stock'] = (int)$data['list_is_in_stock'];
        }

        if (isset($data['list_qty_magento'])) {
            $prepared['list_qty_magento'] = (int)$data['list_qty_magento'];
        }

        if (isset($data['list_qty_magento_value'])) {
            $prepared['list_qty_magento_value'] = (int)$data['list_qty_magento_value'];
        }

        if (isset($data['list_qty_magento_value_max'])) {
            $prepared['list_qty_magento_value_max'] = (int)$data['list_qty_magento_value_max'];
        }

        if (isset($data['list_qty_calculated'])) {
            $prepared['list_qty_calculated'] = (int)$data['list_qty_calculated'];
        }

        if (isset($data['list_qty_calculated_value'])) {
            $prepared['list_qty_calculated_value'] = (int)$data['list_qty_calculated_value'];
        }

        if (isset($data['list_qty_calculated_value_max'])) {
            $prepared['list_qty_calculated_value_max'] = (int)$data['list_qty_calculated_value_max'];
        }

        return $prepared;
    }

    private function prepareReviseData(array $data)
    {
        $prepared = array();

        if (isset($data['revise_update_qty'])) {
            $prepared['revise_update_qty'] = (int)$data['revise_update_qty'];
        }

        $key = 'revise_update_qty_max_applied_value_mode';
        if (isset($data[$key])) {
            $prepared[$key] = (int)$data[$key];
        }

        if (isset($data['revise_update_qty_max_applied_value'])) {
            $prepared['revise_update_qty_max_applied_value'] = (int)$data['revise_update_qty_max_applied_value'];
        }

        if (isset($data['revise_update_price'])) {
            $prepared['revise_update_price'] = (int)$data['revise_update_price'];
        }

        $key = 'revise_update_price_max_allowed_deviation_mode';
        if (isset($data[$key])) {
            $prepared[$key] = (int)$data[$key];
        }

        $key = 'revise_update_price_max_allowed_deviation';
        if (isset($data[$key])) {
            $prepared[$key] = (int)$data[$key];
        }

        if (isset($data['revise_update_title'])) {
            $prepared['revise_update_title'] = (int)$data['revise_update_title'];
        }

        if (isset($data['revise_update_sub_title'])) {
            $prepared['revise_update_sub_title'] = (int)$data['revise_update_sub_title'];
        }

        if (isset($data['revise_update_description'])) {
            $prepared['revise_update_description'] = (int)$data['revise_update_description'];
        }

        if (isset($data['revise_update_images'])) {
            $prepared['revise_update_images'] = (int)$data['revise_update_images'];
        }

        //------------------------------

        if (isset($data['revise_change_selling_format_template'])) {
            $prepared['revise_change_selling_format_template'] = (int)$data['revise_change_selling_format_template'];
        }

        if (isset($data['revise_change_description_template'])) {
            $prepared['revise_change_description_template'] = (int)$data['revise_change_description_template'];
        }

        if (isset($data['revise_change_category_template'])) {
            $prepared['revise_change_category_template'] = (int)$data['revise_change_category_template'];
        }

        if (isset($data['revise_change_payment_template'])) {
            $prepared['revise_change_payment_template'] = (int)$data['revise_change_payment_template'];
        }

        if (isset($data['revise_change_shipping_template'])) {
            $prepared['revise_change_shipping_template'] = (int)$data['revise_change_shipping_template'];
        }

        if (isset($data['revise_change_return_template'])) {
            $prepared['revise_change_return_template'] = (int)$data['revise_change_return_template'];
        }

        return $prepared;
    }

    private function prepareRelistData(array $data)
    {
        $prepared = array();

        if (isset($data['relist_mode'])) {
            $prepared['relist_mode'] = (int)$data['relist_mode'];
        }

        if (isset($data['relist_filter_user_lock'])) {
            $prepared['relist_filter_user_lock'] = (int)$data['relist_filter_user_lock'];
        }

        if (isset($data['relist_send_data'])) {
            $prepared['relist_send_data'] = (int)$data['relist_send_data'];
        }

        if (isset($data['relist_status_enabled'])) {
            $prepared['relist_status_enabled'] = (int)$data['relist_status_enabled'];
        }

        if (isset($data['relist_is_in_stock'])) {
            $prepared['relist_is_in_stock'] = (int)$data['relist_is_in_stock'];
        }

        if (isset($data['relist_qty_magento'])) {
            $prepared['relist_qty_magento'] = (int)$data['relist_qty_magento'];
        }

        if (isset($data['relist_qty_magento_value'])) {
            $prepared['relist_qty_magento_value'] = (int)$data['relist_qty_magento_value'];
        }

        if (isset($data['relist_qty_magento_value_max'])) {
            $prepared['relist_qty_magento_value_max'] = (int)$data['relist_qty_magento_value_max'];
        }

        if (isset($data['relist_qty_calculated'])) {
            $prepared['relist_qty_calculated'] = (int)$data['relist_qty_calculated'];
        }

        if (isset($data['relist_qty_calculated_value'])) {
            $prepared['relist_qty_calculated_value'] = (int)$data['relist_qty_calculated_value'];
        }

        if (isset($data['relist_qty_calculated_value_max'])) {
            $prepared['relist_qty_calculated_value_max'] = (int)$data['relist_qty_calculated_value_max'];
        }

        return $prepared;
    }

    private function prepareStopData(array $data)
    {
        $prepared = array();

        if (isset($data['stop_status_disabled'])) {
            $prepared['stop_status_disabled'] = (int)$data['stop_status_disabled'];
        }

        if (isset($data['stop_out_off_stock'])) {
            $prepared['stop_out_off_stock'] = (int)$data['stop_out_off_stock'];
        }

        if (isset($data['stop_qty_magento'])) {
            $prepared['stop_qty_magento'] = (int)$data['stop_qty_magento'];
        }

        if (isset($data['stop_qty_magento_value'])) {
            $prepared['stop_qty_magento_value'] = (int)$data['stop_qty_magento_value'];
        }

        if (isset($data['stop_qty_magento_value_max'])) {
            $prepared['stop_qty_magento_value_max'] = (int)$data['stop_qty_magento_value_max'];
        }

        if (isset($data['stop_qty_calculated'])) {
            $prepared['stop_qty_calculated'] = (int)$data['stop_qty_calculated'];
        }

        if (isset($data['stop_qty_calculated_value'])) {
            $prepared['stop_qty_calculated_value'] = (int)$data['stop_qty_calculated_value'];
        }

        if (isset($data['stop_qty_calculated_value_max'])) {
            $prepared['stop_qty_calculated_value_max'] = (int)$data['stop_qty_calculated_value_max'];
        }

        return $prepared;
    }

    private function prepareScheduleData(array $data)
    {
        $prepared = array();

        if (isset($data['schedule_mode'])) {
            $prepared['schedule_mode'] = (int)$data['schedule_mode'];
        }

        //--
        $intervalSettings = array(
            'mode'      => 0,
            'date_from' => null,
            'date_to'   => null
        );

        if ($prepared['schedule_mode'] && isset($data['schedule_interval_settings']['mode'])) {
            $intervalSettings['mode'] = (int)$data['schedule_interval_settings']['mode'];
        }

        if ($intervalSettings['mode'] &&
            isset($data['schedule_interval_settings']['date_from']) &&
            isset($data['schedule_interval_settings']['date_to'])) {

            $intervalSettings['date_from'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                $data['schedule_interval_settings']['date_from'].' 00:00:00'
            );

            $intervalSettings['date_to'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                $data['schedule_interval_settings']['date_to'].' 23:59:59'
            );
        }

        $prepared['schedule_interval_settings'] = json_encode($intervalSettings);
        //--

        //--
        $weekSettings = array();
        if (isset($data['schedule_week_days']) && $prepared['schedule_mode']) {

            foreach ($data['schedule_week_days'] as $weekDay) {

                if (!empty($data['schedule_week_settings'][$weekDay]['time_from']) &&
                    !empty($data['schedule_week_settings'][$weekDay]['time_to'])) {

                    $timeInfo = $data['schedule_week_settings'][$weekDay];

                    $weekSettings[$weekDay] = array(
                        'time_from' => date('H:i:s', strtotime($timeInfo['time_from'])),
                        'time_to'   => date('H:i:s', strtotime($timeInfo['time_to']))
                    );
                }
            }
        }

        $prepared['schedule_week_settings'] = json_encode($weekSettings);
        //--

        return $prepared;
    }

    // ########################################
}