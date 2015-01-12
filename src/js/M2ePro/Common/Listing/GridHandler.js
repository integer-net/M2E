CommonListingGridHandler = Class.create(ListingGridHandler, {

    //----------------------------------

    getLogViewUrl: function(rowId)
    {
        var temp = this.getProductIdByRowId(rowId);

        var regExpImg= new RegExp('<img[^><]*>','gi');
        var regExpHr= new RegExp('<hr>','gi');

        temp = temp.replace(regExpImg,'');
        temp = temp.replace(regExpHr,'');

        var productId = strip_tags(temp);

        return M2ePro.url.get('adminhtml_common_log/listing',{
            filter: base64_encode('product_id[from]='+productId+'&product_id[to]='+productId)
        });
    },

    //----------------------------------

    prepareActions: function($super)
    {
        $super();

        this.actions = Object.extend(this.actions,{
            duplicateAction: this.duplicateProducts.bind(this)
        });

    },

    //----------------------------------

    duplicateProducts: function()
    {
        this.scroll_page_to_top();
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_common_listing/duplicateProducts'), {
            method: 'post',
            parameters: {
                component: this.getComponent(),
                ids: this.getSelectedProductsString()
            },
            onSuccess: (function(transport) {
                try {
                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type != 'error') {
                        this.unselectAllAndReload();
                    }

                } catch (e) {
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    }

    //----------------------------------

});