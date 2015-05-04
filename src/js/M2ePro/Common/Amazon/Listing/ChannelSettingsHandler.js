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

    sku_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('sku_custom_attribute').value = '';
        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('sku_custom_attribute'));
        }
    },

    //----------------------------------

    general_id_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('general_id_custom_attribute').value = '';
        if (this.value == self.GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('general_id_custom_attribute'));
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

        $('worldwide_id_custom_attribute').value = '';
        if (this.value == self.WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('worldwide_id_custom_attribute'));
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

        $('condition_custom_attribute').value = '';
        $('condition_value').value = '';
        if (this.value == self.CONDITION_MODE_DEFAULT) {
            self.updateHiddenValue(this, $('condition_value'));
            $('condition_note_mode_tr').show();
            condition_note_mode.simulate('change');

        } else {
            self.updateHiddenValue(this, $('condition_custom_attribute'));
            $('condition_note_mode_tr').show();
            condition_note_mode.simulate('change');
        }
    },

    //----------------------------------

    condition_note_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $('condition_note_value_tr').show();
        } else {
            $('condition_note_value_tr').hide();
        }
    },

    handling_time_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('handling_time_custom_attribute').value = '';
        $('handling_time_value').value = '';
        if (this.value == self.HANDLING_TIME_MODE_RECOMMENDED) {
            self.updateHiddenValue(this, $('handling_time_value'));
        }

        if (this.value == self.HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('handling_time_custom_attribute'));
        }
    },

    restock_date_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('restock_date_value_tr').hide();

        $('restock_date_custom_attribute').value = '';
        if (this.value == self.RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $('restock_date_value_tr').show();
        }

        if (this.value == self.RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('restock_date_custom_attribute'));
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