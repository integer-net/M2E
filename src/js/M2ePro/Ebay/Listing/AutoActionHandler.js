EbayListingAutoActionHandler = Class.create(CommonHandler, {

    internalData: {},

    magentoCategoryIdsFromOtherGroups: {},
    magentoCategoryTreeChangeEventInProgress: false,

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-category-selection', M2ePro.translator.translate('You must select at least 1 category.'), function() {
            return categories_selected_items.length > 0
        });

        Validation.add('M2ePro-validate-category-group-title', M2ePro.translator.translate('Rule with the same title already exists.'), function(value, element) {
            var unique = true;

            new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/isCategoryGroupTitleUnique') ,
            {
                method: 'get',
                asynchronous : false,
                parameters : {
                    group_id: $('group_id').value,
                    title: $('group_title').value
                },
                onSuccess: function (transport)
                {
                    unique = transport.responseText.evalJSON()['unique'];
                }
            });

            return unique;
        });
    },

    clear: function()
    {
        this.internalData = {};
        this.magentoCategoryTreeChangeEventInProgress = false;
    },

    //----------------------------------

    loadAutoActionHtml: function(mode, callback)
    {
        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/index') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                auto_mode: mode || null
            },
            onSuccess: function (transport)
            {
                var content = transport.responseText;
                var title = M2ePro.translator.translate('Automatic Actions');

                this.clear();
                this.openPopUp(title, content);

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    //----------------------------------

    openPopUp: function(title, content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            minWidth: 800,
            maxHeight: 500,
            width: 800,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                EbayListingAutoActionHandlerObj.clear();

                return true;
            }
        };

        try {
            Windows.getFocusedWindow() || Dialog.info(null, config);
            Windows.getFocusedWindow().setTitle(title);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    //----------------------------------

    addingModeChange: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY')) {
            $('confirm_button').hide();
            $('continue_button').show();
            $('breadcrumb_container').show();
        } else {
            $('continue_button').hide();
            $('breadcrumb_container').hide();
            $('confirm_button').show();
        }
    },

    //----------------------------------

    loadCategoryChooser: function(callback)
    {
        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/getCategoryChooserHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                auto_mode: $('auto_mode').value,
                // this parameter only for auto_mode=category
                magento_category_id: typeof categories_selected_items != 'undefined' ? categories_selected_items[0] : null
            },
            onSuccess: function (transport)
            {
                $('data_container').update(transport.responseText);

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    loadSpecific: function(callback)
    {
        var category = EbayListingCategoryChooserHandlerObj.getSelectedCategory(0); // todo constant

        if (!category.mode) {
            return;
        }

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/getCategorySpecificHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                auto_mode: this.internalData.auto_mode,
                category_mode: category.mode,
                category_value: category.value,
                // this parameter only for auto_mode=category
                magento_category_id: typeof categories_selected_items != 'undefined' ? categories_selected_items[0] : null
            },
            onSuccess: function (transport)
            {
                $('data_container').innerHTML = transport.responseText;
                try {
                    $('data_container').innerHTML.evalScripts();
                } catch (ignored) {

                }

                if (typeof callback == 'function') {
                    callback();
                }

            }.bind(this)
        });
    },

    //----------------------------------

    loadAutoCategoryForm: function(groupId, callback)
    {
        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/getAutoCategoryFormHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                group_id: groupId || null
            },
            onSuccess: function (transport)
            {
                $('data_container').replace(transport.responseText);
                this.magentoCategoryTreeChangeEventInProgress = false;

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    magentoCategorySelectCallback: function(selectedCategories)
    {
        if (this.magentoCategoryTreeChangeEventInProgress) {
            return;
        }

        this.magentoCategoryTreeChangeEventInProgress = true;

        var latestCategory = selectedCategories[selectedCategories.length - 1];

        if (!latestCategory || typeof this.magentoCategoryIdsFromOtherGroups[latestCategory] == 'undefined') {
            this.magentoCategoryTreeChangeEventInProgress = false;
            return;
        }

        var template = $('dialog_confirm_container');

        template.down('.dialog_confirm_content').innerHTML = $('dialog_confirm_content').innerHTML;
        template.down('.dialog_confirm_content').innerHTML = template.down('.dialog_confirm_content')
            .innerHTML
            .replace('%s', this.magentoCategoryIdsFromOtherGroups[latestCategory]);

        Dialog._openDialog(template.innerHTML, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            title: 'Remove Category',
            width: 400,
            height: 80,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: "selected-category-already-used",
            ok: function() {
                new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/deleteCategory') ,
                {
                    method: 'post',
                    asynchronous : true,
                    parameters : {
                        category_id: latestCategory
                    },
                    onSuccess: function (transport)
                    {
                        delete EbayListingAutoActionHandlerObj.magentoCategoryIdsFromOtherGroups[latestCategory];
                    }
                });

                return true;
            }.bind(this),
            cancel: function() {
                tree.getNodeById(latestCategory).ui.check(false);
            },
            onClose: function() {
                EbayListingAutoActionHandlerObj.magentoCategoryTreeChangeEventInProgress = false;
            }
        });
    },

    //----------------------------------

    highlightBreadcrumbStep: function(step)
    {
        $$('#breadcrumb_container .breadcrumb').each(function(element) { element.removeClassName('selected'); });

        $('step_' + step).addClassName('selected');
    },

    //----------------------------------

    globalStepTwo: function()
    {
        EbayListingAutoActionHandlerObj.collectData();

        var callback = function() {
            $('continue_button')
                .stopObserving('click')
                .observe('click', EbayListingAutoActionHandlerObj.globalStepThree);

            EbayListingAutoActionHandlerObj.highlightBreadcrumbStep(2);
        };

        EbayListingAutoActionHandlerObj.loadCategoryChooser(callback);
    },

    globalStepThree: function()
    {
        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        EbayListingAutoActionHandlerObj.collectData();

        var callback = function() {
            EbayListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };

        EbayListingAutoActionHandlerObj.loadSpecific(callback);
    },

    //----------------------------------

    websiteStepTwo: function()
    {
        EbayListingAutoActionHandlerObj.collectData();

        var callback = function () {
            $('continue_button')
                .stopObserving('click')
                .observe('click', EbayListingAutoActionHandlerObj.websiteStepThree);

            EbayListingAutoActionHandlerObj.highlightBreadcrumbStep(2);
        };

        EbayListingAutoActionHandlerObj.loadCategoryChooser(callback);
    },

    websiteStepThree: function()
    {
        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        EbayListingAutoActionHandlerObj.collectData();

        var callback = function() {
            EbayListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };

        EbayListingAutoActionHandlerObj.loadSpecific(callback);
    },

    //----------------------------------

    isCategoryAlreadyUsed: function(categoryId)
    {
        return this.magentoCategoryUsedIds.indexOf(categoryId) != -1;
    },

    categoryCancel: function()
    {
        EbayListingAutoActionHandlerObj.loadAutoActionHtml(
            M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY')
        );
    },

    categoryStepOne: function(groupId)
    {
        var callback = function() {
            $('add_button').hide();
            $('reset_button').hide();
            $('close_button').hide();
            $('cancel_button').show();
        };

        this.loadAutoCategoryForm(groupId, callback);
    },

    categoryStepTwo: function()
    {
        if (!EbayListingAutoActionHandlerObj.validate()) {
            return;
        }

        EbayListingAutoActionHandlerObj.collectData();

        var callback = function () {
            $('continue_button')
                .stopObserving('click')
                .observe('click', EbayListingAutoActionHandlerObj.categoryStepThree);

            EbayListingAutoActionHandlerObj.highlightBreadcrumbStep(2);
        };

        EbayListingAutoActionHandlerObj.loadCategoryChooser(callback);
    },

    categoryStepThree: function()
    {
        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        EbayListingAutoActionHandlerObj.collectData();

        var callback = function() {
            EbayListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };

        EbayListingAutoActionHandlerObj.loadSpecific(callback);
    },

    //----------------------------------

    categoryDeleteGroup: function(groupId)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/deleteCategoryGroup') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : {
                group_id: groupId
            },
            onSuccess: function (transport)
            {
                ebayListingAutoActionModeCategoryGroupGridJsObject.doFilter();
            }.bind(this)
        });
    },

    //----------------------------------

    validate: function()
    {
        var validationResult = [];

        if ($('edit_form')) {
            validationResult = Form.getElements('edit_form').collect(Validation.validate);

            if ($('auto_mode') && $('auto_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY')) {
                validationResult.push(Validation.validate($('validate_category_selection')));
            }
        } else if ($('category_specific_form')) {
            validationResult = Form.getElements('category_specific_form').collect(Validation.validate);
        }

        if (validationResult.indexOf(false) != -1) {
            return false;
        }

        return true;
    },

    confirm: function()
    {
        if ($('ebayListingAutoActionModeCategoryGroupGrid')) {
            Windows.getFocusedWindow().close();
            return;
        }

        if (!EbayListingAutoActionHandlerObj.validate()) {
            return;
        }

        EbayListingAutoActionHandlerObj.collectData();

        var callback;
        if (EbayListingAutoActionHandlerObj.internalData.auto_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY')) {
            callback = EbayListingAutoActionHandlerObj.loadAutoActionHtml.bind(EbayListingAutoActionHandlerObj);
        } else {
            callback = Windows.getFocusedWindow().close.bind(Windows.getFocusedWindow());
        }

        EbayListingAutoActionHandlerObj.submitData(callback);
    },

    collectData: function()
    {
        if ($('auto_mode')) {
            switch (parseInt($('auto_mode').value)) {
                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL'):
                    EbayListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_global_adding_mode: $('auto_global_adding_mode').value,
                        auto_global_adding_template_category_id: null
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE'):
                    EbayListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_website_adding_mode: $('auto_website_adding_mode').value,
                        auto_website_adding_template_category_id: null,
                        auto_website_deleting_mode: $('auto_website_deleting_mode').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY'):
                    EbayListingAutoActionHandlerObj.internalData = {
                        group_id: $('group_id').value,
                        group_title: $('group_title').value,
                        auto_mode: $('auto_mode').value,
                        adding_mode: $('adding_mode').value,
                        adding_template_category_id: null,
                        deleting_mode: $('deleting_mode').value,
                        categories: categories_selected_items
                    };
                    break;
            }
        }

        if ($('ebay_category_chooser')) {
            EbayListingAutoActionHandlerObj.internalData.template_category_data = EbayListingCategoryChooserHandlerObj.getInternalData();
        }

        if ($('category_specific_form')) {
            EbayListingAutoActionHandlerObj.internalData.template_category_specifics_data = EbayListingCategorySpecificHandlerObj.getInternalData();
        }
    },

    submitData: function(callback)
    {
        var data = this.internalData;

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/save'),
        {
            method: 'post',
            asynchronous : true,
            parameters : {
                auto_action_data: Object.toJSON(data)
            },
            onSuccess: function (transport)
            {
                if (typeof callback == 'function') {
                    callback();
                }
            }
        });
    },

    reset: function(skipConfirmation)
    {
        skipConfirmation = skipConfirmation || false;

        if (!skipConfirmation && !confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_autoAction/reset') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : {},
            onSuccess: function (transport)
            {
                EbayListingAutoActionHandlerObj.loadAutoActionHtml();
            }
        });
    }

    //----------------------------------

});
