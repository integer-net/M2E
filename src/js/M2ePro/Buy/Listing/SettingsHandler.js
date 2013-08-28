BuyListingSettingsHandler = Class.create();
BuyListingSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {

        this.setValidationCheckRepetitionValue('M2ePro-listing-title',
            M2ePro.text.title_not_unique_error,
            'Listing', 'title', 'id',
            M2ePro.formData.id);

        Validation.add('M2ePro-input-datetime', M2ePro.text.wrong_date_time_format_error, function(value,el) {
            if ($(el).up('tr').visible()) {
                return value.match(/^\d{4}-\d{2}-\d{1,2}\s\d{2}:\d{2}:\d{2}$/g);
            }
            return true;
        });
    },

    //----------------------------------

    save_click: function(url)
    {
        if (typeof categories_selected_items != 'undefined') {
            array_unique(categories_selected_items);

            var selectedCategories = implode(',',categories_selected_items);

            $('selected_categories').value = selectedCategories;
        }

        if (typeof url == 'undefined' || url == '') {
            url = M2ePro.url.formSubmit + 'back/'+base64_encode('list')+'/';
        }
        this.submitForm(url);
    },

    save_and_edit_click: function(url, tabsId)
    {
        if (typeof categories_selected_items != 'undefined') {
            array_unique(categories_selected_items);

            var selectedCategories = implode(',',categories_selected_items);

            $('selected_categories').value = selectedCategories;
        }

        if (typeof url == 'undefined' || url == '') {

            var tabsUrl = '';
            if (typeof tabsId != 'undefined') {
                tabsUrl = '|tab=' + $$('#' + tabsId + ' a.active')[0].name;
            }

            url = M2ePro.url.formSubmit + 'back/'+base64_encode('edit'+tabsUrl) + '/';
        }
        this.submitForm(url);
    },

    attribute_sets_confirm: function()
    {
        AttributeSetHandlerObj.confirmAttributeSets();

        var attributeSets = AttributeSetHandlerObj.getConfirmedAttributeSets();

        if ($('template_selling_format_autocomplete')) {
            $('template_selling_format_autocomplete').value = '';
        }
        if ($('template_selling_format_id')) {
            $('template_selling_format_id').value = '';
        }

        if (M2ePro.autoCompleteData.flags.sellingFormatTemplatesDropDown) {
            BuyListingSettingsHandlerObj.reloadSellingFormatTemplates();
        } else {
            $('template_selling_format_autocomplete').remove();
            newInput = new Element('input', {
                'id'         : 'template_selling_format_autocomplete',
                'class'      : 'input-text',
                'selected_id': '',
                'style'      : 'width: 275px;color: gray;',
                'value'      : M2ePro.text.typeTemplateNameHere,
                'onblur'     : 'if (this.value.trim().length == 0) { this.value = M2ePro.text.typeTemplateNameHere; this.style.color = "gray"; }',
                'onfocus'    : 'if (this.value == M2ePro.text.typeTemplateNameHere) { this.value = ""; this.style.color = ""}'
            });
            $('template_selling_format_cell').insert({top: newInput});
            M2ePro.formData.template_selling_format_id > 0 && newInput.setStyle({color: 'initial'});
            AutoCompleteHandler.bind(
                "template_selling_format_autocomplete",
                M2ePro.autoCompleteData.url.getSellingFormatTemplatesBySet + 'attribute_sets/' + attributeSets,
                M2ePro.formData.template_selling_format_id > 0 ? M2ePro.formData.template_selling_format_id : '',
                M2ePro.formData.template_selling_format_title,
                function (id) {
                    $('template_selling_format_id').value = id
                }
            );
            M2ePro.formData.template_selling_format_id = 0;
            M2ePro.formData.template_selling_format_title = '';
            $('template_selling_format_id').value = $('template_selling_format_autocomplete').readAttribute('selected_id');
        }

        $$('button.add').each(function(obj) {
            var onclickAction = obj.readAttribute('onclick');

            if (onclickAction.match(/attribute_sets\/[-]?[0-9,]+/)) {
                onclickAction = onclickAction.replace(/attribute_sets\/[-]?[0-9,]+/, 'attribute_sets/' + attributeSets);
            } else {
                onclickAction = onclickAction.replace(/new\//, 'new/attribute_sets/' + attributeSets + '/');
            }

            obj.writeAttribute('onclick', onclickAction);
        });
    },

    //----------------------------------

    reloadSellingFormatTemplates: function()
    {
        var attributeSets = AttributeSetHandlerObj.getConfirmedAttributeSets();
        if (attributeSets == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        BuyListingSettingsHandlerObj.reloadByAttributeSet(M2ePro.url.getSellingFormatTemplatesBySet + 'attribute_sets/' + attributeSets, 'template_selling_format_id');
    },

    reloadSynchronizationTemplates: function()
    {
        BuyListingSettingsHandlerObj.reloadByAttributeSet(M2ePro.url.getSynchronizationTemplates, 'template_synchronization_id');
    },

    //----------------------------------

    synchronization_template_id_change: function()
    {
        BuyListingSettingsHandlerObj.hideEmptyOption(this);
    },

    synchronization_start_type_change: function()
    {
        var value = $('synchronization_start_type').value;

        if (value == 1) {
            $('synchronization_start_through_value_container').style.display = 'none';
            $('synchronization_start_date_container').style.display = 'none';
        } else if (value == 2) {
            $('synchronization_start_through_value_container').style.display = '';
            $('synchronization_start_date_container').style.display = 'none';
        } else if (value == 3) {
            $('synchronization_start_through_value_container').style.display = 'none';
            $('synchronization_start_date_container').style.display = '';
        } else{
            $('synchronization_start_through_value_container').style.display = 'none';
            $('synchronization_start_date_container').style.display = 'none';
        }
    },

    synchronization_stop_type_change: function()
    {
        var value = $('synchronization_stop_type').value;

        if (value == 0) {
            $('synchronization_stop_through_value_container').style.display = 'none';
            $('synchronization_stop_date_container').style.display = 'none';
        } else if (value == 1) {
            $('synchronization_stop_through_value_container').style.display = '';
            $('synchronization_stop_date_container').style.display = 'none';
        } else if (value == 2) {
            $('synchronization_stop_through_value_container').style.display = 'none';
            $('synchronization_stop_date_container').style.display = '';
        } else {
            $('synchronization_stop_through_value_container').style.display = 'none';
            $('synchronization_stop_date_container').style.display = 'none';
        }
    },

    //----------------------------------

    reloadByAttributeSet: function(url, id)
    {
        new Ajax.Request(url, {
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);

                var options = '';

                var firstItemValue = '';
                var currentValue = $(id).value;

                data.each(function(paris) {
                    var key = (typeof paris.key != 'undefined') ? paris.key : paris.id;
                    var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
                    options += '<option value="' + key + '">' + val + '</option>\n';

                    if (firstItemValue == '') {
                        firstItemValue = val;
                    }
                });

                $(id).update();
                $(id).insert(options);

                if (currentValue != '') {
                    $(id).value = currentValue;
                } else {
                    if (M2ePro.formData[id] > 0) {
                        $(id).value = M2ePro.formData[id];
                    } else {
                        $(id).value = firstItemValue;
                    }
                }
            }
        });
    }

    //----------------------------------
});
