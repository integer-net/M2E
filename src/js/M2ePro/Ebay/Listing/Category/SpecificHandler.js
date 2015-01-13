EbayListingCategorySpecificHandler = Class.create();
EbayListingCategorySpecificHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(categoryMode, categoryValue, marketplaceId, uniqId)
    {
        this.categoryMode = categoryMode;
        this.categoryValue = categoryValue;
        this.marketplaceId = marketplaceId;

        this.uniqId = uniqId || '';

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
    },

    setDivId: function(divId)
    {
        this.divId = divId;
    },

    setCategoryData: function(categoryData)
    {
        this.categoryData = categoryData;
        this.renderSpecifics();
    },

    initCategory: function()
    {
        if (this.categoryMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY')) {
            $(this.uniqId+'item_specifics_tbody').innerHTML = '';

            if ($$('#'+this.uniqId+'item_specifics_tbody .not-custom').length > 0) {
                $$('#'+this.uniqId+'item_specifics_tbody .not-custom').invoke('show');
            }
        } else {
            $$('#'+this.uniqId+'item_specifics_tbody .not-custom').invoke('hide');
        }
    },

    setAttributes: function(attributes)
    {
        this.attributes = attributes;
        this.prepareAttributes();
    },

    setSelectedSpecifics: function(specifics)
    {
        this.selectedSpecifics = specifics;
    },

    prepareAttributes: function()
    {
        var cachedOptions = '';
        this.attributes.each(function(v) {
            cachedOptions += '<option value="' + v.code + '">' + v.label + '</option>\n';
        });

        this.attributeOptions = cachedOptions;
    },

    //----------------------------------

    renderSpecifics: function()
    {
        var self = this;
        var itemSpecifics = self.categoryData.item_specifics || [];

        $(self.uniqId+'add_custom_container').show();

        self.clearSpecifics();

        if (itemSpecifics.length == 0 || itemSpecifics.specifics.length == 0) {
            $(self.uniqId+'item_specifics_tbody').hide();
            return;
        }

        if (itemSpecifics.mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ATTRIBUTE_SET')) {
            $(self.uniqId+'add_custom_container').hide();
        }

        self.specifics.data = itemSpecifics.specifics;

        self.counter = 0;
        var counter = self.counter;

        self.specifics.data.each(function(specific) {
            var template = $(self.uniqId+'specific_template').innerHTML;

            template = template.replace(/%i%/g, counter);

            template = template.replace(/%attribute_id%/g, specific.id);
            template = template.replace(/%attribute_title%/g, specific.title);
            template = template.replace(/%required%/g, specific.required ? '&nbsp;<span class="required">*</span>' : '');

            template = template.replace(/%relation_mode%/, itemSpecifics.mode);
            template = template.replace(/%relation_id%/, itemSpecifics.mode_relation_id);

            if (specific.mode != undefined) {
                self.addRow();
                $$('#'+self.uniqId+'custom_item_specifics_value_mode_'+counter+' option[value="'+specific.value_mode+'"]')[0].selected = true;
                self.specificCustomModeChange($(self.uniqId+'custom_item_specifics_value_mode_' + counter));

                if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
                    $(self.uniqId+'custom_item_specifics_label_custom_value_' + counter).value = specific.attribute_title;
                    $(self.uniqId+'item_specifics_value_custom_value_' + counter).value = specific.value_custom_value;
                }

                if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
                    $$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                }

                if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE')) {
                    $(self.uniqId+'custom_item_specifics_label_custom_label_attribute_' + counter).value = specific.attribute_title;
                    $$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                }
            } else {
                $(self.uniqId+'item_specifics_tbody').insert(template);

                var recommendedOptionsHtml = '';
                specific.values.each(function(recommended) {
                    recommendedOptionsHtml += '<option value="%value%">%label%</option>'
                        .replace(/%value%/, base64_encode(recommended.id+'') + '-|-||-|-' + base64_encode(recommended.value))
                        .replace(/%label%/, recommended.value);
                });

                $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).insert(recommendedOptionsHtml);
                $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).selectedIndex = -1;

                $(self.uniqId+'item_specifics_value_custom_attribute_' + counter).insert(self.attributeOptions);

                var specificValueMode = $(self.uniqId+'item_specifics_value_mode_' + counter);

                if (specific.required) {
                    if (specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]').length > 0) {
                        specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]')[0].remove();
                    }

                    specificValueMode.select('option')[0].selected = 1;
                }

                if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_TEXT')) {
                    specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]')[0].remove();
                }

                if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_ONE') || specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE')) {
                    specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')+'"]')[0].remove();
                }

                if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE') || specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT')) {
                    $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).writeAttribute('multiple', 'true');
                    var tempOldName = $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).readAttribute('name');
                    $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).writeAttribute('name', tempOldName + '[]');
                }

               self.selectedSpecifics.each(function(selectedSpecific) {

                    if (selectedSpecific.mode != itemSpecifics.mode ||
                        selectedSpecific.mode_relation_id != itemSpecifics.mode_relation_id ||
                        selectedSpecific.attribute_id != specific.id ||
                        selectedSpecific.attribute_title != specific.title) {
                        return;
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')) {
                        $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]')[0].selected = true;
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')) {
                        $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]')[0].selected = true;
                        $$('#'+self.uniqId+'item_specifics_value_ebay_recommended_'+counter+' option').each(function(tempOption){
                            selectedSpecific.value_data.each(function(tempSelected) {
                                var tempSearchValue = base64_encode(tempSelected.id) + '-|-||-|-' + base64_encode(tempSelected.value);
                                if(tempOption.value == tempSearchValue){
                                    tempOption.selected = true;
                                }
                            });
                        });
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
                        $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')+'"]')[0].selected = true;
                        $(self.uniqId+'item_specifics_value_custom_value_'+counter).setValue(selectedSpecific.value_data);
                    }

                    if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
                        $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')+'"]')[0].selected = true;
                        $$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+selectedSpecific.value_data+'"]')[0].selected = true;
                    }
                });

                self.specificModeChange($(self.uniqId+'item_specifics_value_mode_'+counter));
            }

            counter++;
            self.counter = counter;
        });

        $(self.uniqId+'item_specifics_tbody').show();
    },

    clearSpecifics: function()
    {
        $$('#'+self.uniqId+'item_specifics_tbody tr').each(Element.remove);
    },

    //----------------------------------

    addRow: function()
    {
        var self = this;
        var id = self.uniqId+'item_specifics_tbody';
        var i = self.counter;

        var tpl = $(self.uniqId+'specific_template').innerHTML;
        tpl = tpl.replace(/%i%/g, i);
        tpl = tpl.replace(/%attribute_title%%required%/g, '');
        tpl = tpl.replace(/%relation_mode%/g, M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS'));
        $(id).show();
        $(id).insert(tpl);

        $(self.uniqId+'item_specifics_value_custom_attribute_' + i).insert(self.attributeOptions);

        $(self.uniqId+'item_specifics_value_mode_' + i).hide();
        $(self.uniqId+'custom_item_specifics_value_mode_' + i).show();
        $(self.uniqId+'custom_item_specific_remove_' + i).show();
        $(self.uniqId+'specific_' + i + '_row').removeClassName('not-custom');

        self.specificCustomModeChange($(self.uniqId+'custom_item_specifics_value_mode_'+i));

        self.counter++;
    },

    //----------------------------------

    removeSpecific: function(button)
    {
        $(button).up('tr').remove();
    },

    //----------------------------------

    specificModeChange: function(select)
    {
        var self = this;
        var number = select.id.replace(self.uniqId+'item_specifics_value_mode_', '');

        $(self.uniqId+'item_specifics_value_ebay_recommended_' + number,
            self.uniqId+'item_specifics_value_custom_value_' + number,
            self.uniqId+'item_specifics_value_custom_attribute_' + number,
            self.uniqId+'custom_item_specifics_label_custom_attribute_' + number
        ).invoke('hide');

        $(self.uniqId+'attribute_title_' + number).show();

        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')) {
            $(self.uniqId+'item_specifics_value_ebay_recommended_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
            $(self.uniqId+'item_specifics_value_custom_value_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
            $(self.uniqId+'attribute_title_' + number).show();
            $(self.uniqId+'custom_item_specifics_label_custom_attribute_' + number).hide();
            $(self.uniqId+'item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    specificCustomModeChange: function(select)
    {
        var self = this;
        var number = select.id.replace(self.uniqId+'custom_item_specifics_value_mode_', '');

        $(self.uniqId+'item_specifics_value_custom_value_' + number,
            self.uniqId+'item_specifics_value_custom_attribute_' + number,
            self.uniqId+'item_specifics_value_ebay_recommended_' + number).invoke('hide');

        $(self.uniqId+'custom_item_specifics_label_custom_value_' + number,
            self.uniqId+'custom_item_specifics_label_custom_label_attribute_' + number,
            self.uniqId+'custom_item_specifics_label_custom_attribute_' + number).invoke('hide');

        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
            $(self.uniqId+'item_specifics_value_custom_value_'+number).show();
            $(self.uniqId+'custom_item_specifics_label_custom_value_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
            $(self.uniqId+'custom_item_specifics_label_custom_attribute_'+number).show();
            $(self.uniqId+'item_specifics_value_custom_attribute_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE')) {
            $(self.uniqId+'custom_item_specifics_label_custom_label_attribute_'+number).show();
            $(self.uniqId+'item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    getItemSpecifics: function()
    {
        var self  = this;

        var parameters = $(self.uniqId+'category_specific_form').serialize(true);

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
        var self  = this;

        var internalData = $(self.uniqId+'category_specific_form').serialize(true);
        internalData['specifics'] = self.getItemSpecifics();

        return internalData;
    },

    reload: function()
    {
        var self  = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getSpecificHtml'),
        {
            method: 'post',
            parameters: {
                marketplace_id: self.marketplaceId,
                category_mode: self.categoryMode,
                category_value: self.categoryValue,
                div_id: self.divId,
                attributes: self.attributes.join(','),
                internal_data: $(self.uniqId+'category_specific_form').serialize(true)
            },
            onSuccess: function(transport)
            {
                $(self.divId).innerHTML = transport.responseText;
            }
        });
    },

    validate: function()
    {
        return window['specificForm'+this.uniqId].validate();
    },

    renderAttributesWithEmptyOption: function(name, insertTo, value, notRequiried)
    {
        var self  = this;

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

    submitData: function(url)
    {
        var self  = this;

        if (!self.validate()) {
            return;
        }

        var specificData = self.getInternalData();

        self.postForm(url,{specific_data: Object.toJSON(specificData)});
    }

    //----------------------------------
});