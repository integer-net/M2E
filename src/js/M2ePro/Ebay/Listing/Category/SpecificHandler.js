EbayListingCategorySpecificHandler = Class.create();
EbayListingCategorySpecificHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(categoryMode, categoryValue, marketplaceId)
    {
        this.categoryMode = categoryMode;
        this.categoryValue = categoryValue;
        this.marketplaceId = marketplaceId;

        this.specifics = {};
        this.specifics.data = {};
        this.counter = 0;
        this.attributes = [];
        this.attributeOptions = '';

        this.divId = '';
        this.interfaceMode = null;

        this.categoryData = {};
        this.selectedSpecifics = [];

        this.specificsJson = [];

        Validation.add('M2ePro-validate-motors-specifics-attribute', M2ePro.translator.translate('Only Text Area type attributes are allowed.'), function(value) {
            if (!value) {
                return true;
            }

            var checkResult = false;

            new Ajax.Request( M2ePro.url.get('adminhtml_ebay_category/getAttributeType') ,
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

    setDivId: function(divId)
    {
        var self = EbayListingCategorySpecificHandlerObj;

        self.divId = divId;
    },

    setCategoryData: function(categoryData)
    {
        var self = EbayListingCategorySpecificHandlerObj;

        self.categoryData = categoryData;
        self.renderSpecifics();
        self.setVisibilityForMotorsSpecificsContainer(true);
    },

    initCategory: function()
    {
        var self = EbayListingCategorySpecificHandlerObj;

        self.renderAttributesWithEmptyOption('motors_specifics_attribute', 'motors_specifics_attribute_td', null, true);
        $('motors_specifics_attribute').addClassName('M2ePro-validate-motors-specifics-attribute');

        if (self.categoryMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY')) {
            $('item_specifics_tbody').innerHTML = '';

            if ($$('#item_specifics_tbody .not-custom').length > 0) {
                $$('#item_specifics_tbody .not-custom').invoke('show');
            }
        } else {
            $$('#item_specifics_tbody .not-custom').invoke('hide');
        }
    },

    setAttributes: function(attributes)
    {
        var self = EbayListingCategorySpecificHandlerObj;

        self.attributes = attributes;
        self.prepareAttributes();
    },

    setSelectedSpecifics: function(specifics)
    {
        var self = EbayListingCategorySpecificHandlerObj;

        self.selectedSpecifics = specifics;
    },

    prepareAttributes: function()
    {
        var self = EbayListingCategorySpecificHandlerObj;

        var cachedOptions = '';
        self.attributes.each(function(v) {
            cachedOptions += '<option value="' + v.code + '">' + v.label + '</option>\n';
        });

        self.attributeOptions = cachedOptions;
    },

    //----------------------------------

    renderSpecifics: function()
    {
        var self = EbayListingCategorySpecificHandlerObj;
        var itemSpecifics = self.categoryData.item_specifics || [];

        $('add_custom_container').show();

        self.clearSpecifics();

        if (itemSpecifics.length == 0 || itemSpecifics.specifics.length == 0) {
            $('item_specifics_tbody').hide();
            return;
        }

        if (itemSpecifics.mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ATTRIBUTE_SET')) {
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

                if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
                    $('custom_item_specifics_label_custom_value_' + counter).value = specific.attribute_title;
                    $('item_specifics_value_custom_value_' + counter).value = specific.value_custom_value;
                }

                if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
                    $$('#item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                }

                if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE')) {
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

                $('item_specifics_value_custom_attribute_' + counter).insert(self.attributeOptions);

                var specificValueMode = $('item_specifics_value_mode_' + counter);

                if (specific.required) {
                    specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]')[0].remove();
                    specificValueMode.select('option')[0].selected = 1;
                }

                if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_TEXT')) {
                    specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]')[0].remove();
                }

                if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_ONE') || specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE')) {
                    specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')+'"]')[0].remove();
                }

                if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE') || specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT')) {
                    $('item_specifics_value_ebay_recommended_'+counter).writeAttribute('multiple', 'true');
                    var tempOldName = $('item_specifics_value_ebay_recommended_'+counter).readAttribute('name');
                    $('item_specifics_value_ebay_recommended_'+counter).writeAttribute('name', tempOldName + '[]');
                }

               self.selectedSpecifics.each(function(selectedSpecific) {

                    if (selectedSpecific.mode != itemSpecifics.mode ||
                        selectedSpecific.mode_relation_id != itemSpecifics.mode_relation_id ||
                        selectedSpecific.attribute_id != specific.id ||
                        selectedSpecific.attribute_title != specific.title) {
                        return;
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]')[0].selected = true;
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]')[0].selected = true;
                        $$('#item_specifics_value_ebay_recommended_'+counter+' option').each(function(tempOption){
                            selectedSpecific.value_data.each(function(tempSelected) {
                                var tempSearchValue = base64_encode(tempSelected.id) + '-|-||-|-' + base64_encode(tempSelected.value);
                                if(tempOption.value == tempSearchValue){
                                    tempOption.selected = true;
                                }
                            });
                        });
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')+'"]')[0].selected = true;
                        $('item_specifics_value_custom_value_'+counter).setValue(selectedSpecific.value_data);
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
                        $$('#item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')+'"]')[0].selected = true;
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
        var self = EbayListingCategorySpecificHandlerObj;

        $$('#item_specifics_tbody tr').each(Element.remove);
    },

    //----------------------------------

    setVisibilityForMotorsSpecificsContainer: function(isVisible)
    {
        var self = EbayListingCategorySpecificHandlerObj;

        var canShow = (isVisible && self.marketplaceId == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS')) ? true : false;
        $('listing_category_specific_motors_specifics_container')[canShow ? 'show' : 'hide']();

        if (!canShow) {
            $('motors_specifics_attribute').value = '';
        }
    },

    //----------------------------------

    addRow: function()
    {
        var self = EbayListingCategorySpecificHandlerObj;
        var id = 'item_specifics_tbody';
        var i = self.counter;

        var tpl = $('specific_template').innerHTML;
        tpl = tpl.replace(/%i%/g, i);
        tpl = tpl.replace(/%attribute_title%%required%/g, '');
        tpl = tpl.replace(/%relation_mode%/g, M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS'));
        $(id).show();
        $(id).insert(tpl);

        $('item_specifics_value_custom_attribute_' + i).insert(self.attributeOptions);

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
        var self = EbayListingCategorySpecificHandlerObj;

        $(button).up('tr').remove();
    },

    //----------------------------------

    specificModeChange: function(select)
    {
        var self = EbayListingCategorySpecificHandlerObj;
        var number = select.id.replace('item_specifics_value_mode_', '');

        $('item_specifics_value_ebay_recommended_' + number,
            'item_specifics_value_custom_value_' + number,
            'item_specifics_value_custom_attribute_' + number,
            'custom_item_specifics_label_custom_attribute_' + number
        ).invoke('hide');

        $('attribute_title_' + number).show();

        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')) {
            $('item_specifics_value_ebay_recommended_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
            $('item_specifics_value_custom_value_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
            $('attribute_title_' + number).show();
            $('custom_item_specifics_label_custom_attribute_' + number).hide();
            $('item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    specificCustomModeChange: function(select)
    {
        var self = EbayListingCategorySpecificHandlerObj;
        var number = select.id.replace('custom_item_specifics_value_mode_', '');

        $('item_specifics_value_custom_value_' + number,
            'item_specifics_value_custom_attribute_' + number,
            'item_specifics_value_ebay_recommended_' + number).invoke('hide');

        $('custom_item_specifics_label_custom_value_' + number,
            'custom_item_specifics_label_custom_label_attribute_' + number,
            'custom_item_specifics_label_custom_attribute_' + number).invoke('hide');

        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
            $('item_specifics_value_custom_value_'+number).show();
            $('custom_item_specifics_label_custom_value_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
            $('custom_item_specifics_label_custom_attribute_'+number).show();
            $('item_specifics_value_custom_attribute_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE')) {
            $('custom_item_specifics_label_custom_label_attribute_'+number).show();
            $('item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    getItemSpecifics: function()
    {
        var self  = EbayListingCategorySpecificHandlerObj;

        var parameters = $('category_specific_form').serialize(true);

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getJsonSpecificsFromPost'),
        {
            method: 'post',
            asynchronous: false,
            parameters: parameters,
            onSuccess: function(transport)
            {
                if (transport.responseText.length != 0) {
                    self.specificsJson = transport.responseText.evalJSON();
                }
            }
        });

        return self.specificsJson;
    },

    getInternalData: function()
    {
        var self  = EbayListingCategorySpecificHandlerObj;

        var internalData = $('category_specific_form').serialize(true);
        internalData['specifics'] = self.getItemSpecifics();

        return internalData;
    },

    reload: function()
    {
        var self  = EbayListingCategorySpecificHandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getSpecificHtml'),
        {
            method: 'post',
            parameters: {
                marketplace_id: self.marketplaceId,
                category_mode: self.categoryMode,
                category_value: self.categoryValue,
                div_id: self.divId,
                attributes: self.attributes.join(','),
                internal_data: $('category_specific_form').serialize(true)
            },
            onSuccess: function(transport)
            {
                $(self.divId).innerHTML = transport.responseText;
            }
        });
    },

    validate: function()
    {
        return specificForm.validate();
    },

    renderAttributesWithEmptyOption: function(name, insertTo, value, notRequiried)
    {
        var self  = EbayListingCategorySpecificHandlerObj;

        var className = notRequiried ? '' : ' class="M2ePro-required-when-visible"';
        var txt = '<select name="' + name + '" id="' + name + '" ' + className + '>\n';

        txt += '<option class="empty"></option>\n';
        txt += self.attributeOptions;

        if ($(insertTo + '_note') != null && $$('#' + insertTo + '_note').length != 0) {
            $(insertTo).innerHTML = txt + $(insertTo + '_note').innerHTML;
        } else {
            $(insertTo).innerHTML = txt;
        }

        self.checkAttributesSelect(name, value);
    },

    checkAttributesSelect: function (id, value)
    {
        if ($(id)) {
            if (typeof M2ePro.formData[id] != 'undefined') {
                $(id).value = M2ePro.formData[id];
            }
            if (value) {
                $(id).value = value;
            }
        }
    },

    submitData: function(url, redirectUrl)
    {
        var self  = EbayListingCategorySpecificHandlerObj;

        if (!self.validate()) {
            return;
        }

        var specificData = Object.toJSON(self.getInternalData());

        new Ajax.Request(url,
        {
            method: 'post',
            parameters: {
                specific_data: specificData
            },
            onSuccess: function(transport)
            {
                setLocation(redirectUrl);
            }
        });
    }

    //----------------------------------
});