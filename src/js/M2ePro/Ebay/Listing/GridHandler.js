EbayListingGridHandler = Class.create(GridHandler, {

    backParam: base64_encode('*/adminhtml_ebay_listing/index'),

    //----------------------------------

    prepareActions: function()
    {
        return false;
    },

    //----------------------------------

    manageProductsAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing/view', {id: id, back: this.backParam}));
    },

    //----------------------------------

    addProductsSourceProductsAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_productAdd/index', {
            listing_id: id,
            source: 'products',
            clear: true,
            back: this.backParam
        }));
    },

    //----------------------------------

    addProductsSourceCategoriesAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_productAdd/index', {
            listing_id: id,
            source: 'categories',
            clear: true,
            back: this.backParam
        }));
    },

    //----------------------------------

    autoActionsAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing/view', {id: id, auto_actions: 1}));
    },

    //----------------------------------

    viewLogsAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_log/listing', {id: id}));
    },

    //----------------------------------

    deleteAction: function(id)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        setLocation(M2ePro.url.get('adminhtml_ebay_listing/delete', {id: id}));
    },

    //----------------------------------

    editSettingsAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_template/editListing', {id: id, back: this.backParam}));
    },

    //----------------------------------

    editPaymentAndShippingAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_template/editListing', {
            id: id,
            tab: 'general',
            back: this.backParam
        }));
    },

    //----------------------------------

    editSellingAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_template/editListing', {
            id: id,
            tab: 'selling',
            back: this.backParam
        }));
    },

    //----------------------------------

    editSynchronizationAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_template/editListing', {
            id: id,
            tab: 'synchronization',
            back: this.backParam
        }));
    },

    //----------------------------------

    editTitleAction: function(id)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Edit Listing Title'),
            top: 250,
            maxHeight: 500,
            height: 90,
            width: 460,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        var currentTitle = $('listing_title_' + id).innerHTML;
        var popUpHtml = '<div style="margin: 10px">' +
                        '<input id="listing_title_input" ' +
                                'style="width: 410px;" ' +
                                'type="text" ' +
                                'value="'+currentTitle+'" />' +
                        '<div style="display: none;" id="listing_title_validation_message" class="validation-advice">' +
                            M2ePro.translator.translate('This is a required field.') +
                        '</div> ' +
                        '</div>' +
                        '<div style="float: right; margin-top: 10px;">' +
                        '<a onclick="Windows.getFocusedWindow().close();" ' +
                            'href="javascript:void(0)">'+M2ePro.translator.translate('Cancel')+
                        '</a>&nbsp;&nbsp;&nbsp;' +
                        '<button onclick="EbayListingGridHandlerObj.saveListingTitle('+id+');">'+M2ePro.translator.translate('Save')+'</button>' +
                        '</div>'
            ;

        Dialog.info(popUpHtml, config);
    },

    //----------------------------------

    saveListingTitle: function(listingId)
    {
        $('listing_title_validation_message').hide();

        var newTitle = $('listing_title_input').value;
        if (newTitle.length <= 0) {
            $('listing_title_validation_message').show();
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/saveTitle'),
        {
            parameters : {
                listing_id: listingId,
                title: newTitle
            },
            onSuccess: (function (transport)
            {
                Windows.getFocusedWindow().close();
                this.getGridObj().reload();
            }).bind(this)
        });
    }

    //----------------------------------

});