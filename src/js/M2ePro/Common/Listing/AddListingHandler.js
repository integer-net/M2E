AddListingHandler = Class.create();
AddListingHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro, ProgressBarObj, WrapperObj)
    {
        this.M2ePro = M2ePro;
        this.categories = '';
        this.products = '';
        this.listing_id = null;
        this.is_list = null;
        this.back = '';
        this.emptyListing = 0;

        this.categoriesAddAction = null;
        this.categoriesDeleteAction = null;

        this.progressBarObj = ProgressBarObj;
        this.wrapperObj = WrapperObj;
    },

    //----------------------------------

    add: function(items, categoriesMode, back, isList, categoriesSave)
    {
        var self = this;
        self.is_list = isList;
        self.back = back;

        self.getListingId(items, categoriesMode);

        if (self.emptyListing == 1) {
            return;
        }

        if (categoriesMode == true) {
            self.categories = items;
        } else {
            self.products = items;
        }

        self.getProductsFromCategories(categoriesSave);

        var parts = self.makeProductsParts();

        self.progressBarObj.reset();
        self.progressBarObj.setTitle('Adding products to listing');
        self.progressBarObj.setStatus('Adding in process. Please wait...');
        self.progressBarObj.show();
        self.scroll_page_to_top();

        $('loading-mask').setStyle({visibility: 'hidden'});
        self.wrapperObj.lock();

        self.sendPartsProducts(parts, parts.length);
    },

    setCategoriesActions: function(addAction, deleteAction)
    {
        this.categoriesAddAction = addAction;
        this.categoriesDeleteAction = deleteAction;
    },

    createListing: function(items, categoriesMode)
    {
        var self = this;
        var categoriesString = '';

        if (categoriesMode == true) {
            categoriesString = items;
        }

        if (items === true) {
            self.emptyListing = 1;
        }

        new Ajax.Request(self.M2ePro.url.create_listing, {
            method: 'post',
            asynchronous: false,
            parameters: {
                empty_listing: self.emptyListing,
                back: self.back,
                categories: categoriesString,
                categories_add_action: self.categoriesAddAction,
                categories_delete_action: self.categoriesDeleteAction
            },
            onSuccess: function(transport) {
                if (self.emptyListing == 1) {
                    setLocation(transport.responseText);
                } else {
                    self.listing_id = transport.responseText;
                }
            }
        });
    },

    getListingId: function(items, categoriesMode)
    {
        var self = this;

        if (window.location.href.indexOf('/add/step/') + 1) {
            self.createListing(items, categoriesMode);
        } else {
            var hrefParts = explode('/', window.location.href);

            for (var i = 0; i < hrefParts.length; i++) {
                if (hrefParts[i] == 'id') {
                    self.listing_id = hrefParts[i+1];
                    break;
                }
            }
        }
    },

    getProductsFromCategories: function(categoriesSave)
    {
        var self = this;

        if (self.categories == '') {
            return;
        }

        new Ajax.Request(self.M2ePro.url.get_products_from_categories, {
            method: 'post',
            asynchronous: false,
            parameters: {
                listing_id: self.listing_id,
                categories: self.categories,
                categories_save: categoriesSave,
                hide_products_others_listings: +self.hideProductsOthersListings
            },
            onComplete: function(transport) {
                self.products = transport.responseText;
            }
        });
    },

    makeProductsParts: function()
    {
        var self = this;

        var productsInPart = 50;
        var productsArray = explode(',', self.products);
        var parts = new Array();

        if (productsArray.length < productsInPart) {
            return parts[0] = productsArray;
        }

        var result = new Array();
        for (var i = 0; i < productsArray.length; i++) {
            if (result.length == 0 || result[result.length-1].length == productsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = productsArray[i];
        }

        return result;
    },

    sendPartsProducts: function(parts, partsCount)
    {
        var self = this;

        if (parts.length == 0) {
            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var isLastPart = '';
        if (parts.length <= 0) {
            isLastPart = 'yes';
        }

        new Ajax.Request(self.M2ePro.url.add_products, {
            method: 'post',
            parameters: {
                listing_id: self.listing_id,
                is_last_part: isLastPart,
                do_list: self.is_list,
                back: self.back,
                products: partString
            },
            onSuccess: function(transport) {
                var percents = (100/partsCount)*(partsCount-parts.length);

                if (percents <= 0) {
                    self.progressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    self.progressBarObj.setPercents(100,0);
                    self.progressBarObj.setStatus('Adding has been completed.');

                    return setLocation(transport.responseText.evalJSON()['redirect']);
                } else {
                    self.progressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsProducts(parts, partsCount);
                }, 500);
            }
        });
    },

    //----------------------------------

    setHideProductsPresentedInOtherListings: function(hideProductsOthersListings)
    {
        this.hideProductsOthersListings = hideProductsOthersListings;
    }

});