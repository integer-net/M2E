EbayListingSettingsGridHandler = Class.create(GridHandler, {

    productIdCellIndex: 1,
    productTitleCellIndex: 2,

    selectedProductsIds: [],
    selectedCategoriesData: {},

    //----------------------------------

    prepareActions: function()
    {
        this.actions = {

            editCategorySettingsAction: function(id) {
                this.editCategorySettings(id);
            }.bind(this),

            editAllSettingsAction: function (id) {
                this.editSettings(id);
            }.bind(this),
            editGeneralSettingsAction: function (id) {
                this.editSettings(id, 'general');
            }.bind(this),
            editSellingSettingsAction: function (id) {
                this.editSettings(id, 'selling');
            }.bind(this),
            editSynchSettingsAction: function (id) {
                this.editSettings(id, 'synchronization');
            }.bind(this),

            editMotorsSpecificsAction: function() {
                EbayMotorSpecificHandlerObj.openPopUp();
            }
        };
    },

    //----------------------------------

    editCategorySettings: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing/getCategoryChooserHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function (transport)
            {
                var title = M2ePro.translator.translate('eBay Categories');

                this.openPopUp(title, transport.responseText);

                $('cancel_button').observe('click', function() { Windows.getFocusedWindow().close(); });

                $('done_button').observe('click', function() {
                    if (!EbayListingCategoryChooserHandlerObj.validate()) {
                        return;
                    }

                    this.selectedCategoriesData = EbayListingCategoryChooserHandlerObj.getInternalData();
                    this.editSpecificSettings();
                }.bind(this));
            }.bind(this)
        });
    },

    editSpecificSettings: function()
    {
        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing/getCategorySpecificHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(','),
                category_mode: EbayListingCategoryChooserHandlerObj.getSelectedCategory(0)['mode'],
                category_value: EbayListingCategoryChooserHandlerObj.getSelectedCategory(0)['value']
            },
            onSuccess: function (transport)
            {
                var title = M2ePro.translator.translate('Specifics');

                this.openPopUp(title, transport.responseText);

                $('cancel_button').observe('click', function() { Windows.getFocusedWindow().close(); });
                $('done_button').observe('click', this.saveCategoryTemplate.bind(this));
            }.bind(this)
        });
    },

    saveCategoryTemplate: function()
    {
        if (!EbayListingCategorySpecificHandlerObj.validate()) {
            return;
        }

        var categoryTemplateData = {};
        categoryTemplateData = Object.extend(categoryTemplateData, this.selectedCategoriesData);
        categoryTemplateData = Object.extend(categoryTemplateData, EbayListingCategorySpecificHandlerObj.getInternalData());

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing/saveCategoryTemplate') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(','),
                template_category_data: Object.toJSON(categoryTemplateData)
            },
            onSuccess: function (transport)
            {
                Windows.getFocusedWindow().close();
                this.getGridObj().doFilter();
            }.bind(this)
        });
    },

    //----------------------------------

    editSettings: function(id, tab)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_template/editListingProduct') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(','),
                tab: tab || ''
            },
            onSuccess: function (transport)
            {
                var title = this.getPopUpTitle(tab, this.getSelectedProductsTitles());

                this.openPopUp(title, transport.responseText);

                ebayListingTemplateEditTabsJsTabs.moveTabContentInDest();
            }.bind(this)
        });
    },

    //----------------------------------

    saveSettings: function(savedTemplates)
    {
        var requestParams = {};

        // push information about saved templates into the request params
        //----------------------------------
        $H(savedTemplates).each(function(i) {
            requestParams[i.key] = i.value;
        });
        //----------------------------------

        //----------------------------------
        requestParams['ids'] = this.selectedProductsIds.join(',');
        //----------------------------------

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_template/saveListingProduct') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : requestParams,
            onSuccess: function (transport)
            {
                Windows.getFocusedWindow().close();
                this.getGridObj().doFilter();
            }.bind(this)
        });
    },

    //----------------------------------

    getSelectedProductsTitles: function()
    {
        if (this.selectedProductsIds.length > 3) {
            return '';
        }

        var title = '';

        // use the names of only first three products for pop up title
        for (var i = 0; i < 3; i++) {
            if (typeof this.selectedProductsIds[i] == 'undefined') {
                break;
            }

            if (title != '') {
                title += ', ';
            }

            title += this.getProductNameByRowId(this.selectedProductsIds[i]);
        }

        return title;
    },

    //----------------------------------

    getPopUpTitle: function(tab, productTitles)
    {
        var title;

        switch (tab) {
            case 'general':
                title = M2ePro.translator.translate('Edit Payment and Shipping Settings');
                break;
            case 'selling':
                title = M2ePro.translator.translate('Edit Selling Settings');
                break;
            case 'synchronization':
                title = M2ePro.translator.translate('Edit Synchronization Settings');
                break;
            default:
                title = M2ePro.translator.translate('Edit Settings');
        }

        if (productTitles) {
            title += ' ' + M2ePro.translator.translate('for') + '"' + productTitles + '"';
        }

        title += '.';

        return title;
    },

    //----------------------------------

    openPopUp: function(title, content)
    {
        var self = this;

        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            top: 50,
            maxHeight: 500,
            height: 500,
            width: 1000,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                self.selectedProductsIds = [];
                self.selectedCategoriesData = {};

                return true;
            }
        };

        try {
            Windows.getFocusedWindow() || Dialog.info(null, config);
            Windows.getFocusedWindow().setTitle(title);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}
    },

    //----------------------------------

    confirm: function()
    {
        return true;
    },

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    }

    //----------------------------------
});