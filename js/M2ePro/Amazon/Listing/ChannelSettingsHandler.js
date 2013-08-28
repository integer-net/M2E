AmazonListingChannelSettingsHandler = Class.create();
AmazonListingChannelSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-condition-note-length', M2ePro.text.condition_note_length_error, function(value) {
            if ($('condition_note_mode').value != AmazonListingChannelSettingsHandlerObj.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
                return true;
            }

            return value.length <= 2000;
        });
    },

    //----------------------------------

    account_id_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;
        var accountId = this.value;

        self.hideEmptyOption($('account_id'));

        new Ajax.Request(M2ePro.url.getMarketplacesForAccount,
        {
            method: 'post',
            asynchronous : false,
            parameters : {
                account_id : accountId
            },
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);

                var html = '<option class="empty"></option>';
                data.each(function(v) {
                    html += '<option value="' + v.code + '">' + v.label + '</option>\n';
                });

                $('marketplace_id').update(html);

                $('marketplace_id').value = M2ePro.formData.marketplace_id > 0
                    ? M2ePro.formData.marketplace_id
                    : data.shift().code;

                if ($('marketplace_id').value == '') {
                    $('marketplace_id').value = data.shift().code;
                }
//                $('marketplace_id').value = M2ePro.formData.marketplace_id;
//                $('marketplace_id_container').show();
            }
        });
    },

    //----------------------------------

    marketplace_id_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('marketplace_id'));
    },

    //----------------------------------

    sku_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            $('sku_custom_attribute_container').show();
        } else {
            $('sku_custom_attribute_container').hide();
        }
    },

    sku_custom_attribute_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('sku_custom_attribute'));
    },

    //----------------------------------

    general_id_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        if (this.value == self.GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $('general_id_custom_attribute_container').show();
        } else {
            $('general_id_custom_attribute_container').hide();
        }
    },

    general_id_custom_attribute_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('general_id_custom_attribute'));
    },

    //----------------------------------

    worldwide_id_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        if (this.value == self.WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $('worldwide_id_custom_attribute_container').show();
        } else {
            $('worldwide_id_custom_attribute_container').hide();
        }
    },

    worldwide_id_custom_attribute_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('worldwide_id_custom_attribute'));
    },

    //----------------------------------

    condition_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        var condition_note_mode = $('condition_note_mode');

        $('condition_value_tr', 'condition_custom_attribute_tr').invoke('hide');

        if (this.value == self.CONDITION_MODE_NOT_SET) {
            $('condition_value_tr').hide();
            $('condition_custom_attribute_tr').hide();

            $('condition_note_mode_tr').hide();
            $('condition_note_value_tr').hide();
            $('condition_note_custom_attribute_tr').hide();

        } else if (this.value == self.CONDITION_MODE_DEFAULT) {
            $('condition_value_tr').show();

            $('condition_note_mode_tr').show();
            condition_note_mode.simulate('change');

        } else {
            $('condition_custom_attribute_tr').show();

            $('condition_note_mode_tr').show();
            condition_note_mode.simulate('change');
        }
    },

    condition_value_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('condition_value'));
    },

    condition_custom_attribute_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('condition_custom_attribute'));
    },

    //----------------------------------

    condition_note_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('condition_note_value_tr', 'condition_note_custom_attribute_tr').invoke('hide');

        if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $('condition_note_value_tr').show();
        } else if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $('condition_note_custom_attribute_tr').show();
        }
    },

    condition_note_custom_attribute_change: function()
    {
        AmazonListingChannelSettingsHandlerObj.hideEmptyOption($('condition_note_custom_attribute'));
    },

    handling_time_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('handling_time_value_tr').hide();
        $('handling_time_custom_attribute_tr').hide();

        if (this.value == self.HANDLING_TIME_MODE_RECOMMENDED) {
            $('handling_time_value_tr').show();
        }

        if (this.value == self.HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $('handling_time_custom_attribute_tr').show();
        }
    },

    restock_date_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('restock_date_value_tr').hide();
        $('restock_date_custom_attribute_tr').hide();

        if (this.value == self.RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $('restock_date_value_tr').show();
        }

        if (this.value == self.RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            $('restock_date_custom_attribute_tr').show();
        }
    }

    //----------------------------------
});