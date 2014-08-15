PlayListingChannelSettingsHandler = Class.create();
PlayListingChannelSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-condition-note-length', M2ePro.text.condition_note_length_error, function(value) {
            if ($('condition_note_mode').value != PlayListingChannelSettingsHandlerObj.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
                return true;
            }

            return value.length <= 1000;
        });

        Validation.add('M2ePro-validate-shipping-value-positive', M2ePro.text.shipping_rate_error, function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }
            var floatValidator = Validation.get('M2ePro-validation-float');
            if (!floatValidator.test($F(el), el)) {
                return true;
            }

            if(value.indexOf('.') != -1 && (value.substring(value.indexOf('.')+1)).length > 2) {
                return false;
            }

            return true;
        });
    },

    //----------------------------------

    account_id_change: function()
    {
        PlayListingChannelSettingsHandlerObj.hideEmptyOption($('account_id'));
    },

    //----------------------------------

    sku_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            $('sku_custom_attribute_container').show();
        } else {
            $('sku_custom_attribute_container').hide();
        }
    },

    sku_custom_attribute_change: function()
    {
        PlayListingChannelSettingsHandlerObj.hideEmptyOption($('sku_custom_attribute'));
    },

    //----------------------------------

    general_id_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        if (this.value == self.GENERAL_ID_MODE_WORLDWIDE) {
            $('general_id_attribute_label').innerHTML = M2ePro.text.general_id_label_upc;
        }
        if (this.value == self.GENERAL_ID_MODE_GENERAL_ID) {
            $('general_id_attribute_label').innerHTML = M2ePro.text.general_id_label_play_id;
            $('general_id_tips').innerHTML = M2ePro.text.general_id_tips_play_id;
        } else {
            $('general_id_tips').innerHTML = M2ePro.text.general_id_tips_default;
        }
        if (this.value == self.GENERAL_ID_MODE_ISBN) {
            $('general_id_attribute_label').innerHTML = M2ePro.text.general_id_label_isbn;
        }

        if (this.value == self.GENERAL_ID_MODE_NOT_SET) {
            $('general_id_custom_attribute_container').hide();
        } else {
            $('general_id_custom_attribute_container').show();
        }
    },

    general_id_custom_attribute_change: function()
    {
        PlayListingChannelSettingsHandlerObj.hideEmptyOption($('general_id_custom_attribute'));
    },

    //----------------------------------

    dispatch_to_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('dispatch_to_value_tr').hide();
        $('dispatch_to_custom_attribute_tr').hide();

        if (this.value == self.DISPATCH_TO_MODE_DEFAULT) {
            $('dispatch_to_value').simulate('change');
            $('dispatch_to_value_tr').show();
        }
        if (this.value == self.DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {
            $('dispatch_to_custom_attribute_tr').show();

            //$('shipping_price_gbr_mode').value = self.SHIPPING_PRICE_GBR_MODE_NONE;
            $('shipping_price_gbr_mode').simulate('change');
            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_hr').show();

            //$('shipping_price_euro_mode').value = self.SHIPPING_PRICE_EURO_MODE_NONE;
            $('shipping_price_euro_mode').simulate('change');
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();
        }
        if (this.value == self.DISPATCH_TO_MODE_NOT_SET) {
            $('shipping_price_gbr_mode').value = self.SHIPPING_PRICE_GBR_MODE_NONE;
            $('shipping_price_gbr_mode').simulate('change');
            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_hr').show();

            $('shipping_price_euro_mode').value = self.SHIPPING_PRICE_EURO_MODE_NONE;
            $('shipping_price_euro_mode').simulate('change');
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();
        }
    },

    dispatch_to_value_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        var dispatchToSelect = $('dispatch_to_mode');

        if (dispatchToSelect.value == self.DISPATCH_TO_MODE_NOT_SET ||
            dispatchToSelect.value == self.DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {

            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();
            return;
        }

        if (this.value == self.DISPATCH_TO_UK) {
            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_hr').show();

            $('shipping_price_gbr_mode').simulate('change');

            $('shipping_price_euro_mode_tr').hide();
            $('shipping_price_euro_value_tr').hide();
            $('shipping_price_euro_custom_attribute_tr').hide();

        } else if (this.value == self.DISPATCH_TO_EUROPA) {
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();

            $('shipping_price_euro_mode').simulate('change');

            $('shipping_price_gbr_mode_tr').hide();
            $('shipping_price_gbr_value_tr').hide();
            $('shipping_price_gbr_custom_attribute_tr').hide();

        } else if (this.value == self.DISPATCH_TO_BOTH) {
            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();

            $('shipping_price_gbr_mode').simulate('change');
            $('shipping_price_euro_mode').simulate('change');
        }
    },

    //----------------------------------

    dispatch_from_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('dispatch_from_value_tr').hide();

        if (this.value == self.DISPATCH_FROM_MODE_DEFAULT) {
            $('dispatch_from_value_tr').show();
        }
    },

    //----------------------------------

    price_gbr_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('shipping_price_gbr_value_tr').hide();
        $('shipping_price_gbr_custom_attribute_tr').hide();

        if ($('dispatch_to_value').value == self.DISPATCH_TO_EUROPA &&
            $('dispatch_to_mode').value == self.DISPATCH_TO_MODE_DEFAULT) {
            return;
        }
        if (this.value == self.SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE) {
            $('shipping_price_gbr_value_tr').show();
        }
        if (this.value == self.SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE) {
            $('shipping_price_gbr_custom_attribute_tr').show();
        }
    },

    price_euro_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('shipping_price_euro_value_tr').hide();
        $('shipping_price_euro_custom_attribute_tr').hide();

        if ($('dispatch_to_value').value == self.DISPATCH_TO_UK &&
            $('dispatch_to_mode').value == self.DISPATCH_TO_MODE_DEFAULT) {
            return;
        }
        if (this.value == self.SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE) {
            $('shipping_price_euro_value_tr').show();
        }
        if (this.value == self.SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE) {
            $('shipping_price_euro_custom_attribute_tr').show();
        }
    },

    //----------------------------------

    condition_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        var condition_note_mode = $('condition_note_mode');

        $('condition_value_tr', 'condition_custom_attribute_tr').invoke('hide');

        if (this.value == self.CONDITION_MODE_NOT_SET) {
            $('condition_value_tr').hide();
            $('condition_custom_attribute_tr').hide();
        } else if (this.value == self.CONDITION_MODE_DEFAULT) {
            $('condition_value_tr').show();
        } else {
            $('condition_custom_attribute_tr').show();
        }
    },

    condition_value_change: function()
    {
        PlayListingChannelSettingsHandlerObj.hideEmptyOption($('condition_value'));
    },

    condition_custom_attribute_change: function()
    {
        PlayListingChannelSettingsHandlerObj.hideEmptyOption($('condition_custom_attribute'));
    },

    //----------------------------------

    condition_note_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('condition_note_value_tr', 'condition_note_custom_attribute_tr').invoke('hide');

        if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $('condition_note_value_tr').show();
        } else if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $('condition_note_custom_attribute_tr').show();
        }
    },

    condition_note_custom_attribute_change: function()
    {
        PlayListingChannelSettingsHandlerObj.hideEmptyOption($('condition_note_custom_attribute'));
    }

    //----------------------------------
});
