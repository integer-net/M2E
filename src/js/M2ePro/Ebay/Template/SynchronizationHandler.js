EbayTemplateSynchronizationHandler = Class.create();
EbayTemplateSynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_Synchronization', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-input-time', M2ePro.text.wrong_time_format_error, function(value) {
            return value.match(/^\d{2}:\d{2}$/g);
        });

        Validation.add('validate-qty', 'Wrong value. Only integer numbers.', function(value, el)
        {
            if (!el.up('tr').visible()) {
                return true;
            }

            if (value.match(/[^\d]+/g)) {
                return false;
            };

            if (value <= 0) {
                return false;
            }

            return true;
        });
    },

    //----------------------------------

    duplicate_click: function($headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                               M2ePro.text.title_not_unique_error,
                                               'Template_Synchronization', 'title', '',
                                               '');

        CommonHandlerObj.duplicate_click($headId);
    },

    //----------------------------------

    stopQty_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        if ($('stop_qty').value == self.STOP_QTY_NONE) {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == self.STOP_QTY_LESS) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == self.STOP_QTY_BETWEEN) {
            $('stop_qty_item_min').show();
            $('stop_qty_item').hide();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').show();
        } else if ($('stop_qty').value == self.STOP_QTY_MORE) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        }
    },

    listMode_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        if ($('list_mode').value == self.LIST_MODE_NONE) {
            $('magento_block_ebay_template_synchronization_list_rules').hide();
        } else if ($('list_mode').value == self.LIST_MODE_YES) {
            $('magento_block_ebay_template_synchronization_list_rules').show();
        } else {
            $('magento_block_ebay_template_synchronization_list_rules').hide();
        }
    },

    listQty_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        if ($('list_qty').value == self.LIST_QTY_NONE) {
            $('list_qty_value_container').hide();
            $('list_qty_value_max_container').hide();
        } else if ($('list_qty').value == self.LIST_QTY_LESS) {
            $('list_qty_item_min').hide();
            $('list_qty_item').show();
            $('list_qty_value_container').show();
            $('list_qty_value_max_container').hide();
        } else if ($('list_qty').value == self.LIST_QTY_BETWEEN) {
            $('list_qty_item_min').show();
            $('list_qty_item').hide();
            $('list_qty_value_container').show();
            $('list_qty_value_max_container').show();
        } else if ($('list_qty').value == self.LIST_QTY_MORE) {
            $('list_qty_item_min').hide();
            $('list_qty_item').show();
            $('list_qty_value_container').show();
            $('list_qty_value_max_container').hide();
        } else {
            $('list_qty_value_container').hide();
            $('list_qty_value_max_container').hide();
        }
    },

    relistMode_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        if ($('relist_mode').value == self.RELIST_MODE_NONE) {
            $('relist_filter_user_lock_tr_container').hide();
            $('relist_send_data_tr_container').hide();
            $('magento_block_ebay_template_synchronization_relist_rules').hide();
            $('magento_block_ebay_template_synchronization_relist_schedule').hide();
        } else if ($('relist_mode').value == self.RELIST_MODE_YES) {
            $('relist_filter_user_lock_tr_container').show();
            $('relist_send_data_tr_container').show();
            $('magento_block_ebay_template_synchronization_relist_rules').show();
            $('magento_block_ebay_template_synchronization_relist_schedule').show();
        } else {
            $('relist_filter_user_lock_tr_container').hide();
            $('relist_send_data_tr_container').hide();
            $('magento_block_ebay_template_synchronization_relist_rules').hide();
            $('magento_block_ebay_template_synchronization_relist_schedule').hide();
        }

        $('relist_schedule_type').value = self.RELIST_SCHEDULE_TYPE_IMMEDIATELY;
        self.relistScheduleType_change();
    },

    relistQty_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        if ($('relist_qty').value == self.RELIST_QTY_NONE) {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == self.RELIST_QTY_LESS) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == self.RELIST_QTY_BETWEEN) {
            $('relist_qty_item_min').show();
            $('relist_qty_item').hide();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').show();
        } else if ($('relist_qty').value == self.RELIST_QTY_MORE) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        }
    },

    relistScheduleType_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        if ($('relist_schedule_type').value == self.RELIST_SCHEDULE_TYPE_IMMEDIATELY) {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else if ($('relist_schedule_type').value == self.RELIST_SCHEDULE_TYPE_THROUGH) {
            $('relist_schedule_through_value_container').show();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else if ($('relist_schedule_type').value == self.RELIST_SCHEDULE_TYPE_WEEK) {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').show();
            $('relist_schedule_week_time_container').show();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        }
    },

    relistScheduleWeekTime_change : function()
    {
        if ($('relist_schedule_week_time').value == 0) {
            $('relist_schedule_week_start_time_container').hide();
            $('relist_schedule_week_end_time_container').hide();
        } else {
            $('relist_schedule_week_start_time_container').show();
            $('relist_schedule_week_end_time_container').show();
        }
    },

    reviseQty_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        var qtyMaxMode = $('revise_update_qty_max_applied_value_mode_tr');
        var qtyMax = $('revise_update_qty_max_applied_value_tr');
        var qtyMaxValueLine = $('revise_update_qty_max_applied_value_line_tr');

        var qtyMaxModeSelect = $('revise_update_qty_mode');

        if (this.value == self.REVISE_UPDATE_QTY_YES) {
            qtyMaxMode.show();
            qtyMaxValueLine.show();

            if (M2ePro.formData.revise_update_qty == self.REVISE_UPDATE_QTY_NONE) {
                $('revise_update_qty_max_applied_value').value = self.REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT;
                qtyMaxModeSelect.value = self.REVISE_MAX_AFFECTED_QTY_MODE_ON;
            }

            qtyMaxModeSelect.simulate('change');
        } else {
            qtyMaxMode.hide();
            qtyMax.hide();
            qtyMaxValueLine.hide();
        }
    },

    reviseQtyMode_change : function()
    {
        var self = EbayTemplateSynchronizationHandlerObj;
        var qtyMax = $('revise_update_qty_max_applied_value_tr');

        if (this.value == self.REVISE_MAX_AFFECTED_QTY_MODE_ON) {

            if (M2ePro.formData.revise_update_qty_max_applied_value <= 0) {
                $('revise_update_qty_max_applied_value').value = self.REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT;
            }

            qtyMax.show();
        } else {
            qtyMax.hide();
        }
    }

    //----------------------------------
});