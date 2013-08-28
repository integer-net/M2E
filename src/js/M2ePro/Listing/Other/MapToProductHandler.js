ListingOtherMapToProductHandler = Class.create();
ListingOtherMapToProductHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro)
    {
        this.M2ePro = M2ePro;
    },

    //----------------------------------

    openPopUp: function(productTitle, otherProductId)
    {
        var self = this;
        eval(self.M2ePro.customData.componentMode + 'ListingOtherGrid_massactionJsObject.unselectAll()');

        popUp = Dialog.info('', {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: self.M2ePro.text.mapping_product_title + ' "' + productTitle + '"',
            top: 100,
            width: 750,
            height: 500,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        $('other_product_id').value = otherProductId;
        $('modal_dialog_message').insert($('pop_up_content').innerHTML);

        $('mapToProduct_submit_button').observe('click',function(event){
            self.map();
        });

        $('product_id').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.map();
        });

        $('sku').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.map();
        });
    },

    //----------------------------------

    map: function()
    {
        var self = this;
        var productId = $('product_id').value;
        var sku = $('sku').value;
        var otherProductId = $('other_product_id').value;

        MagentoMessageObj.clearAll();

        if (otherProductId == '' || (/^\s*(\d)*\s*$/i).test(otherProductId) == false){
            return;
        }

        if ((sku == '' && productId == '')) {
            $('product_id').focus();
            alert(self.M2ePro.text.enter_product_or_sku);
            return;
        }
        if (((/^\s*(\d)*\s*$/i).test(productId) == false)) {
            alert(self.M2ePro.text.invalid_data);
            $('product_id').focus();
            $('product_id').value = '';
            $('sku').value = '';
            return;
        }

        if (!confirm(self.M2ePro.text.confirm)) {
            return;
        }

        $('help_grid').hide();

        new Ajax.Request(self.M2ePro.url.mapToProduct, {
            method: 'post',
            parameters: {
                productId : productId,
                sku : sku,
                otherProductId : otherProductId
            },
            onSuccess: function (transport) {
                if (transport.responseText == 0) {
                    eval(self.M2ePro.customData.componentMode + 'ListingOtherGridJsObject.reload();');
                    popUp.close();
                    self.scroll_page_to_top();
                    MagentoMessageObj.addSuccess(self.M2ePro.text.successfully_mapped);
                } else if (transport.responseText == 1) {
                    alert(self.M2ePro.text.product_does_not_exist);
                } else if (transport.responseText == 2) {
                    alert(self.M2ePro.text.select_simple_product);
                } else if (transport.responseText == 3) {
                    popUp.close();
                    self.scroll_page_to_top();
                    MagentoMessageObj.addError(str_replace('%s', productId, self.M2ePro.text.select_without_options));
                }
            }
        });
    }

    //----------------------------------
});