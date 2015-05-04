EbayListingCategorySpecificHandler = Class.create();
EbayListingCategorySpecificHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(marketplaceId, categoryMode, categoryValue, uniqId, interfaceMode)
    {
        this.marketplaceId = marketplaceId;
        this.categoryMode  = categoryMode;
        this.categoryValue = categoryValue;
        this.interfaceMode = interfaceMode;

        this.counter       = 0;
        this.uniqId        = uniqId || '';

        this.attributes          = [];
        this.attributeOptions    = '';

        this.dictionarySpecifics = [];
        this.ebaySelectedSpecifics     = [];
        this.customSelectedSpecifics   = [];

        this.specificsJson = [];
    },

    // ----------------------------------------

    setAttributes: function(attributes)
    {
        this.attributes = attributes;
        return this;
    },

    // ----------------------------------------

    setDictionarySpecifics: function(specifics)
    {
        this.dictionarySpecifics = specifics;
        return this;
    },

    setEbaySelectedSpecifics: function(specifics)
    {
        this.ebaySelectedSpecifics = specifics;
        return this;
    },

    setCustomSelectedSpecifics: function(specifics)
    {
        this.customSelectedSpecifics = specifics;
        return this;
    },

    // ----------------------------------------

    getItemSpecifics: function()
    {
        var self  = this;

        var parameters = $(self.uniqId+'category_specific_form').serialize(true);

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getJsonSpecificsFromPost'), {
            method: 'post',
            asynchronous: false,
            parameters: parameters,
            onSuccess: function(transport) {
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

    // ----------------------------------------

    prepareAttributes: function()
    {
        var cachedOptions = '';
        this.attributes.each(function(v) {
            cachedOptions += '<option value="' + v.code + '">' + v.label + '</option>\n';
        });

        this.attributeOptions = cachedOptions;
    },

    //----------------------------------

    prepareBeforeRenderSpecifics: function()
    {
        if (this.categoryMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY')) {
            $(this.uniqId+'item_specifics_tbody').innerHTML = '';

            if ($$('#'+this.uniqId+'item_specifics_tbody .not-custom').length > 0) {
                $$('#'+this.uniqId+'item_specifics_tbody .not-custom').invoke('show');
            }
        } else {
            $$('#'+this.uniqId+'item_specifics_tbody .not-custom').invoke('hide');
        }

        $(this.uniqId+'add_custom_container').show();

        this.prepareAttributes();
        this.clearSpecifics();

        if ((this.dictionarySpecifics.length == 0) &&
            (this.customSelectedSpecifics.length == 0)) {
            $(this.uniqId+'item_specifics_tbody').hide();
            return false;
        }

        return true;
    },

    renderSpecifics: function()
    {
        if (!this.prepareBeforeRenderSpecifics()) {
            return;
        }

        this.renderDictionarySpecific();
        this.renderCustomSelectedSpecifics();

        if (this.interfaceMode != M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            $$('.custom-specific-mode-custom-attribute').each(function(node) {
                node.hide();
            });
        }

        $(this.uniqId+'item_specifics_tbody').show();
    },

    renderDictionarySpecific: function()
    {
        var self = this;

        self.dictionarySpecifics.each(function(specific) {

            var counter                = self.counter;
            var recommendedOptionsHtml = '';

            self.addDictionarySpecificRow(specific);

            for (var i = 0; i < specific.values.length; i++) {
                var recommended = specific.values[i];
                recommendedOptionsHtml += '<option value="%value%">%label%</option>'
                    .replace(/%value%/, base64_encode(recommended.value))
                    .replace(/%label%/, recommended.value);
            }

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

            if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_ONE') ||
                specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE')) {
                specificValueMode.select('option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')+'"]')[0].remove();
            }

            if (specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE') ||
                specific.type == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT')) {
                $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).writeAttribute('multiple', 'true');
                var tempOldName = $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).readAttribute('name');
                $(self.uniqId+'item_specifics_value_ebay_recommended_'+counter).writeAttribute('name', tempOldName + '[]');
            }

            self.chooseEbaySelectedSpecifics(specific, counter);

            self.dictionarySpecificModeChange($(self.uniqId+'item_specifics_value_mode_'+counter));
        });

    },

    renderCustomSelectedSpecifics: function()
    {
        var self = this;

        this.customSelectedSpecifics.each(function(specific) {

            if (self.interfaceMode != M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED') &&
                (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE') ||
                 specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE'))
                ) {
                return;
            }

            var counter = self.counter;

            self.addCustomSpecificRow();

            $$('#'+self.uniqId+'custom_item_specifics_value_mode_'+counter+' option[value="'+specific.value_mode+'"]')[0].selected = true;
            self.customSelectedSpecificChange($(self.uniqId+'custom_item_specifics_value_mode_' + counter));

            if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
                $(self.uniqId+'custom_item_specifics_label_custom_value_' + counter).value = specific.attribute_title;
                $(self.uniqId+'item_specifics_value_custom_value_' + counter).value = specific.value_custom_value;
            }

            if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
                if ($$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]').length > 0) {
                    $$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                } else {
                    $(self.uniqId+'item_specifics_value_custom_attribute_'+counter).selectedIndex = -1;
                }
            }

            if (specific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE')) {
                $(self.uniqId+'custom_item_specifics_label_custom_label_attribute_' + counter).value = specific.attribute_title;
                if ($$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]').length > 0) {
                    $$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+specific.value_custom_attribute+'"]')[0].selected = true;
                } else {
                    $(self.uniqId+'item_specifics_value_custom_attribute_'+counter).selectedIndex = -1;
                }
            }

            self.customSelectedSpecificChange($(self.uniqId+'custom_item_specifics_value_mode_'+counter));
        });
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

    //----------------------------------

    addDictionarySpecificRow: function(specific)
    {
        var template = $(this.uniqId+'specific_template').innerHTML;

        template = template.replace(/%i%/g, this.counter);
        template = template.replace(/%attribute_title%/g, specific.title);
        template = template.replace(/%required%/g, specific.required ? '&nbsp;<span class="required">*</span>' : '');
        template = template.replace(/%relation_mode%/, M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS'));

        $(this.uniqId+'item_specifics_tbody').insert(template);

        ++this.counter;
    },

    addCustomSpecificRow: function()
    {
        var template = $(this.uniqId+'specific_template').innerHTML;

        template = template.replace(/%i%/g, this.counter);
        template = template.replace(/%attribute_title%%required%/g, '');
        template = template.replace(/%relation_mode%/g, M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS'));

        $(this.uniqId+'item_specifics_tbody').show();
        $(this.uniqId+'item_specifics_tbody').insert(template);

        if (this.interfaceMode != M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            $(this.uniqId+'custom_item_specifics_value_mode_' + this.counter).value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE');
            $(this.uniqId+'item_specifics_value_custom_value_'+this.counter).show();
            $(this.uniqId+'custom_item_specifics_label_custom_value_' +this.counter).show();

            var removeNodes = function(node) { node.remove() };
            $$('.custom-specific-mode-custom-attribute').each(removeNodes);
            $$('.custom-specific-mode-custom-label-attribute').each(removeNodes);
        } else {
            $(this.uniqId+'item_specifics_value_custom_attribute_' + this.counter).insert(this.attributeOptions);
            $(this.uniqId+'item_specifics_value_custom_attribute_' + this.counter).show();
        }

        $(this.uniqId+'item_specifics_value_mode_' + this.counter).hide();
        $(this.uniqId+'attribute_title_' + this.counter).hide();
        $(this.uniqId+'custom_item_specifics_value_mode_' + this.counter).show();
        $(this.uniqId+'custom_item_specific_remove_' + this.counter).show();
        $(this.uniqId+'specific_' + this.counter + '_row').removeClassName('not-custom');

        ++this.counter;
    },

    //----------------------------------

    chooseEbaySelectedSpecifics: function(specific, counter)
    {
        var self = this;

        self.ebaySelectedSpecifics.each(function(selectedSpecific) {

            if (selectedSpecific.attribute_title != specific.title) {
                return;
            }

            if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')) {
                $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]')[0].selected = true;
            }

            if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')) {
                $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]')[0].selected = true;
                $$('#'+self.uniqId+'item_specifics_value_ebay_recommended_'+counter+' option').each(function(tempOption) {

                    for (var i = 0; i < selectedSpecific.value_data.length; i++) {
                        var tempSearchValue = base64_encode(selectedSpecific.value_data[i]);
                        if(tempOption.value == tempSearchValue) {
                            tempOption.selected = true;
                        }
                    }
                });
            }

            if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')) {
                $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE')+'"]')[0].selected = true;
                $(self.uniqId+'item_specifics_value_custom_value_'+counter).setValue(selectedSpecific.value_data);
            }

            if (selectedSpecific.value_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE') &&
                self.interfaceMode == M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
                if ($$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+selectedSpecific.value_data+'"]').length != 0) {
                    $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE')+'"]')[0].selected = true;
                    $$('#'+self.uniqId+'item_specifics_value_custom_attribute_'+counter+' option[value="'+selectedSpecific.value_data+'"]')[0].selected = true;
                } else if ($$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]').length > 0) {
                    $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE')+'"]')[0].selected = true;
                } else if ($$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]').length > 0) {
                    $$('#'+self.uniqId+'item_specifics_value_mode_'+counter+' option[value="'+M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED')+'"]')[0].selected = true;
                }
            }
        });
    },

    dictionarySpecificModeChange: function(select)
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

    customSelectedSpecificChange: function(select)
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
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE') &&
            self.interfaceMode == M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            $(self.uniqId+'custom_item_specifics_label_custom_attribute_'+number).show();
            $(self.uniqId+'item_specifics_value_custom_attribute_' + number).show();
        }
        if (select.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE') &&
            self.interfaceMode == M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            $(self.uniqId+'custom_item_specifics_label_custom_label_attribute_'+number).show();
            $(self.uniqId+'item_specifics_value_custom_attribute_' + number).show();
        }

        select.up('td').next('td').select('.validation-advice').each(Element.hide);
    },

    //----------------------------------

    validate: function()
    {
        return window['specificForm'+this.uniqId].validate();
    },

    checkAttributesSelect: function(id, value)
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

    //----------------------------------

    reload: function()
    {
        var self  = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getSpecificHtml'), {
            method: 'post',
            parameters: {
                marketplace_id: self.marketplaceId,
                category_mode: self.categoryMode,
                category_value: self.categoryValue,
                unique_id: self.uniqId,
                attributes: self.attributes.join(','),
                internal_data: $(self.uniqId+'category_specific_form').serialize(true)
            },
            onSuccess: function(transport) {
                $('category_specific_box' + self.uniqId).innerHTML = transport.responseText;
            }
        });
    },

    submitData: function(url)
    {
        var self  = this;

        if (!self.validate()) {
            return;
        }

        var specificData = self.getInternalData();

        self.postForm(url, {specific_data: Object.toJSON(specificData)});
    },

    //----------------------------------

    clearSpecifics: function()
    {
        $$('#'+self.uniqId+'item_specifics_tbody tr').each(Element.remove);
    },

    removeSpecific: function(button)
    {
        $(button).up('tr').remove();
    }

    //----------------------------------
});