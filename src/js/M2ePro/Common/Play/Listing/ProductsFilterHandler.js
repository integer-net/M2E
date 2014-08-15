PlayListingProductsFilterHandler = Class.create();
PlayListingProductsFilterHandler.prototype = Object.extend(new CommonHandler(), {

    templateSellingFormatId: null,
    marketplaceId: null,

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    store_id_change: function()
    {
        PlayListingProductsFilterHandlerObj.checkMessages();
    },

    //----------------------------------

    checkMessages: function()
    {
        if (PlayListingProductsFilterHandlerObj.templateSellingFormatId === null || PlayListingProductsFilterHandlerObj.marketplaceId === null) {
            return;
        }

        var id = PlayListingProductsFilterHandlerObj.templateSellingFormatId,
            nick = 'selling_format',
            storeId = $('store_id').value,
            marketplaceId = PlayListingProductsFilterHandlerObj.marketplaceId,
            checkAttributesAvailability = false,
            container = 'store_messages',
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages();
                    }.bind(this))
                }
            }.bind(this);

        TemplateHandlerObj
            .checkMessages(
                id,
                nick,
                '',
                storeId,
                marketplaceId,
                checkAttributesAvailability,
                container,
                callback
            );
    }

    //----------------------------------
});