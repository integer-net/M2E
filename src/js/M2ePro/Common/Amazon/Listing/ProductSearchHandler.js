AmazonListingProductSearchHandler = Class.create(ActionHandler,{

    //----------------------------------

    initialize: function($super,gridHandler)
    {
        var self = this;

        $super(gridHandler);

        $('productSearchMenu_cancel_button').observe('click', function(){
            popUp.close();
        });

        $('productSearch_cancel_button').observe('click',function(){
            popUp.close();
        });

        $('productSearch_submit_button').observe('click',function(event){
            self.searchGeneralIdManual(self.params.productId);
        });

        $('productSearch_reset_button').observe('click',function(event){
            $('query').value = '';
            $('productSearch_grid').hide();
        });

        $('productSearch_back_button').observe('click',function(event){
            popUp.close();
            self.openPopUp(0, self.params.title, self.params.productId);
        });

        $('query').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.searchGeneralIdManual(self.params.productId);
        });
    },

    //----------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options,options);
        return this;
    },

    //----------------------------------

    params: {autoMapErrorFlag : false},

    //----------------------------------

    openPopUp: function(mode, title, productId, errorMsg)
    {
        MagentoMessageObj.clearAll();

        var self = this;

        this.gridHandler.unselectAll();

        this.params = {
            mode: mode,
            title: title,
            productId: productId,
            size_menu: {
                width: 500,
                height: (typeof errorMsg == 'undefined') ? 340 : 400
            },
            size_main: {
                width: 750,
                height: 500
            },
            autoMapErrorFlag : false
        };

        popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: "&quot;" + title + "&quot;",
            top: 70,
            width: (mode ==0) ? this.params.size_menu.width : this.params.size_main.width,
            height: (mode ==0) ? this.params.size_menu.height : this.params.size_main.height,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        popUp.options.destroyOnClose = false;

        if (mode == 0) {
            $('modal_dialog_message').insert($('productSearchMenu_pop_up_content').show());
            $('productSearchMenu_error_block').hide();
            if (errorMsg != undefined) {
                $('productSearchMenu_error_message').update(errorMsg);
                $('productSearchMenu_error_block').show();
            }
        } else {
            $('modal_dialog_message').insert($('productSearch_pop_up_content').show());
            $('productSearch_form').hide();
            $('productSearch_back_button').hide();

            $('productSearch_buttons').show();
            new Ajax.Request(self.options.url.suggestedAsinGrid, {
                method: 'post',
                parameters: {
                    product_id : productId
                },
                onSuccess: function (transport) {
                    $('productSearch_grid').update(transport.responseText);
                    $('productSearch_grid').show();
                    $('productSearch_cleanSuggest_button').observe('click', function() {
                        if (confirm(self.options.text.confirm)) {
                            popUp.close();
                            self.unmapFromGeneralId(productId, function() {
                                self.openPopUp(0, self.params.title, self.params.productId);
                            });
                        }
                    });
                }
            });
        }

    },

    //----------------------------------

    showSearchManualPrompt: function ()
    {
        var self = this;

        popUp.close();

        popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: "&quot;" + self.params.title + "&quot;",
            top: 100,
            width: this.params.size_main.width,
            height: this.params.size_main.height,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        popUp.options.destroyOnClose = false;

        $('modal_dialog_message').insert($('productSearch_pop_up_content').show());
        //search manual
        $('productSearch_form').show();
        $('productSearch_back_button').show();
        $('productSearch_buttons').show();
        $('productSearch_error_block').hide();
        $('productSearch_grid').hide();
        $('query').value = '';
    },

    showSearchGeneralIdAutoPrompt: function ()
    {
        if (confirm(this.options.text.confirm)) {
            popUp.close();
            this.searchGeneralIdAuto(this.params.productId);
        }
    },

    showUnmapFromGeneralIdPrompt: function (productId)
    {
        MagentoMessageObj.clearAll();
        var self = this;

        if (confirm(self.options.text.confirm)) {
            this.unmapFromGeneralId(productId);
        }
    },

    addNewGeneralId: function (listingProductIds)
    {
        if (!this.options.customData.isNewAsinAvailable) {
            return alert(this.options.text.new_asin_not_available.replace('%code%',this.options.customData.marketplace.code));
        }

        if (!this.options.customData.isMarketplaceSynchronized) {
            alert(this.options.text.not_synchronized_marketplace.replace('%code%',this.options.customData.marketplace.code));
            return setLocation(this.options.url.marketplace_synch);
        }

        listingProductIds = listingProductIds || this.params.productId;

        this.postForm(
            this.options.url.newAsin,
            {
                listing_product_ids: listingProductIds
            }
        );
    },

    //----------------------------------

    searchGeneralIdManual: function(productId)
    {
        var self = this;
        var query = $('query').value;

        MagentoMessageObj.clearAll();

        if (query == '') {
            $('query').focus();
            alert(self.options.text.enter_productSearch_query);
            return;
        }

        $('productSearch_grid').hide();
        $('productSearch_error_block').hide();
        new Ajax.Request(self.options.url.searchAsinManual, {
            method: 'post',
            parameters: {
                query : query,
                product_id : productId
            },
            onSuccess: function (transport) {
                transport = transport.responseText.evalJSON();

                if(transport.result == 'success') {
                    $('productSearch_grid').update(transport.data);
                    $('productSearch_grid').show();
                } else {
                    $('productSearch_error_message').update(transport.data);
                    $('productSearch_error_block').show();
                }
            }
        });
    },

    searchGeneralIdAuto: function(productIds)
    {
        MagentoMessageObj.clearAll();
        var self = this;

        var selectedProductsString =  productIds.toString();
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            return;
        }

        var maxProductsInPart = 10;

        var result = new Array();
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        var selectedProductsParts = result;

        ListingProgressBarObj.reset();
        ListingProgressBarObj.show(self.options.text.automap_asin_progress_title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.params.autoMapErrorFlag = false;

        self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length);
    },

    sendPartsOfProducts : function(parts,totalPartsCount)
    {
        var self = this;

        if (parts.length == 0) {

            ListingProgressBarObj.setStatus(self.options.text.task_completed_message);
            ListingProgressBarObj.hide();
            ListingProgressBarObj.reset();
            GridWrapperObj.unlock();
            $('loading-mask').setStyle({visibility: 'visible'});

            self.gridHandler.unselectAllAndReload();

            if (self.params.autoMapErrorFlag == true) {
                MagentoMessageObj.addError(self.options.text.automap_error_message);
            }

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = part.length;
        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, self.options.text.sending_data_message));

        new Ajax.Request(self.options.url.searchAsinAuto, {
            method: 'post',
            parameters: {
                product_ids : partString
            },
            onSuccess: function (transport) {

                if (transport.responseText == 1) {
                    self.params.autoMapErrorFlag = true;
                }

                var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100,0);
                } else {
                    ListingProgressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsOfProducts(parts,totalPartsCount);
                },500);
            }
        });
    },

    //----------------------------------

    mapToGeneralId: function(productId, generalId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        $('productSearch_grid').hide();

        new Ajax.Request(self.options.url.mapToAsin, {
            method: 'post',
            parameters: {
                product_id : productId,
                general_id : generalId
            },
            onSuccess: function (transport) {
                if (transport.responseText == 0) {
                    self.gridHandler.unselectAllAndReload();
                } else {
                    alert(transport.responseText);
                }
            }
        });

        popUp.close();
    },

    unmapFromGeneralId: function(productIds, afterDoneFunction)
    {
        var self = this;

        this.gridHandler.unselectAll();

        self.flagSuccess = false;

        new Ajax.Request(self.options.url.unmapFromAsin, {
            method: 'post',
            parameters: {
                product_ids : productIds
            },
            onSuccess: function (transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();
                self.flagSuccess = true;

                var response = transport.responseText.evalJSON();

                MagentoMessageObj.clearAll();
                MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);
            },
            onComplete: function () {
                if (self.flagSuccess == true && afterDoneFunction != undefined) {
                    afterDoneFunction();
                }
            }
        });
    },

    //----------------------------------

    specificsChange: function(select)
    {
        var self = this;

        var idParts = explode('_', select.id);
        var id = idParts[2];
        var specifics = [];
        var selectedAsin = '';

        var asins = JSON.parse($('asins_' + id).innerHTML);

        $$('.specifics_' + id).each(function(el) {
            var specificName = explode('_', el.id);
            specificName = specificName[1];

            specifics[specificName] = el.value;
        });

        for (var spec in asins) {
            var productSpecifics = asins[spec].specifics;

            var found = true;
            for (var sName in productSpecifics) {

                if (productSpecifics[sName] != specifics[sName]) {
                    found = false;
                    break;
                }
            }

            if (found) {
                selectedAsin = spec;
                break;
            }
        }

        if (selectedAsin === '') {
            $('asin_link_'+id).innerHTML = $('parent_asin_'+id).innerHTML;
            return $('map_link_' + id).innerHTML = '<span style="color: #808080">' + self.options.text.assign + '</span>';
        }

        $('asin_link_'+id).innerHTML = selectedAsin;

        var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
        mapLinkTemplate = mapLinkTemplate.replace('%general_id%', selectedAsin);
        $('map_link_' + id).innerHTML = mapLinkTemplate;
    }

    //----------------------------------
});