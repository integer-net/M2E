ProductGridHandler = Class.create();
ProductGridHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(AddListingHandlerObj)
    {
        this.addListingHandlerObj = AddListingHandlerObj;
    },

    //----------------------------------

    save_click: function(url)
    {
        var selected = this.getSelectedProducts();
        if (selected) {
            var back = '';
            var isList = '';
            if (url.indexOf('/back/list/') + 1) {
                back = 'list';
            }

            this.addListingHandlerObj.add(selected, false, back, isList);
        }
    },

    //----------------------------------

    save_and_list_click: function(url)
    {
        if (this.getSelectedProducts()) {
            var back = 'view';
            var isList = 'yes';

            this.addListingHandlerObj.add(this.getSelectedProducts(), false, back, isList);
        }
    },

    //----------------------------------

    setFilter: function(event)
    {
        if (event != undefined) {
            Event.stop(event);
        }

        var filters = $$('#'+this.containerId+' .filter input', '#'+this.containerId+' .filter select');
        var elements = [];
        for(var i in filters){
            if(filters[i].value && filters[i].value.length) elements.push(filters[i]);
        }
        if (!this.doFilterCallback || (this.doFilterCallback && this.doFilterCallback())) {
            var ruleParams = $('rule_form').serialize(true);

            var numParams = 0;
            for (var param in ruleParams) {
                numParams++;
            }

            if (numParams > 4) {
                this.reloadParams = ruleParams;
            } else {
                this.reloadParams = {rule: ""};
            }

            this.reload(this.addVarToUrl(this.filterVar, encode_base64(Form.serializeElements(elements))));
        }
    },

    resetFilter: function()
    {
        this.reloadParams = {rule: ""};
        this.reload(this.addVarToUrl(this.filterVar, ''));
    },

    advancedFilterToggle: function()
    {
        if ($('listing_product_rules').visible()) {
            $('listing_product_rules').hide();
            if ($$('#advanced_filter_button span span span').length > 0) {
                $$('#advanced_filter_button span span span')[0].innerHTML = M2ePro.text.show_advanced_filter;
            } else {
                $$('#advanced_filter_button span')[0].innerHTML = M2ePro.text.show_advanced_filter;
            }
        } else {
            $('listing_product_rules').show();
            if ($$('#advanced_filter_button span span span').length > 0) {
                $$('#advanced_filter_button span span span')[0].innerHTML = M2ePro.text.hide_advanced_filter;
            } else {
                $$('#advanced_filter_button span')[0].innerHTML = M2ePro.text.hide_advanced_filter;
            }
        }
    },

    //----------------------------------

    setGridId:  function(id)
    {
        this.gridId = id;
    },

    getGridId:  function()
    {
        return this.gridId;
    },

    //----------------------------------

    getSelectedProducts: function()
    {
        var selectedProducts = window[this.getGridId() + '_massactionJsObject'].checkedString;
        if (window.location.href.indexOf('/step/') + 1 && !selectedProducts) {
            var isEmpty = confirm(M2ePro.text.create_empty_listing_message);

            if (isEmpty) {
                return true;
            }

            return false;
        }

        if (!selectedProducts) {
            alert(M2ePro.text.select_items_message);
            return false;
        }
        return selectedProducts;
    }

    //----------------------------------
});