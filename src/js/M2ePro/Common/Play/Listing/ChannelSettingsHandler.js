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

        $('sku_custom_attribute').value = '';
        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('sku_custom_attribute'));
        }
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
            $('general_id_tips_value').innerHTML = M2ePro.text.general_id_tips_play_id;
            $('general_id_tips').show();
        } else {
            $('general_id_tips_value').innerHTML = M2ePro.text.general_id_tips_default;
            $('general_id_tips').show();
        }
        if (this.value == self.GENERAL_ID_MODE_ISBN) {
            $('general_id_attribute_label').innerHTML = M2ePro.text.general_id_label_isbn;
        }

        if (this.value == self.GENERAL_ID_MODE_NOT_SET) {
            $('general_id_custom_attribute_container').hide();
            $('general_id_tips').hide();
        } else {
            $('general_id_custom_attribute_container').show();
            $('general_id_tips').show();
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

        $('dispatch_to_value').value = '';
        $('dispatch_to_custom_attribute').value = '';
        if (this.value == self.DISPATCH_TO_MODE_DEFAULT) {
            self.dispatch_to_value_change.bind(this)();
            self.updateHiddenValue(this, $('dispatch_to_value'));
        }
        if (this.value == self.DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('dispatch_to_custom_attribute'));

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
        var attributeCode = dispatchToSelect.options[dispatchToSelect.selectedIndex].getAttribute('attribute_code');

        if (dispatchToSelect.value == self.DISPATCH_TO_MODE_NOT_SET ||
            dispatchToSelect.value == self.DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {

            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();
            return;
        }

        if (attributeCode == self.DISPATCH_TO_UK) {
            $('shipping_price_gbr_mode_tr').show();
            $('shipping_price_hr').show();

            $('shipping_price_gbr_mode').simulate('change');

            $('shipping_price_euro_mode_tr').hide();
            $('shipping_price_euro_value_tr').hide();

        } else if (attributeCode == self.DISPATCH_TO_EUROPA) {
            $('shipping_price_euro_mode_tr').show();
            $('shipping_price_hr').show();

            $('shipping_price_euro_mode').simulate('change');

            $('shipping_price_gbr_mode_tr').hide();
            $('shipping_price_gbr_value_tr').hide();

        } else if (attributeCode == self.DISPATCH_TO_BOTH) {
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

        $('dispatch_from_value').value = '';
        if (this.value == self.DISPATCH_FROM_MODE_DEFAULT) {
            self.updateHiddenValue(this, $('dispatch_from_value'));
        }
    },

    //----------------------------------

    price_gbr_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('shipping_price_gbr_value_tr').hide();

        $('shipping_price_gbr_custom_attribute').value = '';
        if ($('dispatch_to_value').value == self.DISPATCH_TO_EUROPA &&
            $('dispatch_to_mode').value == self.DISPATCH_TO_MODE_DEFAULT) {
            return;
        }
        if (this.value == self.SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE) {
            $('shipping_price_gbr_value_tr').show();
        }
        if (this.value == self.SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('shipping_price_gbr_custom_attribute'));
        }
    },

    price_euro_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('shipping_price_euro_value_tr').hide();

        $('shipping_price_euro_custom_attribute').value = '';
        if ($('dispatch_to_value').value == self.DISPATCH_TO_UK &&
            $('dispatch_to_mode').value == self.DISPATCH_TO_MODE_DEFAULT) {
            return;
        }
        if (this.value == self.SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE) {
            $('shipping_price_euro_value_tr').show();
        }
        if (this.value == self.SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('shipping_price_euro_custom_attribute'));
        }
    },

    //----------------------------------

    condition_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        $('condition_custom_attribute').value = '';
        $('condition_value').value = '';
        if (this.value == self.CONDITION_MODE_DEFAULT) {
            self.updateHiddenValue(this, $('condition_value'));
        } else {
            self.updateHiddenValue(this, $('condition_custom_attribute'));
        }
    },

    //----------------------------------

    condition_note_mode_change: function()
    {
        var self = PlayListingChannelSettingsHandlerObj;

        if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $('condition_note_value_tr').show();
        } else {
            $('condition_note_value_tr').hide();
        }
    },

    //----------------------------------

    appendToText: function(ddId, targetId)
    {
        if ($(ddId).value == '') {
            return;
        }

        var attributePlaceholder = '#' + $(ddId).value + '#',
            element              = $(targetId);

        if (document.selection) {
            /* IE */
            element.focus();
            document.selection.createRange().text = attributePlaceholder;
            element.focus();
        } else if (element.selectionStart || element.selectionStart == '0') {
            /* Webkit */
            var startPos  = element.selectionStart,
                endPos    = element.selectionEnd,
                scrollTop = element.scrollTop,
                tempValue;

            tempValue = element.value.substring(0, startPos);
            tempValue += attributePlaceholder;
            tempValue += element.value.substring(endPos, element.value.length);
            element.value = tempValue;

            element.focus();
            element.selectionStart = startPos + attributePlaceholder.length;
            element.selectionEnd   = startPos + attributePlaceholder.length;
            element.scrollTop      = scrollTop;
        } else {
            element.value += attributePlaceholder;
            element.focus();
        }
    }

    //----------------------------------
});
