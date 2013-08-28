EbayTemplateGeneralSpecificHandler = Class.create();
EbayTemplateGeneralSpecificHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.specifics = {};
        this.specifics.data = {};
        this.counter = 0;

        Validation.add('M2ePro-validate-motors-specifics-attribute', M2ePro.text.validate_motors_specifics_attribute_error, function(value) {
            if (!value) {
                return true;
            }

            var checkResult = false;

            new Ajax.Request( M2ePro.url.getAttributeType ,
            {
                method: 'get',
                asynchronous : false,
                parameters : {
                    attribute_code : value
                },
                onSuccess: function (transport)
                {
                    var attributeType = transport.responseText.evalJSON()['type'];

                    if (attributeType == 'text') {
                        checkResult = true;
                    }
                }
            });

            return checkResult;
        });
    },

    //----------------------------------

    conditionModeChange: function(select)
    {
        if (select.value == 1) { // custom attribute
            $('item_condition_value_container').hide();
            $('item_condition_attribute_container').show();
        } else {
            $('item_condition_value_container').show();
            $('item_condition_attribute_container').hide();
        }
    },

    //----------------------------------

    renderConditions: function(condition_mode, condition_values)
    {
        var optionsString = '';

        $('item_condition_mode_container').show();

        if (parseInt(condition_mode) <= 0 && (typeof condition_values == "undefined")) { // disabled
            $('item_condition_mode_container').hide();
        } else if (parseInt(condition_mode) <= 0 && !(typeof condition_values == "undefined")) { // not required
            $('required_condition').hide();
        }

        // disabled or not required
        if (parseInt(condition_mode) <= 0) {
            optionsString = '<option value="-1">---</option>';
        }

        if (!(typeof condition_values == "undefined")) {
            var ids = [];
            condition_values.each(function(item) {
                optionsString += '<option value="' + item.id + '">' + item.title + '</option>'
                ids.push(item.id);
            });
        }

        $('condition_value').update();
        $('condition_value').insert(optionsString);

        if (ids && ids.indexOf(M2ePro.formData.condition_value) != -1) {
            $('condition_value').value = M2ePro.formData.condition_value;
        } else {
            $('condition_value').selectedIndex = 0;
        }
    },

    //----------------------------------

    renderSpecifics: function(categoryData)
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;
        var itemSpecifics = categoryData.item_specifics || [];

        $('add_custom_container').show();

        self.clearSpecifics();

        if (itemSpecifics.length == 0 || itemSpecifics.specifics.length == 0) {
            $('item_specifics_tbody').hide();
            return;
        }

        if (itemSpecifics.mode == self.MODE_ATTRIBUTE_SET) {
            $('add_custom_container').hide();
        }

        self.specifics.data = itemSpecifics.specifics;

        self.counter = 0;
        var counter = self.counter;

        self.specifics.data.each(function(specific) {
            var template = $('specific_template').innerHTML;

            template = template.replace(/%i%/g, counter);

            template = template.replace(/%attribute_id%/g, specific.id);
            template = template.replace(/%attribute_title%/g, specific.title);
            template = template.replace(/%required%/g, specific.required ? '&nbsp;<span class="required">*</span>' : '');

            template = template.replace(/%relation_mode%/, itemSpecifics.mode);
            template = template.replace(/%relation_id%/, itemSpecifics.mode_relation_id);

            if (specific.mode != undefined) {
                self.addRow();
                $$('#custom_item_specifics_value_mode_'+counter+' option[value="'+specific.value_mode+'"]')[0].selected = true;
                self.specificCustomModeChange($('custom_item_specifics_value_mode_' + counter));

                if (specific.value_mode == self.VALUE_MODE_CUSTOM_VALUE) {
                    $('custom_item_specifics_label_custom_value_' + counter).value = specific.attribute_title;
                    $('item_specifics_value_custom_value_' + counter).value = specific.value_custom_value;
                }

                if (specific.value_mode == self.VALUE_MODE_CUSTOM_ATTRIBUTE) {
                    $$('#item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                }

                if (specific.value_mode == self.VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE) {
                    $('custom_item_specifics_label_custom_label_attribute_' + counter).value = specific.attribute_title;
                    $$('#item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                }
            } else {
                $('item_specifics_tbody').insert(template);

                var recommendedOptionsHtml = '';
                specific.values.each(function(recommended) {
                    recommendedOptionsHtml += '<option value="%value%">%label%</option>'
                        .replace(/%value%/, base64_encode(recommended.id) + '-|-||-|-' + base64_encode(recommended.value))
                        .replace(/%label%/, recommended.value);
                });

                $('item_specifics_value_ebay_recommended_'+counter).insert(recommendedOptionsHtml);
                $('item_specifics_value_ebay_recommended_'+counter).selectedIndex = -1;

                $('item_specifics_value_custom_attribute_' + counter).insert(AttributeSetHandlerObj.attrData);

                var specificValueMode = $('item_specifics_value_mode_' + counter);

                if (specific.required) {
                    //specificValueMode.select('option[value="'+self.VALUE_MODE_NONE+'"]')[0].remove();
                    specificValueMode.select('option')[1].selected = 1;
                }

                if (specific.type == self.RENDER_TYPE_TEXT) {
                    specificValueMode.select('option[value="'+self.VALUE_MODE_EBAY_RECOMMENDED+'"]')[0].remove();
                }

                if (specific.type == self.RENDER_TYPE_SELECT_ONE || specific.type == self.RENDER_TYPE_SELECT_MULTIPLE) {
                    specificValueMode.select('option[value="'+self.VALUE_MODE_CUSTOM_VALUE+'"]')[0].remove();
                }

                if (specific.type == self.RENDER_TYPE_SELECT_MULTIPLE || specific.type == self.RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT) {
                    $('item_specifics_value_ebay_recommended_'+counter).writeAttribute('multiple', 'true');
                    var tempOldName = $('item_specifics_value_ebay_recommended_'+counter).readAttribute('name');
                    $('item_specifics_value_ebay_recommended_'+counter).writeAttribute('name', tempOldName + '[]');
                }

                M2ePro.formData.item_specifics.each(function(selectedSpecific) {

                    if (selectedSpecific.mode != itemSpecifics.mode ||
                        selectedSpecific.mode_relation_id != itemSpecifics.mode_relation_id ||
                        selectedSpecific.attribute_id != specific.id ||
                        selectedSpecific.attribute_title != specific.title) {
                        return;
                    }

                    if (selectedSpecific.value_mode == self.VALUE_MODE_NONE) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+self.VALUE_MODE_NONE+'"]')[0].selected = true;
                    }

                    if (selectedSpecific.value_mode == self.VALUE_MODE_EBAY_RECOMMENDED) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+self.VALUE_MODE_EBAY_RECOMMENDED+'"]')[0].selected = true;
                        $$('#item_specifics_value_ebay_recommended_'+counter+' option').each(function(tempOption){
                            selectedSpecific.value_data.each(function(tempSelected) {
                                var tempSearchValue = base64_encode(tempSelected.id) + '-|-||-|-' + base64_encode(tempSelected.value);
                                if(tempOption.value == tempSearchValue){
                                    tempOption.selected = true;
                                }
                            });
                        });
                    }

                    if (selectedSpecific.value_mode == self.VALUE_MODE_CUSTOM_VALUE) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+self.VALUE_MODE_CUSTOM_VALUE+'"]')[0].selected = true;
                        $('item_specifics_value_custom_value_'+counter).setValue(selectedSpecific.value_data);
                    }

                    if (selectedSpecific.value_mode == self.VALUE_MODE_CUSTOM_ATTRIBUTE) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+self.VALUE_MODE_CUSTOM_ATTRIBUTE+'"]')[0].selected = true;
                        $$('#item_specifics_value_custom_attribute_'+counter+' option[value="'+selectedSpecific.value_data+'"]')[0].selected = true;
                    }
                });

                self.specificModeChange($('item_specifics_value_mode_'+counter));
            }

            counter++;
            self.counter = counter;
        });

        $('item_specifics_tbody').show();
    },

    clearSpecifics: function()
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;

        $$('#item_specifics_tbody tr').each(Element.remove);
    },

    //----------------------------------

    renderMotorsSpecifics: function(categoryData)
    {
        var partsCompatibility = categoryData.parts_compatibility || {};
        var isVisible = typeof partsCompatibility.by_application != 'undefined';

        this.setVisibilityForMotorsSpecificsContainer(isVisible);
    },

    setVisibilityForMotorsSpecificsContainer: function(isVisible)
    {
        var canShow = (isVisible && $F('marketplace_id') == this.MARKETPLACE_MOTORS) ? true : false;
        $('listing_template_specifics_motors_specifics_container')[canShow ? 'show' : 'hide']();

        if (!canShow) {
            $('motors_specifics_attribute').value = '';
        }
    },

    //----------------------------------

    addRow: function()
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;
        var id = 'item_specifics_tbody';
        var i = self.counter;

        var tpl = $('specific_template').innerHTML;
        tpl = tpl.replace(/%i%/g, i);
        tpl = tpl.replace(/%attribute_title%%required%/g, '');
        tpl = tpl.replace(/%relation_mode%/g, self.MODE_CUSTOM_ITEM_SPECIFICS);
        $(id).show();
        $(id).insert(tpl);

        $('item_specifics_value_custom_attribute_' + i).insert(AttributeSetHandlerObj.attrData);

        $('item_specifics_value_mode_' + i).hide();
        $('custom_item_specifics_value_mode_' + i).show();
        $('custom_item_specific_remove_' + i).show();
        $('specific_' + i + '_row').removeClassName('not-custom');

        self.specificCustomModeChange($('custom_item_specifics_value_mode_'+i));

        self.counter++;
    },

    //----------------------------------

    removeSpecific: function(button)
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;

        $(button).up('tr').remove();
    },

    //----------------------------------

    specificModeChange: function(select)
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;
        var number = select.id.replace('item_specifics_value_mode_', '');

        $('item_specifics_value_ebay_recommended_' + number,
            'item_specifics_value_custom_value_' + number,
            'item_specifics_value_custom_attribute_' + number,
            'custom_item_specifics_label_custom_attribute_' + number
        ).invoke('hide');

        $('attribute_title_' + number).show();

        if (select.value == self.VALUE_MODE_EBAY_RECOMMENDED) {
            $('item_specifics_value_ebay_recommended_' + number).show();
        }
        if (select.value == self.VALUE_MODE_CUSTOM_VALUE) {
            $('item_specifics_value_custom_value_' + number).show();
        }
        if (select.value == self.VALUE_MODE_CUSTOM_ATTRIBUTE) {
            $('attribute_title_' + number).show();
            $('custom_item_specifics_label_custom_attribute_' + number).hide();
            $('item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    specificCustomModeChange: function(select)
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;
        var number = select.id.replace('custom_item_specifics_value_mode_', '');

        $('item_specifics_value_custom_value_' + number,
          'item_specifics_value_custom_attribute_' + number,
          'item_specifics_value_ebay_recommended_' + number).invoke('hide');

        $('custom_item_specifics_label_custom_value_' + number,
          'custom_item_specifics_label_custom_label_attribute_' + number,
          'custom_item_specifics_label_custom_attribute_' + number).invoke('hide');

        if (select.value == self.VALUE_MODE_CUSTOM_VALUE) {
            $('item_specifics_value_custom_value_'+number).show();
            $('custom_item_specifics_label_custom_value_' + number).show();
        }
        if (select.value == self.VALUE_MODE_CUSTOM_ATTRIBUTE) {
            $('custom_item_specifics_label_custom_attribute_'+number).show();
            $('item_specifics_value_custom_attribute_' + number).show();
        }
        if (select.value == self.VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE) {
            $('custom_item_specifics_label_custom_label_attribute_'+number).show();
            $('item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    getCustomSpecifics: function(handler)
    {
        var generalId = '';
        if ($('general_id')) {
            generalId = $('general_id').value;
        }

        var url = M2ePro.url.getCategoryInformation + 'only_custom/1' + '/general_id/' + generalId;
        new Ajax.Request(url, {onSuccess: function(transport) {
            handler(transport.responseText.evalJSON());
        }});
    },

    //----------------------------------

    product_details_isbn_mode_change: function()
    {
        EbayTemplateGeneralSpecificHandlerObj.changeDetailsMode(this.value,'product_details_isbn_cv', 'product_details_isbn_ca_tr');
    },

    product_details_epid_mode_change: function()
    {
        EbayTemplateGeneralSpecificHandlerObj.changeDetailsMode(this.value, 'product_details_epid_cv', 'product_details_epid_ca_tr');
    },

    product_details_upc_mode_change: function()
    {
        EbayTemplateGeneralSpecificHandlerObj.changeDetailsMode(this.value, 'product_details_upc_cv', 'product_details_upc_ca_tr');
    },

    product_details_ean_mode_change: function()
    {
        EbayTemplateGeneralSpecificHandlerObj.changeDetailsMode(this.value, 'product_details_ean_cv', 'product_details_ean_ca_tr');
    },

    changeDetailsMode: function(mode, valueContent, attributeContent)
    {
        var self = EbayTemplateGeneralSpecificHandlerObj;

        if (mode == self.PRODUCT_DETAIL_MODE_NONE) {
            $(valueContent).hide();
            $(attributeContent).hide();
        } else if (mode == self.PRODUCT_DETAIL_MODE_CUSTOM_VALUE) {
            $(attributeContent).hide();
            $(valueContent).show();
        } else {
            $(valueContent).hide();
            $(attributeContent).show();
        }
    },

    setVisibilityForProductDetails: function(catalogEnabled)
    {
        if (catalogEnabled) {
            $('listing_template_specifics_details_container').show();
        } else {
            $('listing_template_specifics_details_container').hide();
        }
    }

    //----------------------------------
});