EbayListingEbayGridHandler = Class.create(EbayListingViewGridHandler, {

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
        var self = this;

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

                self.openFeePopUp(response.html);
            }
        });
    },

    //----------------------------------

    showMotorsNotificationPopUp: function(message)
    {
        var content = '<div style="padding: 10px 0;">'+message+'</div>' +
                      '<div style="float: right;">' +
                        '<button onclick="Windows.getFocusedWindow().close()">' +
                          '<span>OK</span>' +
                        '</button>' +
                      '</div> '
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Compatibility Attribute'),
            top: 250,
            height: 85,
            width: 420,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        Dialog.info(content, config);
    }

    //----------------------------------

});