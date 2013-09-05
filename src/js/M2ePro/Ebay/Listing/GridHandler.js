EbayListingGridHandler = Class.create(ListingGridHandler, {

    selectedProductsIds: [],
    selectedCategoriesData: {},

    //----------------------------------

    afterInitPage: function($super)
    {
        $super();

        $(this.gridId+'_massaction-select').observe('change', function() {
            if (!$('get-estimated-fee')) {
                return;
            }

            if (this.value == 'list') {
                $('get-estimated-fee').show();
            } else {
                $('get-estimated-fee').hide();
            }
        });
    },

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    //----------------------------------

    getMaxProductsInPart: function()
    {
        var maxProductsInPart = 10;
        var selectedProductsArray = this.getSelectedProductsArray();

        if (selectedProductsArray.length <= 25) {
            maxProductsInPart = 5;
        }
        if (selectedProductsArray.length <= 15) {
            maxProductsInPart = 3;
        }
        if (selectedProductsArray.length <= 8) {
            maxProductsInPart = 2;
        }
        if (selectedProductsArray.length <= 4) {
            maxProductsInPart = 1;
        }

        return maxProductsInPart;
    },

    //----------------------------------

    prepareActions: function($super)
    {
        $super();
        this.movingHandler = new ListingMovingHandler(this);

        this.actions = Object.extend(this.actions,{

            editCategorySettingsAction: this.editCategorySettings.bind(this),
            movingAction: this.movingHandler.run.bind(this.movingHandler)

        });

    },

    //----------------------------------

    getLogViewUrl: function(rowId)
    {
        var temp = this.getProductIdByRowId(rowId);

        var regExpImg= new RegExp('<img[^><]*>','gi');
        var regExpHr= new RegExp('<hr>','gi');

        temp = temp.replace(regExpImg,'');
        temp = temp.replace(regExpHr,'');

        var productId = strip_tags(temp).trim();

        return M2ePro.url.get('adminhtml_ebay_log/listing',{
            filter: base64_encode('product_id[from]='+productId+'&product_id[to]='+productId)
        });
    },

    //----------------------------------

    editCategorySettings: function()
    {
        this.selectedProductsIds = this.getSelectedProductsArray();

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

                this.openCategoryTemplatePopUp(title, transport.responseText);

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

                this.openCategoryTemplatePopUp(title, transport.responseText);

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

    openCategoryTemplatePopUp: function(title, content)
    {
        var self = this;

        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
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

    openFeePopUp: function(content)
    {
        Dialog.info(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Estimated Fee Details'),
            width: 400,
            zIndex: 100,
            recenterAuto: true
        });

        Windows.getFocusedWindow().content.style.height = '';
        Windows.getFocusedWindow().content.style.maxHeight = '550px';
    },

    getEstimatedFees: function(listingProductId)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getEstimatedFees'),
        {
            method: 'get',
            asynchronous : true,
            parameters : {
                listing_product_id: listingProductId
            },
            onSuccess: function (transport)
            {
                var response = transport.responseText.evalJSON();

                if (response.error) {
                    alert('Unable to receive estimated fee.');
                    return;
                }

                ListingGridHandlerObj.openFeePopUp(response.html);
            }
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

        if (action == 'editCategorySettings') {
            return true;
        }

        return $super();
    }

    //----------------------------------

});