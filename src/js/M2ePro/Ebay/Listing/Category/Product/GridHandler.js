EbayListingCategoryProductGridHandler = Class.create(GridHandler, {

    productIdCellIndex: 1,
    productTitleCellIndex: 2,

    selectedProducts: [],
    showTaxCategory: false,

    //----------------------------------

    prepareActions: function()
    {
        this.actions = {

            getSuggestedCategoriesAction: function(id) {
                this.getSuggestedCategories(id);
            }.bind(this),
            editPrimaryCategoriesAction: function(id) {
                this.editPrimaryCategories(id);
            }.bind(this),
            editCategoriesAction: function(id) {
                this.editCategories(id);
            }.bind(this),
            resetCategoriesAction: function(id) {
                this.resetCategories(id);
            }.bind(this),
            removeItemAction: function(id) {
                var ids = id ? [id] : this.getSelectedProductsArray();
                this.removeItems(ids);
            }.bind(this)

        };
    },

    //----------------------------------

    getSuggestedCategories: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        if (id && !confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        EbayListingCategoryProductSuggestedSearchHandlerObj.search(
            this.selectedProductsIds.join(','), function(searchResult) {
                this.unselectAll();
                this.getGridObj().doFilter();
                this.selectedProductsIds = [];

                MagentoMessageObj.clearAll();

                if (searchResult.failed > 0) {
                    MagentoMessageObj.addError(
                        M2ePro.translator.translate('eBay could not assign categories for {X} products.')
                            .replace('%s', searchResult.failed)
                    );
                } else if (searchResult.succeeded > 0) {
                    MagentoMessageObj.addSuccess(
                        M2ePro.translator.translate('Suggested Categories were successfully received for %s product(s).')
                            .replace('%s', searchResult.succeeded)
                    );
                }

            }.bind(this)
        );
    },

    getSuggestedCategoriesForAll: function()
    {
        var gridIds = this.getGridMassActionObj().getGridIds().split(',')
        if (gridIds.length > 100 && !confirm('Are you sure?')) {
            return;
        }

        this.getGridMassActionObj().selectAll();
        this.getSuggestedCategories();
    },

    //----------------------------------

    editPrimaryCategories: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_categorySettings/getChooserBlockHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function (transport)
            {
                var temp = document.createElement('div');
                temp.innerHTML = transport.responseText;
                temp.innerHTML.evalScripts();

                EbayListingCategoryChooserHandlerObj.showEditPopUp(0);

                EbayListingCategoryChooserHandlerObj.doneCallback = function() {
                    this.doneCategories(EbayListingCategoryChooserHandlerObj.getInternalEbayMainData());
                    this.getGridObj().doFilter();
                    this.unselectAll();

                    EbayListingCategoryChooserHandlerObj.doneCallback = null;
                    EbayListingCategoryChooserHandlerObj.cancelCallback = null;
                }.bind(this);

                EbayListingCategoryChooserHandlerObj.cancelCallback = function() {
                    EbayListingCategoryChooserHandlerObj.doneCallback = null;
                    EbayListingCategoryChooserHandlerObj.cancelCallback = null;

                    this.unselectAll();
                }.bind(this);
            }.bind(this)
        });
    },

    //----------------------------------

    editCategories: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_categorySettings/getChooserBlockHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function (transport)
            {
                var title = M2ePro.translator.translate('Set eBay Category for Product(s)');

                if (this.selectedProductsIds.length == 1) {
                    var productName = this.getProductNameByRowId(this.selectedProductsIds[0]);
                    title += '&nbsp;"' + productName + '"';
                }

                this.showChooserPopup(title, transport.responseText);
            }.bind(this)
        });
    },

    //----------------------------------

    resetCategories: function(id)
    {
        if (id && !confirm('Are you sure?')) {
            return;
        }

        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSuggestedReset') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function (transport)
            {
                this.getGridObj().doFilter();
                this.unselectAll();
            }.bind(this)
        });
    },

    //----------------------------------

    doneCategories: function(templateData)
    {
        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSaveToSession') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : {
                ids: this.selectedProductsIds.join(','),
                template_data: JSON.stringify(templateData)
            },
            onSuccess: function (transport)
            {
                this.unselectAll();
                this.getGridObj().doFilter();

                Windows.getFocusedWindow().close();
            }.bind(this)
        });
    },

    showChooserPopup: function(title, content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 100,
            maxHeight: 500,
            height: 350,
            width: 700,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                this.selectedProductsIds = [];

                return true;
            }
        };

        if (this.showTaxCategory) {
            config.height = 425;
        }

        Dialog.info(content, config);

        $('cancel_button').observe('click', function () {
            Windows.getFocusedWindow().close();
            this.unselectAll();
        }.bind(this));

        $('done_button').observe('click', function () {
            EbayListingCategoryProductGridHandlerObj.doneCategories(EbayListingCategoryChooserHandlerObj.getInternalData());
        });

        $('modal_dialog_message').innerHTML.evalScripts();
    },

    //----------------------------------

    nextStep: function()
    {
        MagentoMessageObj.clearAll();

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoModeProductValidate') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {},
            onSuccess: function (transport)
            {
                var response = transport.responseText.evalJSON();

                if (response['validation']) {
                    return setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                }

                if (response['message']) {
                    return MagentoMessageObj.addError(response['message']);
                }

                this.nextStepWarningPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Set eBay Category'),
                    width: 430,
                    height: 200,
                    zIndex: 100,
                    recenterAuto: false,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                this.nextStepWarningPopup.options.destroyOnClose = false;
                $('modal_dialog_message').insert($('next_step_warning_popup_content').show());

                $('total_count').update(response['total_count']);
                $('failed_count').update(response['failed_count']);

            }.bind(this)
        });
    },

    //----------------------------------

    removeItems: function(ids)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings/deleteModeProduct');
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                ids: ids.join(',')
            },
            onSuccess: function()
            {
                this.unselectAllAndReload();
            }.bind(this)
        });
    },

    //----------------------------------

    confirm: function($super)
    {
        var action = '';

        $$('select#'+this.gridId+'_massaction-select option').each(function(o) {
            if (o.selected && o.value != '') {
                action = o.value;
            }
        });

        if (action == 'removeItem' ||
            action == 'editCategories' ||
            action == 'editPrimaryCategories') {
            return true;
        }

        return $super();
    },

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    }

    //----------------------------------
});