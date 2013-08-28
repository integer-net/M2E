ListingMoveToListingHandler = Class.create();
ListingMoveToListingHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro)
    {
        this.M2ePro = M2ePro;
    },

    //----------------------------------

    openPopUp: function(gridHtml,popup_title)
    {
        popUp = Dialog.info('', {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: popup_title,
            top: 100,
            width: 900,
            height: 500,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        $('modal_dialog_message').style.paddingTop = '20px';
        $('modal_dialog_message').insert(gridHtml);
    },

    //----------------------------------

    getGridHtml: function(selectedProducts)
    {
        this.selectedProducts = selectedProducts;

        var self = this;
        MagentoMessageObj.clearAll();
        eval(self.M2ePro.customData.gridId + '_massactionJsObject.unselectAll()');

        var callback = function(response) {
            new Ajax.Request(self.M2ePro.url.getGridHtml, {
                method: 'get',
                parameters: {
                    componentMode: self.M2ePro.customData.componentMode,
                    accountId: response.accountId,
                    marketplaceId: response.marketplaceId,
                    attrSetId: Object.toJSON(response.attrSetId),
                    ignoreListings: self.M2ePro.customData.ignoreListings
                },
                onSuccess: function (transport) {
                    self.openPopUp(transport.responseText,self.M2ePro.text.popup_title);
                }
            });
        };

        new Ajax.Request(self.M2ePro.url.prepareData, {
            method: 'post',
            parameters: {
                componentMode: self.M2ePro.customData.componentMode,
                selectedProducts: Object.toJSON(self.selectedProducts)
            },
            onSuccess: function (transport) {
                if (transport.responseText == 1) {
                    alert(self.M2ePro.text.select_only_mapped_products);
                } else if (transport.responseText == 2) {
                    alert(self.M2ePro.text.select_the_same_type_products);
                } else {
                    var response = transport.responseText.evalJSON();

                    if (response.offerListingCreation) {
                        return self.offerListingCreation(
                            response.accountId,
                            response.marketplaceId,
                            function() {
                                callback.call(self,response);
                            }
                        );
                    }

                    callback.call(self,response);
                }
            }
        });
    },

    //----------------------------------

    tryToSubmit: function(listingId)
    {
        new Ajax.Request(this.M2ePro.url.tryToMoveToListing, {
            method: 'post',
            parameters: {
                componentMode: this.M2ePro.customData.componentMode,
                selectedProducts: Object.toJSON(this.selectedProducts),
                listingId: listingId
            },
            onSuccess: (function(transport) {

                var response = transport.responseText.evalJSON();

                if (response.result == 'success') {
                    return this.submit(listingId);
                }

                new Ajax.Request(this.M2ePro.url.getFailedProductsGridHtml, {
                    method: 'get',
                    parameters: {
                        component: this.M2ePro.customData.componentMode,
                        failed_products: Object.toJSON(response.failed_products)
                    },
                    onSuccess: (function(transport) {

                        popUp.close();
                        this.openPopUp(transport.responseText,this.M2ePro.text.failed_products_popup_title);

                        $('modal_dialog_message').down('div[class=grid]').setStyle({
                            maxHeight: '300px',
                            overflow: 'auto'
                        });

                        $('failedProducts_back_button').observe('click',(function() {
                            popUp.close();
                            this.getGridHtml(this.selectedProducts);
                        }).bind(this));

                        $('failedProducts_continue_button').observe('click',(function() {
                            this.submit(listingId);
                        }).bind(this));

                    }).bind(this)
                });

            }).bind(this)
        });
    },

    //----------------------------------

    submit: function(listingId)
    {
        var self = this;
        new Ajax.Request(self.M2ePro.url.moveToListing, {
            method: 'post',
            parameters: {
                componentMode: self.M2ePro.customData.componentMode,
                selectedProducts: Object.toJSON(self.selectedProducts),
                listingId: listingId
            },
            onSuccess: function (transport) {
                popUp.close();
                self.scroll_page_to_top();

                var response = transport.responseText.evalJSON();

                if (response.result == 'success') {
                    eval(self.M2ePro.customData.gridId + 'JsObject.reload()');
                    MagentoMessageObj.addSuccess(self.M2ePro.text.successfully_moved);
                    return;
                }

                var message = '';
                if (response.errors == self.selectedProducts.length) { // all items failed
                    message = self.M2ePro.text.products_were_not_moved;
                } else {
                    message = self.M2ePro.text.some_products_were_not_moved;
                    eval(self.M2ePro.customData.gridId + 'JsObject.reload()');
                }

                MagentoMessageObj.addError(str_replace('%s', self.M2ePro.url.logViewUrl, message));
            }
        });
    },

    //----------------------------------

    offerListingCreation: function(accountId,marketplaceId,callback) {

        if (!confirm(this.M2ePro.text.create_listing)) {
            return callback.call(this);
        }

        new Ajax.Request(this.M2ePro.url.createDefaultListing, {
            method: 'post',
            parameters: {
                componentMode: this.M2ePro.customData.componentMode,
                accountId: accountId,
                marketplaceId: marketplaceId
            },
            onSuccess: (function (transport) {
                callback.call(this);
            }).bind(this)
        });
    }

    //----------------------------------

});