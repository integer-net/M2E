<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Synchronization extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const LIST_MODE_NONE = 0;
    const LIST_MODE_YES = 1;

    const LIST_STATUS_ENABLED_NONE = 0;
    const LIST_STATUS_ENABLED_YES  = 1;

    const LIST_IS_IN_STOCK_NONE = 0;
    const LIST_IS_IN_STOCK_YES  = 1;

    const LIST_QTY_NONE    = 0;
    const LIST_QTY_LESS    = 1;
    const LIST_QTY_BETWEEN = 2;
    const LIST_QTY_MORE    = 3;

    const REVISE_UPDATE_QTY_NONE = 0;
    const REVISE_UPDATE_QTY_YES  = 1;

    const REVISE_MAX_AFFECTED_QTY_MODE_OFF = 0;
    const REVISE_MAX_AFFECTED_QTY_MODE_ON = 1;

    const REVISE_UPDATE_PRICE_NONE = 0;
    const REVISE_UPDATE_PRICE_YES  = 1;

    const REVISE_UPDATE_TITLE_NONE = 0;
    const REVISE_UPDATE_TITLE_YES  = 1;

    const REVISE_UPDATE_DESCRIPTION_NONE = 0;
    const REVISE_UPDATE_DESCRIPTION_YES  = 1;

    const REVISE_UPDATE_SUB_TITLE_NONE = 0;
    const REVISE_UPDATE_SUB_TITLE_YES  = 1;

    const RELIST_FILTER_USER_LOCK_NONE = 0;
    const RELIST_FILTER_USER_LOCK_YES  = 1;

    const RELIST_MODE_NONE = 0;
    const RELIST_MODE_YES  = 1;

    const RELIST_STATUS_ENABLED_NONE = 0;
    const RELIST_STATUS_ENABLED_YES  = 1;

    const RELIST_IS_IN_STOCK_NONE = 0;
    const RELIST_IS_IN_STOCK_YES  = 1;

    const RELIST_QTY_NONE    = 0;
    const RELIST_QTY_LESS    = 1;
    const RELIST_QTY_BETWEEN = 2;
    const RELIST_QTY_MORE    = 3;

    const RELIST_SCHEDULE_TYPE_IMMEDIATELY = 0;
    const RELIST_SCHEDULE_TYPE_THROUGH = 1;
    const RELIST_SCHEDULE_TYPE_WEEK = 2;

    const RELIST_SCHEDULE_THROUGH_METRIC_NONE    = 0;
    const RELIST_SCHEDULE_THROUGH_METRIC_MINUTES = 1;
    const RELIST_SCHEDULE_THROUGH_METRIC_HOURS   = 2;
    const RELIST_SCHEDULE_THROUGH_METRIC_DAYS    = 3;

    const STOP_STATUS_DISABLED_NONE = 0;
    const STOP_STATUS_DISABLED_YES  = 1;

    const STOP_OUT_OFF_STOCK_NONE = 0;
    const STOP_OUT_OFF_STOCK_YES  = 1;

    const STOP_QTY_NONE    = 0;
    const STOP_QTY_LESS    = 1;
    const STOP_QTY_BETWEEN = 2;
    const STOP_QTY_MORE    = 3;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Synchronization');
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject->getListings($asObjects,$filters);
    }

    // ########################################

    public function isListMode()
    {
        return $this->getData('list_mode') != self::LIST_MODE_NONE;
    }

    public function isListStatusEnabled()
    {
        return $this->getData('list_status_enabled') != self::LIST_STATUS_ENABLED_NONE;
    }

    public function isListIsInStock()
    {
        return $this->getData('list_is_in_stock') != self::LIST_IS_IN_STOCK_NONE;
    }

    public function isListWhenQtyHasValue()
    {
        return $this->getData('list_qty') != self::LIST_QTY_NONE;
    }

    //------------------------

    public function isReviseWhenChangeQty()
    {
        return $this->getData('revise_update_qty') != self::REVISE_UPDATE_QTY_NONE;
    }

    public function getReviseUpdateQtyMaxAppliedValue()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value');
    }

    public function isReviseWhenChangePrice()
    {
        return $this->getData('revise_update_price') != self::REVISE_UPDATE_PRICE_NONE;
    }

    //------------------------

    public function isRelistMode()
    {
        return $this->getData('relist_mode') != self::RELIST_MODE_NONE;
    }

    public function isRelistFilterUserLock()
    {
        return $this->getData('relist_filter_user_lock') != self::RELIST_FILTER_USER_LOCK_NONE;
    }

    public function isRelistStatusEnabled()
    {
        return $this->getData('relist_status_enabled') != self::RELIST_STATUS_ENABLED_NONE;
    }

    public function isRelistIsInStock()
    {
        return $this->getData('relist_is_in_stock') != self::RELIST_IS_IN_STOCK_NONE;
    }

    public function isRelistWhenQtyHasValue()
    {
        return $this->getData('relist_qty') != self::RELIST_QTY_NONE;
    }

    public function isRelistSchedule()
    {
        return $this->getData('relist_schedule_type') != self::RELIST_SCHEDULE_TYPE_IMMEDIATELY;
    }

    //------------------------

    public function isRelistScheduleWeekDayNow()
    {
        $synchronizationDaysOfWeek = $this->getRelistScheduleWeek();
        $synchronizationDaysOfWeek = explode('_',$synchronizationDaysOfWeek);

        $enabledDaysOfWeek = array();
        foreach ($synchronizationDaysOfWeek as $item) {
            if (!isset($item{2}) || (int)$item{2} != 1) {
                continue;
            }
            $enabledDaysOfWeek[] = $item{0}.$item{1};
        }
        $synchronizationDaysOfWeek = $enabledDaysOfWeek;

        foreach ($synchronizationDaysOfWeek as &$item) {
            $item = strtolower($item);
            switch ($item) {
                case 'mo': $item = 'monday'; break;
                case 'tu': $item = 'tuesday'; break;
                case 'we': $item = 'wednesday'; break;
                case 'th': $item = 'thursday'; break;
                case 'fr': $item = 'friday'; break;
                case 'sa': $item = 'saturday'; break;
                case 'su': $item = 'sunday'; break;
            }
        }

        $todayDayOfWeek = getdate(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        $todayDayOfWeek = strtolower($todayDayOfWeek['weekday']);

        if (!in_array($todayDayOfWeek,$synchronizationDaysOfWeek)) {
            return false;
        }

        return true;
    }

    public function isRelistScheduleWeekTimeNow()
    {
        if (is_null($this->getData('relist_schedule_week_start_time')) ||
            $this->getData('relist_schedule_week_start_time') == '' ||
            is_null($this->getData('relist_schedule_week_end_time')) ||
            $this->getData('relist_schedule_week_end_time') == '') {
            return true;
        }

        $tempStartTime = explode(':',$this->getData('relist_schedule_week_start_time'));
        $tempEndTime = explode(':',$this->getData('relist_schedule_week_end_time'));

        if (!is_array($tempStartTime) || count($tempStartTime) < 2 ||
            !is_array($tempEndTime) || count($tempEndTime) < 2) {
            return true;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        $startTimeStampCurrentDay = mktime(0, 0, 0, date('m',$currentTimeStamp),
                                                    date('d',$currentTimeStamp),
                                                    date('Y',$currentTimeStamp)) +
                                    (int)$tempStartTime[0]*60*60 +
                                    (int)$tempStartTime[1]*60;
        $endTimeStampCurrentDay = mktime(0, 0, 0, date('m',$currentTimeStamp),
                                                  date('d',$currentTimeStamp),
                                                  date('Y',$currentTimeStamp)) +
                                    (int)$tempEndTime[0]*60*60 +
                                    (int)$tempEndTime[1]*60;

        if ($currentTimeStamp < $startTimeStampCurrentDay ||
            $currentTimeStamp > $endTimeStampCurrentDay) {
            return false;
        }

        return true;
    }

    //------------------------

    public function isStopStatusDisabled()
    {
        return $this->getData('stop_status_disabled') != self::STOP_STATUS_DISABLED_NONE;
    }

    public function isStopOutOfStock()
    {
        return $this->getData('stop_out_off_stock') != self::STOP_OUT_OFF_STOCK_NONE;
    }

    public function isStopWhenQtyHasValue()
    {
        return $this->getData('stop_qty') != self::STOP_QTY_NONE;
    }

    // ########################################

    public function getListWhenQtyHasValueType()
    {
        return $this->getData('list_qty');
    }

    public function getListWhenQtyHasValueMin()
    {
        return $this->getData('list_qty_value');
    }

    public function getListWhenQtyHasValueMax()
    {
        return $this->getData('list_qty_value_max');
    }

    //------------------------

    public function getRelistWhenQtyHasValueType()
    {
        return $this->getData('relist_qty');
    }

    public function getRelistWhenQtyHasValueMin()
    {
        return $this->getData('relist_qty_value');
    }

    public function getRelistWhenQtyHasValueMax()
    {
        return $this->getData('relist_qty_value_max');
    }

    public function getRelistScheduleType()
    {
        return $this->getData('relist_schedule_type');
    }

    public function getRelistScheduleThroughMetric()
    {
        return $this->getData('relist_schedule_through_metric');
    }

    public function getRelistScheduleThroughValue()
    {
        return $this->getData('relist_schedule_through_value');
    }

    public function getRelistScheduleWeek()
    {
       return $this->getData('relist_schedule_week');
    }

    //------------------------

    public function getStopWhenQtyHasValueType()
    {
        return $this->getData('stop_qty');
    }

    public function getStopWhenQtyHasValueMin()
    {
        return $this->getData('stop_qty_value');
    }

    public function getStopWhenQtyHasValueMax()
    {
        return $this->getData('stop_qty_value_max');
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_synchronization');
        return parent::delete();
    }

    // ########################################
}