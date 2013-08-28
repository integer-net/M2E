EbayListingOtherSynchronizationHandler = Class.create();
EbayListingOtherSynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

//----------------------------------

    initialize: function()
    {
    },

    save_click: function(redirectUrl)
    {
        var url = M2ePro.url.formSubmit + 'back/'+redirectUrl+'/';
        this.submitForm(url);
    },

    source_change: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;
        var id = this.id;
        var attributeContainerId = id + '_attribute_container';

        eval('var constAttribute = self.' + id.toUpperCase() + '_SOURCE_ATTRIBUTE');
        eval('var constNone = self.' + id.toUpperCase() + '_SOURCE_NONE');

        if (this.value == constAttribute) {
            $(attributeContainerId).show();
        } else {
            $(attributeContainerId).hide();
        }

        if (this.value == constNone) {
            eval('var constReviseNone = self.REVISE_UPDATE_' + id.toUpperCase() + '_NONE');

            $('revise_update_' + id).selectedIndex = constReviseNone;
            $('revise_update_' + id).disabled = true;

            if (id == 'qty') {
                $('relist_qty').selectedIndex = self.RELIST_QTY_NONE;
                $('relist_qty').simulate('change');
                $('relist_qty').disabled = true;

                $('stop_qty').selectedIndex = self.STOP_QTY_NONE;
                $('stop_qty').simulate('change');
                $('stop_qty').disabled = true;
            }
        } else {
            $('revise_update_' + id).disabled = false;

            if (id == 'qty') {
                $('relist_qty').disabled = false;
                $('stop_qty').disabled = false;
            }
        }

        if (id == 'price') {
            self.price_mode_change();
        }
    },

    price_mode_change: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        if ($('price').value == self.PRICE_SOURCE_FINAL) {
            $('customer_group_id_container').show();
            $('note_price').innerHTML = M2ePro.text.final_price_note;
        } else {
            $('customer_group_id').value = '';
            $('customer_group_id_container').hide();
            $('note_price').innerHTML = M2ePro.text.all_price_note;
        }
    },

    relist_mode_change: function()
    {
        if (this.value == 1) {
            $$('.relist-options').each(function(elem) {
                elem.show();
            });
        } else {
            $$('.relist-options').each(function(elem) {
                elem.hide();
            });
        }
    },

    relist_qty_change: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        $('relist_qty_value_container').hide();
        $('relist_qty_item').hide();
        $('relist_qty_value_max_container').hide();
        $('relist_qty_item_min').hide();

        if (this.value == self.RELIST_QTY_MORE) {
            $('relist_qty_value_container').show();
            $('relist_qty_item').show();
        }

        if (this.value == self.RELIST_QTY_BETWEEN) {
            $('relist_qty_value_max_container').show();
            $('relist_qty_item_min').show();
            $('relist_qty_value_container').show();
        }
    },

    stop_qty_change: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        $('stop_qty_value_container').hide();
        $('stop_qty_item').hide();
        $('stop_qty_value_max_container').hide();
        $('stop_qty_item_min').hide();

        if (this.value == self.RELIST_QTY_LESS) {
            $('stop_qty_value_container').show();
            $('stop_qty_item').show();
        }

        if (this.value == self.RELIST_QTY_BETWEEN) {
            $('stop_qty_value_max_container').show();
            $('stop_qty_item_min').show();
            $('stop_qty_value_container').show();
        }
    },

    completeStep : function()
    {
        new Ajax.Request( M2ePro.url.formSubmit + '?' + $('edit_form').serialize() ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                window.opener.completeStep = 1;
                window.close();
            }
        });
    }

});