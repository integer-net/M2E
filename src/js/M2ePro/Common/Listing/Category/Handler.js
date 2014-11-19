ListingCategoryHandler = Class.create();
ListingCategoryHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(AddListingHandlerObj)
    {
        this.addListingHandlerObj = AddListingHandlerObj;
    },

    //----------------------------------

    save_click: function(action)
    {
        array_unique(categories_selected_items);

        if (categories_selected_items.length <= 0) {
            alert(M2ePro.text.select_items_message);
            return;
        }

        var selectedCategories = implode(',',categories_selected_items);

        if (action.indexOf('/add_products/yes/') + 1) {
            if (action.indexOf('/next/yes/') + 1) {
                var url = action+'selected_categories/'+selectedCategories+'/';
                url += 'save_categories/' + Number($('save_categories').checked) + '/';
                setLocation(url);

                return;
            }
            this.addListingHandlerObj.setHideProductsPresentedInOtherListings(+($('hide_products_others_listings').value));
            this.addListingHandlerObj.add(selectedCategories, true, '', 0, Number($('save_categories').checked));
            return;
        }

        if (action.indexOf('/remember_categories/yes/') + 1) {
            var url = action+'selected_categories/'+selectedCategories+'/';
            url += 'categories_add_action/' + $('categories_add_action').value + '/';
            url += 'categories_delete_action/' + $('categories_delete_action').value + '/';
            setLocation(url);
            return;
        }

        var back = '';
        if (action.indexOf('/back/list/') + 1) {
            back = 'list';
        }

        this.addListingHandlerObj.setHideProductsPresentedInOtherListings(+($('hide_products_others_listings').value));
        this.addListingHandlerObj.setCategoriesActions($('categories_add_action').value, $('categories_delete_action').value);
        this.addListingHandlerObj.add(selectedCategories, true, back);
    },

    save_and_list_click: function(url)
    {
        array_unique(categories_selected_items);

        if (categories_selected_items.length <= 0) {
            alert(M2ePro.text.select_items_message);
            return;
        }

        var selectedCategories = implode(',',categories_selected_items);

        var back = 'view';
        var isList = 'yes';

        this.addListingHandlerObj.add(selectedCategories, true, back, isList, Number($('save_categories').checked));
    },

    //----------------------------------

    categories_products_from_change: function()
    {
        var value = $('categories_products_from').value;

        if (value == 'manual') {
            $('hide_products_others_listings').up('tr').hide();
            $$('.save_and_next_button').each(function(o) { o.show(); });
            $$('.save_and_list_button').each(function(o) { o.hide(); });
            $$('.save_and_go_to_listings_view_button').each(function(o) { o.hide(); });
            $$('.save_and_go_to_listings_list_button').each(function(o) { o.hide(); });
            $$('.save_and_go_to_listing_view_button').each(function(o) { o.hide(); });
        } else {
            $('hide_products_others_listings').up('tr').show();
            $$('.save_and_next_button').each(function(o) { o.hide(); });
            $$('.save_and_list_button').each(function(o) { o.show(); });
            $$('.save_and_go_to_listings_view_button').each(function(o) { o.show(); });
            $$('.save_and_go_to_listings_list_button').each(function(o) { o.show(); });
            $$('.save_and_go_to_listing_view_button').each(function(o) { o.show(); });
        }
    }

    //----------------------------------
});
