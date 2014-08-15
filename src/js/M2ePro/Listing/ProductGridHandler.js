ProductGridHandler = Class.create();
ProductGridHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(AddListingHandlerObj)
    {
        this.addListingHandlerObj = AddListingHandlerObj || null;
    },

    //----------------------------------

    save_click: function(back)
    {
        var selected = this.getSelectedProducts();
        if (selected) {
            this.addListingHandlerObj.add(selected, false, back, '');
        }
    },

    //----------------------------------

    save_and_list_click: function(back)
    {
        if (this.getSelectedProducts()) {
            this.addListingHandlerObj.add(this.getSelectedProducts(), false, back, 'yes');
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

            for (var reloadParam in this.reloadParams) {
                reloadParam.match('^rule|^hide') && delete this.reloadParams[reloadParam];
            }

            if (numParams > 5) {
                this.reloadParams = Object.extend(this.reloadParams, ruleParams);
            } else {

                if (ruleParams['hide_products_others_listings'] == 0) {
                    this.reloadParams.hide_products_others_listings = 0;
                }

                this.reloadParams.rule = "";
            }

            this.reload(this.addVarToUrl(this.filterVar, encode_base64(Form.serializeElements(elements))));
        }
    },

    resetFilter: function()
    {
        for (var reloadParam in this.reloadParams) {
            reloadParam.match('^rule|^hide') && delete this.reloadParams[reloadParam];
        }
        this.reloadParams.rule = "";

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