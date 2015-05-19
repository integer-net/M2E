AmazonListingVariationProductManageHandler = Class.create(ActionHandler,{

    //----------------------------------

    initialize: function($super,gridHandler)
    {
        var self = this;

        $super(gridHandler);

    },

    //----------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options,options);
        return this;
    },

    initSettingsTab: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form');
        if (form) {
            form.on('change', 'select', function(e) {
                $(e.target).select('.empty') && $(e.target).select('.empty').length && $(e.target).select('.empty')[0].hide();
            });
        }
    },

    //----------------------------------

    parseResponse: function(response)
    {
        if (!response.responseText.isJSON()) {
            return;
        }

        return response.responseText.evalJSON();
    },

    //----------------------------------

    openPopUp: function(productId, title, filter)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(self.options.url.variationProductManage, {
            method: 'post',
            parameters: {
                product_id : productId,
                filter: filter
            },
            onSuccess: function (transport) {

                variationProductManagePopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    top: 5,
                    width: 1100,
                    height: 600,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        ListingGridHandlerObj.unselectAllAndReload();
                    }
                });
                variationProductManagePopup.options.destroyOnClose = true;

                variationProductManagePopup.productId = productId;

                $('modal_dialog_message').update(transport.responseText);
                self.initSettingsTab();
            }
        });
    },

    closeManageVariationsPopup: function()
    {
        variationProductManagePopup.close();
    },

    //----------------------------------

    setGeneralIdOwner: function (value, hideConfirm)
    {
        var self = this;

        if(!hideConfirm && !this.gridHandler.confirm()) {
            return;
        }

        new Ajax.Request(this.options.url.variationProductSetGeneralIdOwner, {
            method: 'post',
            parameters: {
                product_id : variationProductManagePopup.productId,
                general_id_owner: value
            },
            onSuccess: function (transport) {

                var response = self.parseResponse(transport);
                if(response.success) {
                    self.reloadVariationsGrid();
                    return self.reloadSettings();
                }

                if (response.empty_sku) {
                    return self.openSkuPopUp();
                }
                self.openDescriptionTemplatePopUp(response.html);
            }
        });
    },

    openSkuPopUp: function()
    {
        var self = this;
        manageVariationSkuPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: self.options.text.variation_manage_matched_sku_popup_title,
            top: 70,
            width: 470,
            height: 190,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                $('variation_manager_sku_form').reset();
                var errorBlock = $('variation_manager_sku_form_error');
                errorBlock.hide();
            }
        });
        manageVariationSkuPopUp.options.destroyOnClose = false;

        $('modal_dialog_message').insert($('manage_variation_sku_popup').show());

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    setProductSku: function()
    {
        var self = this,
            errorBlock = $('variation_manager_sku_form_error'),
            data;

        data = $('variation_manager_sku_form').serialize(true);

        errorBlock.hide();

        if (data.sku == '') {
            errorBlock.show();
            errorBlock.innerHTML = self.options.text.empty_sku_error;
            return;
        }

        data.product_id = variationProductManagePopup.productId;

        new Ajax.Request(self.options.url.variationProductSetListingProductSku, {
            method: 'post',
            parameters: data,
            onSuccess: function (transport) {

                errorBlock.hide();
                var response = self.parseResponse(transport);
                if(response.success) {
                    manageVariationSkuPopUp.close();
                    self.setGeneralIdOwner(1, true);
                } else {
                    errorBlock.show();
                    errorBlock.innerHTML = response.msg;
                }
            }
        });
    },

    openDescriptionTemplatePopUp: function(contentData)
    {
        var self = this;
        templateDescriptionPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: self.options.text.templateDescriptionPopupTitle,
            top: 70,
            width: 800,
            height: 550,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        templateDescriptionPopup.options.destroyOnClose = true;

        templateDescriptionPopup.productsIds = variationProductManagePopup.productId;

        $('modal_dialog_message').insert(contentData);

        new Ajax.Request(self.options.url.manageVariationViewTemplateDescriptionsGrid, {
            method: 'get',
            parameters: {
                product_id : variationProductManagePopup.productId
            },
            onSuccess: function (transport) {
                $('templateDescription_grid').update(transport.responseText);
                $('templateDescription_grid').show();
            }
        });

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '600px';
        }, 50);
    },

    mapToTemplateDescription: function (el, templateId, mapToGeneralId)
    {
        var self = this;

        new Ajax.Request(self.options.url.manageVariationMapToTemplateDescription, {
            method: 'post',
            parameters: {
                product_id : variationProductManagePopup.productId,
                template_id : templateId
            },
            onSuccess: function (transport) {
                var response = self.parseResponse(transport);
                if(response.success) {
                    templateDescriptionPopup.close();
                    self.setGeneralIdOwner(1, true);
                }
            }
        });

        templateDescriptionPopup.close();
    },

    //----------------------------------

    changeVariationTheme: function(el)
    {
        var attrs = $('variation_manager_theme_attributes');
        attrs.hide();
        attrs.next().show();

        el.hide();
        el.next().show();

        var channelVariationThemeNote = $('channel_variation_theme_note');
        channelVariationThemeNote && channelVariationThemeNote.hide();
    },

    setVariationTheme: function()
    {
        var self = this,
            value = $('variation_manager_theme').value;

        if(value) {
            new Ajax.Request(this.options.url.variationProductSetVariationTheme, {
                method: 'post',
                parameters: {
                    product_id : variationProductManagePopup.productId,
                    variation_theme: value
                },
                onSuccess: function (transport) {
                    var response = self.parseResponse(transport);
                    if(response.success) {
                        self.reloadSettings();
                    }
                }
            });
        }
    },

    cancelVariationTheme: function(el) {
        var attrs = $('variation_manager_theme_attributes');
        attrs.show();
        attrs.next().hide();

        el.up().previous().show();
        el.up().hide();

        var channelVariationThemeNote = $('channel_variation_theme_note');
        channelVariationThemeNote && channelVariationThemeNote.show();
    },

    changeMatchedAttributes: function(el)
    {
        $$('.variation_manager_attributes_amazon_value').each(function(el){
            el.hide();
        });

        $$('.variation_manager_attributes_amazon_select').each(function(el){
            el.show();
        });

        el.hide();
        el.next().show();
        el.next().next().show();
    },

    isValidAttributes: function()
    {
        var self = this,
            existedValues = [],
            isValid = true,
            form = $('variation_manager_attributes_form');

        if (!form || (form && form.serialize() == '')) {
            return true;
        }
        var data = form.serialize(true);

        form.select('.validation-advice').each(function(el){
            el.hide();
        });

        if (typeof data['variation_attributes[amazon_attributes][]'] == 'string') {

            if (data['variation_attributes[amazon_attributes][]'] != '') {
                return true;
            }

            var errorEl = form.select('.validation-advice')[0];
            errorEl.show();
            errorEl.update(self.options.text.variation_manage_matched_attributes_error);

            return false;
        }

        var i = 0;
        data['variation_attributes[amazon_attributes][]'].each(function(attrVal){
            if(attrVal != '' && existedValues.indexOf(attrVal) === -1) {
                existedValues.push(attrVal);
            } else {
                isValid = false;

                var errorEl = $('variation_manager_attributes_error_'+i);
                errorEl.show();
                if(attrVal == '') {
                    errorEl.update(self.options.text.variation_manage_matched_attributes_error);
                } else {
                    errorEl.update(self.options.text.variation_manage_matched_attributes_error_duplicate)
                }
            }
            i++;
        });

        return isValid;
    },

    setMatchedAttributes: function()
    {
        var self = this,
            data;

        if(!self.isValidAttributes()) {
            return;
        }

        $('variation_manager_attributes_form').select('.validation-advice').each(function(el){
            el.hide();
        });

        data = $('variation_manager_attributes_form').serialize(true);
        data.product_id = variationProductManagePopup.productId;

        new Ajax.Request(this.options.url.variationProductSetMatchedAttributes, {
            method: 'post',
            parameters: data,
            onSuccess: function (transport) {
                var response = self.parseResponse(transport);
                if(response.success) {
                    self.reloadSettings();
                    self.reloadVariationsGrid();
                }
            }
        });
    },

    cancelMatchedAttributes: function(el)
    {
        $$('.variation_manager_attributes_amazon_value').each(function(el){
            el.show();
        });

        $$('.variation_manager_attributes_amazon_select').each(function(el){
            el.hide();
        });

        $('variation_manager_attributes_form').select('.validation-advice').each(function(el){
            el.hide();
        });

        el.hide();
        el.previous().show();
        el.next().hide();
    },

    //----------------------------------

    reloadSettings: function(callback, hideMask)
    {
        var self = this;

        new Ajax.Request(this.options.url.viewVariationsSettingsAjax, {
            method: 'post',
            parameters: {
                product_id : variationProductManagePopup.productId
            },
            onSuccess: function (transport) {

                var response = self.parseResponse(transport);

                $('amazonVariationProductManageTabs_settings_content').update(response.html);
                self.initSettingsTab();

                if (response.errors_count == 0) {
                    var img = $('amazonVariationProductManageTabs_settings').down('img');
                    if (img) {
                        img.hide();
                    }
                }

                if(callback) {
                    callback.call();
                }
            }
        });

        hideMask && $('loading-mask').hide();
    },

    loadVariationsGrid: function(showMask)
    {
        var self = this;
        showMask && $('loading-mask').show();

        var gridIframe = $('amazonVariationsProductManageVariationsGridIframe');

        if(gridIframe) {
            gridIframe.remove();
        }

        var iframe = new Element('iframe', {
            id: 'amazonVariationsProductManageVariationsGridIframe',
            src: $('amazonVariationsProductManageVariationsGridIframeUrl').value,
            width: '100%',
            height: '100%',
            style: 'border: none;'
        });

        $('amazonVariationsProductManageVariationsGrid').insert(iframe);

        Event.observe($('amazonVariationsProductManageVariationsGridIframe'), 'load', function() {
            $('loading-mask').hide();
        });
    },

    reloadVariationsGrid: function()
    {
        var gridIframe = $('amazonVariationsProductManageVariationsGridIframe');

        if(!gridIframe) {
            return;
        }
        gridIframe.contentWindow.ListingGridHandlerObj.actionHandler.gridHandler.unselectAllAndReload();
    },

    //---------------------------------

    openVariationsTab: function (createNewAsin) {
        amazonVariationProductManageTabsJsTabs.showTabContent(amazonVariationProductManageTabsJsTabs.tabs[0]);
        $('amazonVariationsProductManageVariationsGridIframe').contentWindow.ListingGridHandlerObj.showNewChildForm(createNewAsin);
    }

    //---------------------------------
});