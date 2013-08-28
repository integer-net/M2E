AmazonListingProductSearchHandler = Class.create();
AmazonListingProductSearchHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro)
    {
        this.M2ePro = M2ePro;
        this.params = {
            autoMapErrorFlag : false
        }
    },

    //----------------------------------

    openPopUp: function(mode, title, productId, errorMsg)
    {
        MagentoMessageObj.clearAll();

        var self = this;

        eval(self.M2ePro.customData.gridId + '_massactionJsObject.unselectAll()');

        this.params = {
            mode: mode,
            title: title,
            productId: productId,
            size_menu: {
                width: 500,
                height: (errorMsg == undefined) ? 340 : 400
            },
            size_main: {
                width: 750,
                height: 500
            },
            autoMapErrorFlag : false
        };

        popUp = Dialog.info('', {
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

        if (mode == 0) {
            $('modal_dialog_message').update($('productSearchMenu_pop_up_content').innerHTML);
            $('productSearchMenu_error_block').hide();
            $('productSearchMenu_cancel_button').observe('click', function(){
                popUp.close();
            });
            if (errorMsg != undefined) {
                $('productSearchMenu_error_message').update(errorMsg);
                $('productSearchMenu_error_block').show();
            }
        } else {
            $('modal_dialog_message').update($('productSearch_pop_up_content').innerHTML);
            $('productSearch_form').hide();
            $('productSearch_back_button').hide();
            $('productSearch_cancel_button').observe('click',function(event){
                popUp.close();
            });
            $('productSearch_buttons').show();
            new Ajax.Request(self.M2ePro.url.suggestedAsinGrid, {
                method: 'post',
                parameters: {
                    product_id : productId
                },
                onSuccess: function (transport) {
                    $('productSearch_grid').update(transport.responseText);
                    $('productSearch_grid').show();
                    $('productSearch_cleanSuggest_button').observe('click', function() {
                        if (confirm(self.M2ePro.text.confirm)) {
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

        popUp = Dialog.info('', {
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

        $('modal_dialog_message').update($('productSearch_pop_up_content').innerHTML);
        //search manual
        $('productSearch_form').show();
        $('productSearch_back_button').show();
        $('productSearch_buttons').show();
        $('productSearch_error_block').hide();
        $('productSearch_grid').hide();
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
        $('productSearch_cancel_button').observe('click',function(event){
            popUp.close();
        });

        $('query').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.searchGeneralIdManual(self.params.productId);
        });
    },

    showSearchGeneralIdAutoPrompt: function ()
    {
        if (confirm(this.M2ePro.text.confirm)) {
            popUp.close();
            this.searchGeneralIdAuto(this.params.productId);
        }
    },

    showUnmapFromGeneralIdPrompt: function (productId)
    {
        MagentoMessageObj.clearAll();
        var self = this;

        if (confirm(self.M2ePro.text.confirm)) {
            this.unmapFromGeneralId(productId);
        }
    },

    addNewGeneralId: function (listingProductIds)
    {
        if (!this.M2ePro.customData.isNewAsinAvailable) {
            return alert(this.M2ePro.text.new_asin_not_available.replace('%code%',this.M2ePro.customData.marketplace.code));
        }

        if (!this.M2ePro.customData.isMarketplaceSynchronized) {
            alert(this.M2ePro.text.not_synchronized_marketplace.replace('%code%',this.M2ePro.customData.marketplace.code));
            return setLocation(this.M2ePro.url.marketplace_synch);
        }

        listingProductIds = listingProductIds || this.params.productId;

        this.postForm(
            this.M2ePro.url.newAsin,
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
            alert(self.M2ePro.text.enter_productSearch_query);
            return;
        }

        $('productSearch_grid').hide();
        $('productSearch_error_block').hide();
        new Ajax.Request(self.M2ePro.url.searchAsinManual, {
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
        ListingProgressBarObj.show(self.M2ePro.text.automap_asin_progress_title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.params.autoMapErrorFlag = false;

        self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length);
    },

    sendPartsOfProducts : function(parts,totalPartsCount)
    {
        var self = this;

        if (parts.length == 0) {

            ListingProgressBarObj.setStatus(self.M2ePro.text.task_completed_message);
            ListingProgressBarObj.hide();
            ListingProgressBarObj.reset();
            GridWrapperObj.unlock();
            $('loading-mask').setStyle({visibility: 'visible'});

            eval(self.M2ePro.customData.gridId + '_massactionJsObject.unselectAll()');
            eval(self.M2ePro.customData.gridId + 'JsObject.reload()');

            if (self.params.autoMapErrorFlag == true) {
                MagentoMessageObj.addError(self.M2ePro.text.automap_error_message);
            }

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = part.length;
        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%s', partExecuteString, self.M2ePro.text.sending_data_message));

        new Ajax.Request(self.M2ePro.url.searchAsinAuto, {
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

        if (!confirm(self.M2ePro.text.confirm)) {
            return;
        }

        $('productSearch_grid').hide();

        new Ajax.Request(self.M2ePro.url.mapToAsin, {
            method: 'post',
            parameters: {
                product_id : productId,
                general_id : generalId
            },
            onSuccess: function (transport) {
                if (transport.responseText == 0) {
                    eval(self.M2ePro.customData.gridId + 'JsObject.reload()');
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

        eval(self.M2ePro.customData.gridId + '_massactionJsObject.unselectAll()');

        self.flagSuccess = false;

        new Ajax.Request(self.M2ePro.url.unmapFromAsin, {
            method: 'post',
            parameters: {
                product_ids : productIds
            },
            onSuccess: function (transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                eval(self.M2ePro.customData.gridId + 'JsObject.reload()');
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
    }

    //----------------------------------
});