<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Tabs_Schedule
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Data
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateSynchronizationEditFormTabsSchedule');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/synchronization/form/tabs/schedule.phtml');
    }

    // ####################################

    public function getFormData()
    {
        $data = parent::getFormData();

        //--
        if (!empty($data['schedule_interval_settings']) && is_string($data['schedule_interval_settings'])) {

            $scheduleIntervalSettings = json_decode($data['schedule_interval_settings'], true);
            unset($data['schedule_interval_settings']);

            if (isset($scheduleIntervalSettings['mode'])) {
                $data['schedule_interval_settings']['mode'] = $scheduleIntervalSettings['mode'];
            }

            if (isset($scheduleIntervalSettings['date_from'])) {
                $data['schedule_interval_settings']['date_from'] =
                    Mage::helper('M2ePro')->gmtDateToTimezone($scheduleIntervalSettings['date_from'],false,'Y-m-d');
            }

            if (isset($scheduleIntervalSettings['date_to'])) {
                $data['schedule_interval_settings']['date_to'] =
                    Mage::helper('M2ePro')->gmtDateToTimezone($scheduleIntervalSettings['date_to'],false,'Y-m-d');
            }
        } else {
            unset($data['schedule_interval_settings']);
        }
        //--

        //--
        if (!empty($data['schedule_week_settings']) && is_string($data['schedule_week_settings'])) {

            $scheduleWeekSettings = json_decode($data['schedule_week_settings'], true);
            unset($data['schedule_week_settings']);

            $parsedSettings = array();
            foreach ($scheduleWeekSettings as $day => $scheduleDaySettings) {

                $convertedTimeFrom = Mage::helper('M2ePro')->gmtDateToTimezone(
                    $scheduleDaySettings['time_from'], false, 'g:i:a'
                );
                $convertedTimeTo = Mage::helper('M2ePro')->gmtDateToTimezone(
                    $scheduleDaySettings['time_to'], false, 'g:i:a'
                );

                $convertedTimeFrom = explode(':',$convertedTimeFrom);
                $convertedTimeTo = explode(':',$convertedTimeTo);

                $parsedSettings[$day] = array(
                    'hours_from'   => $convertedTimeFrom[0],
                    'minutes_from' => $convertedTimeFrom[1],
                    'appm_from'    => $convertedTimeFrom[2],

                    'hours_to'   => $convertedTimeTo[0],
                    'minutes_to' => $convertedTimeTo[1],
                    'appm_to'    => $convertedTimeTo[2],
                );
            }

            $data['schedule_week_settings'] = $parsedSettings;
        } else {
            unset($data['schedule_week_settings']);
        }
        //--

        return $data;
    }

    // ####################################

    public function getDefault()
    {
        $default = Mage::helper('M2ePro/View_Ebay')->isSimpleMode()
            ? Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getScheduleDefaultSettingsSimpleMode()
            : Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getScheduleDefaultSettingsAdvancedMode();

        $default['schedule_interval_settings'] = json_decode($default['schedule_interval_settings'], true);
        $default['schedule_week_settings'] = json_decode($default['schedule_week_settings'], true);

        return $default;
    }

    // ####################################

    public function isDayExistInWeekSettingsArray($day, $weekSettings)
    {
        $daysInSettingsArray = array_keys($weekSettings);
        return in_array(strtolower($day), $daysInSettingsArray);
    }

    // ####################################
}