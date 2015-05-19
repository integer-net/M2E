CommonAmazonTemplateDescriptionHandler = Class.create(CommonHandler, {

    //----------------------------------

    initialize: function()
    {
        // ugly hack
        //if (version_compare(Prototype.Version,'1.7') < 0) {
        //    for (var property in Selector.xpath.operators) {
        //        Selector.xpath.operators[property] = Selector.xpath.operators[property].split('#{3}').join('#{4}');
        //    }
        //    Selector.patterns['attr'] = /\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\](?![^\[]*\])(?![^\(]*\))/;
        //}
        // -------

        var self = this;

        self.specificHandler = null;

        // -------
        self.categoryPathHiddenInput            = $('category_path');
        self.categoryProductDataNickHiddenInput = $('product_data_nick');
        self.categoryNodeIdHiddenInput          = $('browsenode_id');
        // -------

        self.variationThemes = [];

        // -------

        self.initValidation();
    },

    initValidation: function()
    {
        var self = this;

        self.setValidationCheckRepetitionValue('M2ePro-description-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another Policy. Policy Title must be unique.'),
                                                'Template_Description', 'title', 'id',
                                                M2ePro.formData.general.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        Validation.add('M2ePro-validate-greater-than', M2ePro.translator.translate('Please enter a valid number value in a specified range.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            value = str_replace(',', '.', value);

            if (value.match(/[^\d.]+/g) || value < 0) {
                return false;
            }

            return value >= el.getAttribute('min_value');
        });

        Validation.add('M2ePro-validate-category', M2ePro.translator.translate('You should select Category first'), function(value) {

            if (!self.isNewAsinAccepted()) {
                return true;
            }

            return $('category_path').value != '';
        });
    },

    //----------------------------------

    setSpecificHandler: function(object)
    {
        var self = this;
        self.specificHandler = object;
    },

    //----------------------------------

    isNewAsinAccepted: function()
    {
        return $('new_asin_accepted').value == 1;
    },

    checkMarketplaceSelection: function()
    {
        return $('marketplace_id').value != '';
    },

    checkSpecificsReady: function()
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        if (!self.specificHandler.isReady()) {
            alert(M2ePro.translator.translate('You should select Category first'));
            self.goToGeneralTab();
        }
    },

    //##################################

    duplicate_click: function($headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-description-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another Policy. Policy Title must be unique.'),
                                                'Template_Description', 'title', '','',
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        if (M2ePro.customData.category_locked) {

            M2ePro.customData.category_locked = false;
            this.hideCategoryWarning('category_locked_warning_message');
            $('edit_category_link').show();
        }

        if (M2ePro.customData.marketplace_locked) {

            M2ePro.customData.marketplace_locked = false;
            $('marketplace_locked_warning_message').remove();

            if (!M2ePro.customData.marketplace_force_set) {
                $('marketplace_hidden_input').remove();
                $('marketplace_id').removeAttribute('disabled');
            }
        }

        if (M2ePro.customData.new_asin_switcher_locked) {

            M2ePro.customData.new_asin_switcher_locked = false;
            $('new_asin_locked_warning_message').remove();

            if (!M2ePro.customData.new_asin_switcher_force_set) {
                $('new_asin_accepted_hidden_input').remove();
                $('new_asin_accepted').removeAttribute('disabled');
            }
        }

        CommonHandlerObj.duplicate_click($headId, M2ePro.translator.translate('Add Description Policy'));
    },

    //----------------------------------

    save_click: function($super, url)
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        self.specificHandler.prepareSpecificsDataToPost();
        $super(url);
    },

    save_and_edit_click: function($super, url, tabsId)
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        self.specificHandler.prepareSpecificsDataToPost();
        $super(url, tabsId);
    },

    //##################################

    onChangeMarketplace: function()
    {
        var self = AmazonTemplateDescriptionHandlerObj;
        self.resetCategory();
    },

    onClickEditCategory: function()
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        if (!self.checkMarketplaceSelection()) {
            return alert(M2ePro.translator.translate('You should select Marketplace first.'));
        }

        AmazonTemplateDescriptionCategoryChooserHandlerObj.showEditCategoryPopUp();
    },

    onChangeNewAsinAccepted: function()
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        // --
        var onlyAsinBlocks = $$('.hide-when-asin-is-disabled');

        onlyAsinBlocks.invoke('hide');
        if (this.value == 1) {

            onlyAsinBlocks.invoke('show');

            var worldWideIdMode = $('worldwide_id_mode');
            worldWideIdMode.simulate('change');

            if ($('registered_parameter').value == '' &&
                $('worldwide_id_custom_attribute').value == '') {
                worldWideIdMode.value = -1;
            }

            $('item_package_quantity_mode').simulate('change');
            $('number_of_items_mode').simulate('change');
        }
        // --

        // set is required
        parseInt(this.value) ? $('category_path').addClassName('required-entry')
                             : $('category_path').removeClassName('required-entry');

        self.updateSpanRequirements($('category_path'), this.value);

        self.updateFieldRequirements($('manufacturer_mode'), this.value);
        self.updateFieldRequirements($('brand_mode'), this.value);
        self.updateFieldRequirements($('image_main_mode'), this.value);

        var chooser = $('number_of_items_mode');
        if (chooser.getAttribute('required_attribute_for_new_asin')) {
            if(this.value == 0) chooser.value = '';
            self.updateFieldRequirements(chooser, this.value, 'M2ePro-required-when-visible');
        }

        chooser = $('item_package_quantity_mode');
        if (chooser.getAttribute('required_attribute_for_new_asin')) {
            if(this.value == 0) chooser.value = '';
            self.updateFieldRequirements(chooser, this.value, 'M2ePro-required-when-visible');
        }
        // --
    },

    //----------------------------------

    onChangeWorldwideId: function()
    {
        var target = $('worldwide_id_custom_attribute');

        target.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionHandlerObj.updateHiddenValue(this, target);
        }
    },

    onChangeRegisteredParameter: function()
    {
        var worldwideIdMode = $('worldwide_id_mode'),
            noneOption      = worldwideIdMode.down('option'),
            currentValue    = worldwideIdMode.getAttribute('data-current-value');

        if (!this.value) {
            noneOption.hide();
            worldwideIdMode.simulate('change');

            if ($('worldwide_id_custom_attribute').value == '') {
                worldwideIdMode.selectedIndex = -1;
            }
        } else {
            noneOption.show();
            if (currentValue == '') {
                worldwideIdMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_NONE');
            }
            $('worldwide_id_custom_attribute').value = ''
        }
        worldwideIdMode.setAttribute('data-current-value', '');
    },

    onChangeItemPackageQuantityMode: function()
    {
        var targetCustomValue     = $('item_package_quantity_custom_value_tr'),
            targetCustomAttribute = $('item_package_quantity_custom_attribute');

        targetCustomValue.hide();

        targetCustomAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_VALUE')) {
            targetCustomValue.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionHandlerObj.updateHiddenValue(this, targetCustomAttribute);
        }
    },

    onChangeNumberOfItemsMode: function()
    {
        var targetCustomValue     = $('number_of_items_custom_value_tr'),
            targetCustomAttribute = $('number_of_items_custom_attribute');

        targetCustomValue.hide();

        targetCustomAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::NUMBER_OF_ITEMS_MODE_CUSTOM_VALUE')) {
            targetCustomValue.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionHandlerObj.updateHiddenValue(this, targetCustomAttribute);
        }
    },

    //----------------------------------

    setCategory: function(categoryInfo)
    {
        this.initHiddenValues(categoryInfo);
        this.updateCategoryPathSpan(this.getInterfaceCategoryPath(categoryInfo, true));

        this.updateRequiredAttributesForCategory(categoryInfo);
        this.hideCategoryWarning('category_is_not_accessible_message');
        this.updateWarningMessagesVisibility();

        this.specificHandler.reset();
        this.specificHandler.run(categoryInfo);
    },

    resetCategory: function()
    {
        this.resetHiddenValues();
        this.resetCategoryPathSpan();

        this.resetRequiredAttributesForCategory();
        this.hideCategoryWarning('category_variation_warning_message');

        this.specificHandler.reset();
    },

    //----------------------------------

    prepareEditMode: function()
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        if (M2ePro.formData.general.product_data_nick == '' ||
            M2ePro.formData.general.browsenode_id == '' ||
            M2ePro.formData.general.category_path == '') {

            return;
        }

        var callback = function(transport) {

            if (!transport.responseText) {

                self.resetCategory();
                self.showCategoryWarning('category_is_not_accessible_message');
            } else {

                var categoryInfo = transport.responseText.evalJSON();

                if (categoryInfo.hasOwnProperty('partial_match')) {
                    self.specificHandler.resetFormDataSpecifics();
                }

                self.setCategory(categoryInfo);

                if (M2ePro.customData.category_locked) {
                    self.showCategoryWarning('category_locked_warning_message');
                    $('edit_category_link').hide();
                }

                if (categoryInfo.hasOwnProperty('partial_match')) {
                    self.showCategoryWarning('category_is_not_accessible_message');
                }
            }
        };

        AmazonTemplateDescriptionCategoryChooserHandlerObj.getCategoryInfoFromDictionaryBrowseNodeId(
            M2ePro.formData.general.product_data_nick,
            M2ePro.formData.general.browsenode_id,
            M2ePro.formData.general.category_path,
            callback
        );
    },

    //----------------------------------

    showCategoryWarning: function(item)
    {
        var me = $(item);

        var atLeastOneWarningShown = $$('#category_warning_messages span.category-warning-item').any(function(obj) {
            return $(obj).visible();
        });

        if (atLeastOneWarningShown && me.previous('span.additional-br')) {
            me.previous('span.additional-br').show();
        }

        $(item).show();
        $('category_warning_messages').show();
    },

    hideCategoryWarning: function(item)
    {
        var me = $(item);
        $(item).hide();

        var atLeastOneWarningShown = $$('#category_warning_messages .category-warning-item').any(function(obj) {
            return $(obj).visible();
        });

        if (me.previous('span.additional-br')) {
            me.previous('span.additional-br').hide();
        }

        !atLeastOneWarningShown && $('category_warning_messages').hide();
    },

    //----------------------------------

    initHiddenValues: function(categoryInfo)
    {
        this.categoryPathHiddenInput.value            = this.getInterfaceCategoryPath(categoryInfo);
        this.categoryProductDataNickHiddenInput.value = categoryInfo.product_data_nick;
        this.categoryNodeIdHiddenInput.value          = categoryInfo.browsenode_id;
    },

    resetHiddenValues: function()
    {
        this.categoryPathHiddenInput.value            = '';
        this.categoryProductDataNickHiddenInput.value = '';
        this.categoryNodeIdHiddenInput.value          = '';
    },

    updateCategoryPathSpan: function(path)
    {
        $('category_path_span').update(path);
    },

    resetCategoryPathSpan: function()
    {
        var span = $('category_path_span');
        span.innerHTML = '<span style="color: grey; font-style: italic">' + M2ePro.translator.translate('Not Selected') + '</span>';
    },

    resetRequiredAttributesForCategory: function()
    {
        this.resetManufacturerPartNumberRequired();
        this.resetTargetAudienceRequired();
        this.resetItemDimensionWeightRequired();
        this.resetItemPackageQuantityRequired();
        this.resetNumberOfItemsRequired();
    },

    updateRequiredAttributesForCategory: function(categoryInfo)
    {
        this.updateManufacturerPartNumberRequired(categoryInfo);
        this.updateTargetAudienceRequired(categoryInfo);
        this.updateItemDimensionWeightRequired(categoryInfo);
        this.updateItemPackageQuantityRequired(categoryInfo);
        this.updateNumberOfItemsRequired(categoryInfo);
    },

    updateWarningMessagesVisibility: function()
    {
        var self = AmazonTemplateDescriptionHandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getVariationThemes'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                marketplace_id:     $('marketplace_id').value,
                product_data_nick: self.categoryProductDataNickHiddenInput.value
            },
            onSuccess: function(transport) {

                self.variationThemes = transport.responseText.evalJSON();

                self.variationThemes.length == 0 ? self.showCategoryWarning('category_variation_warning_message')
                                                 : self.hideCategoryWarning('category_variation_warning_message');
            }
        });
    },

    //##################################

    resetManufacturerPartNumberRequired: function()
    {
        this.updateFieldRequirements($('manufacturer_part_number_mode'), 0);
    },

    resetItemDimensionWeightRequired: function()
    {
        var chooser = $('item_dimensions_weight_mode');
        chooser.down('option').show(); // 'None' option
    },

    resetTargetAudienceRequired: function()
    {
        var targetAudienceChooser = $('target_audience_mode');
        targetAudienceChooser.removeAttribute('disabled');

        var hiddenInput = targetAudienceChooser.up('td.value').down('input[type="hidden"]');
        hiddenInput && hiddenInput.remove();

        AmazonTemplateDescriptionDefinitionHandlerObj.forceClearElements('target_audience');
    },

    resetItemPackageQuantityRequired: function()
    {
        var chooser = $('item_package_quantity_mode');

        this.updateFieldRequirements(chooser, 0);
        chooser.removeAttribute('required_attribute_for_new_asin');
    },

    resetNumberOfItemsRequired: function()
    {
        var chooser = $('number_of_items_mode');

        this.updateFieldRequirements(chooser, 0);
        chooser.removeAttribute('required_attribute_for_new_asin');
    },

    //----------------------------------

    updateManufacturerPartNumberRequired: function(categoryInfo)
    {
        if (!categoryInfo.required_attributes.hasOwnProperty('/DescriptionData/MfrPartNumber')) {
            return;
        }

        this.updateFieldRequirements($('manufacturer_part_number_mode'), 1);
    },

    updateTargetAudienceRequired: function(categoryInfo)
    {
        if (!categoryInfo.required_attributes.hasOwnProperty('/DescriptionData/TargetAudience')) {
            return;
        }

        var targetAudienceChooser = $('target_audience_mode');

        targetAudienceChooser.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::TARGET_AUDIENCE_MODE_CUSTOM');
        targetAudienceChooser.simulate('change');
        targetAudienceChooser.setAttribute('disabled', 'disabled');

        targetAudienceChooser.up('td.value').appendChild(new Element('input', {
            name  : targetAudienceChooser.name,
            type  : 'hidden',
            value : M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::TARGET_AUDIENCE_MODE_CUSTOM')
        }));

        categoryInfo.required_attributes['/DescriptionData/TargetAudience'].each(function(value) {
            AmazonTemplateDescriptionDefinitionHandlerObj.forceFillUpElement('target_audience', value);
        });
    },

    updateItemDimensionWeightRequired: function(categoryInfo)
    {
        if (!categoryInfo.required_attributes.hasOwnProperty('/DescriptionData/ItemDimensions/Weight')) {
            return;
        }

        var chooser = $('item_dimensions_weight_mode');
        chooser.down('option').hide(); // 'None' option
        if (chooser.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_MODE_NONE')) {
            chooser.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_MODE_CUSTOM_VALUE');
        }

        chooser.simulate('change');
    },

    updateItemPackageQuantityRequired: function(categoryInfo)
    {
        if (!categoryInfo.required_attributes.hasOwnProperty('/ItemPackageQuantity')) {
            return;
        }

        var chooser = $('item_package_quantity_mode');

        chooser.setAttribute('required_attribute_for_new_asin', 'true');

        if (this.isNewAsinAccepted()) {
            this.updateFieldRequirements(chooser, 1, 'M2ePro-required-when-visible');
        }
    },

    updateNumberOfItemsRequired: function(categoryInfo)
    {
        if (!categoryInfo.required_attributes.hasOwnProperty('/NumberOfItems')) {
            return;
        }

        var chooser = $('number_of_items_mode');

        chooser.setAttribute('required_attribute_for_new_asin', 'true');

        if (this.isNewAsinAccepted()) {
            this.updateFieldRequirements(chooser, 1, 'M2ePro-required-when-visible');
        }
    },

    //##################################

    updateSpanRequirements: function(element, dependence)
    {
        var label = element.up('tr').down('td.label').down('label');

        label.innerHTML = label.innerHTML.replace(' <span class="required">*</span>','');
        if (parseInt(dependence)) label.innerHTML += ' <span class="required">*</span>';
    },

    updateFieldRequirements: function(element, dependence, className)
    {
        className = className || 'required-entry';

        // --
        var firstOption = element.select('option').first();
        if (firstOption.value == '0') {

            firstOption.show();

            var hiddenOpt = element.select('option.hidden-opt').first();
            hiddenOpt && hiddenOpt.remove();

            if (parseInt(dependence)) {

                firstOption.hide();

                element.appendChild(new Element('option', {
                    style: 'display: none;',
                    class: 'hidden-opt'
                }));
            }
        }
        // --

        // --
        if (parseInt(dependence) && element.value == 0) {
            element.value = '';
        }
        // --

        //--
        parseInt(dependence) ? element.addClassName(className)
                             : element.removeClassName(className);
        //--

        element.simulate('change');
        this.updateSpanRequirements(element, dependence);
    },

    //----------------------------------

    getInterfaceCategoryPath: function(categoryInfo, withBrowseNodeId)
    {
        withBrowseNodeId = withBrowseNodeId || false;

        var path = categoryInfo.path.replace(/>/g,' > ') + ' > ' + categoryInfo.title;
        return !withBrowseNodeId ? path : path + ' ('+categoryInfo.browsenode_id+')';
    },

    //----------------------------------

    goToGeneralTab: function()
    {
        amazonTemplateDescriptionEditTabsJsTabs.showTabContent($('amazonTemplateDescriptionEditTabs_general'));
    }

    //----------------------------------
});