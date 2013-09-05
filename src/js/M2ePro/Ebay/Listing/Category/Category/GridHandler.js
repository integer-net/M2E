EbayListingCategoryCategoryGridHandler = Class.create(GridHandler, {

    //----------------------------------

    showTaxCategory: false,

    //----------------------------------

    prepareActions: function()
    {
        this.actions = {

            editCategoriesAction: function(id) {
                if (id) {
                    this.selectByRowId(id);
                }

                this.getChooserBlockHtml();
            }.bind(this),

            editPrimaryCategoriesAction: function(id) {
                if (id) {
                    this.selectByRowId(id);
                }

                this.editPrimaryCategories();
            }.bind(this)

        };
    },

    //----------------------------------

    getChooserBlockHtml: function(categoryTitle)
    {
        categoryTitle = categoryTitle || '';

        var url = M2ePro.url.get(
            'adminhtml_ebay_listing_categorySettings/getChooserBlockHtml'
        );

        new Ajax.Request(url, {
            method: 'get',
            parameters: {
                ids: this.getSelectedProductsString()
            },
            onSuccess: function(transport) {

                var popUpTitle = M2ePro.translator.translate('Set eBay Categories') + categoryTitle;

                this.openPopUp(popUpTitle, transport.responseText);

            }.bind(this)
        });
    },

    //----------------------------------

    editPrimaryCategories: function()
    {
        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing_categorySettings/getChooserBlockHtml') ,
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                ids: this.getSelectedProductsString()
            },
            onSuccess: function (transport)
            {
                var temp = document.createElement('div');
                temp.innerHTML = transport.responseText;
                temp.innerHTML.evalScripts();

                EbayListingCategoryChooserHandlerObj.showEditPopUp(0);

                this.registerChooserCallbacks();
            }.bind(this)
        });
    },

    registerChooserCallbacks: function()
    {
        EbayListingCategoryChooserHandlerObj
            .categoriesRequiringValidation[M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::CATEGORY_TYPE_EBAY_MAIN')] = true;

        EbayListingCategoryChooserHandlerObj.doneCallback = function() {
            this.saveCategoriesData(EbayListingCategoryChooserHandlerObj.getInternalEbayMainData());
            this.unselectAll();
            this.getGridObj().doFilter();

            EbayListingCategoryChooserHandlerObj.doneCallback = null;
            EbayListingCategoryChooserHandlerObj.cancelCallback = null;

            delete EbayListingCategoryChooserHandlerObj
                .categoriesRequiringValidation[M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::CATEGORY_TYPE_EBAY_MAIN')];
        }.bind(this);

        EbayListingCategoryChooserHandlerObj.cancelCallback = function() {
            this.unselectAll();

            EbayListingCategoryChooserHandlerObj.doneCallback = null;
            EbayListingCategoryChooserHandlerObj.cancelCallback = null;

            delete EbayListingCategoryChooserHandlerObj
                .categoriesRequiringValidation[M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::CATEGORY_TYPE_EBAY_MAIN')];
        }.bind(this);
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
            top: 100,
            maxHeight: 500,
            height: 310,
            width: 700,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        if (this.showTaxCategory) {
            config.height = 385;
        }

        this.popUp = Dialog.info(content, config);

        $('modal_dialog_message').innerHTML.evalScripts();

        $('done_button').observe('click', function() {
            if (!EbayListingCategoryChooserHandlerObj.validate()) {
                return;
            }

            this.saveCategoriesData(EbayListingCategoryChooserHandlerObj.getInternalData());
        }.bind(this));

        $('cancel_button').observe('click', function() {
            this.popUp.close();
            this.unselectAll();
        }.bind(this));
    },

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    //----------------------------------

    saveCategoriesData: function(templateData)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSaveToSession'), {
            method: 'post',
            parameters: {
                ids: this.getSelectedProductsString(),
                template_data: Object.toJSON(templateData)
            },
            onSuccess: function(transport) {
                this.popUp.close();
                this.unselectAllAndReload();
            }.bind(this)
        });
    },

    //----------------------------------

    validate: function()
    {
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoModeCategoryValidate'), {
            method: 'post',
            onSuccess: function(transport) {
                var response = transport.responseText.evalJSON();

                if (response.validation == true) {
                    setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                } else {
                    MagentoMessageObj.addError(response.message);
                }

            }.bind(this)
        });
    },

    //----------------------------------

    confirm: function()
    {
        return true;
    }

    //----------------------------------
});