ListingActionHandler = Class.create();
ListingActionHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro, listingId)
    {
        this.M2ePro = M2ePro;
        this.sendPartsResponses = new Array();
        this.listingId = listingId;
    },

    //----------------------------------

    setListingItemGridHandlerObj: function(ListingItemGridHandlerObj)
    {
        this.ListingItemGridHandlerObj = ListingItemGridHandlerObj;
    },

    //----------------------------------

    startActions : function(title,url,selectedProductsParts)
    {
        MagentoMessageObj.clearAll();
        $('listing_container_errors_summary').hide();

        var self = this;
        new Ajax.Request( self.M2ePro.url.checkLockListing + 'id/' + self.listingId + '/' ,
            {
                method:'get',
                onSuccess: function(transport)
                {
                    if (transport.responseText == 'locked') {
                        MagentoMessageObj.addError(self.M2ePro.text.listing_locked_message);
                    } else {
                        new Ajax.Request( self.M2ePro.url.lockListingNow + 'id/' + self.listingId + '/' ,
                            {
                                method:'get',
                                onSuccess: function(transport)
                                {
                                    ListingProgressBarObj.reset();
                                    ListingProgressBarObj.show(title);
                                    GridWrapperObj.lock();
                                    $('loading-mask').setStyle({visibility: 'hidden'});

                                    self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length,url);
                                }
                            });
                    }
                }
            });
    },

    sendPartsOfProducts : function(parts,totalPartsCount,url)
    {
        var self = this;

        if (parts.length == totalPartsCount) {
            self.sendPartsResponses = new Array();
        }

        if (parts.length == 0) {

            ListingProgressBarObj.setPercents(100,0);
            ListingProgressBarObj.setStatus(self.M2ePro.text.task_completed_message);

            new Ajax.Request( self.M2ePro.url.unlockListingNow + 'id/' + self.listingId + '/' ,
                {
                    method:'get',
                    onSuccess: function(transport)
                    {
                        var combineResult = 'success';
                        for (var i=0;i<self.sendPartsResponses.length;i++) {
                            if (self.sendPartsResponses[i].result != 'success' && self.sendPartsResponses[i].result != 'warning') {
                                combineResult = 'error';
                                break;
                            }
                            if (self.sendPartsResponses[i].result == 'warning') {
                                combineResult = 'warning';
                            }
                        }

                        for (var i=0;i<self.sendPartsResponses.length;i++) {
                            if (typeof self.sendPartsResponses[i].is_processing_items != 'undefined' &&
                                self.sendPartsResponses[i].is_processing_items == true) {
                                MagentoMessageObj.addNotice(self.M2ePro.text.locked_obj_notice);
                                break;
                            }
                        }

                        if (combineResult == 'error') {

                            var message = self.M2ePro.text.task_completed_error_message;
                            message = message.replace('%s', ListingProgressBarObj.getTitle());
                            message = message.replace('%s', self.M2ePro.url.logViewUrl);

                            MagentoMessageObj.addError(message);

                            var actionIds = '';
                            for (var i=0;i<self.sendPartsResponses.length;i++) {
                                if (actionIds != '') {
                                    actionIds += ',';
                                }
                                actionIds += self.sendPartsResponses[i].action_id;
                            }

                            new Ajax.Request( self.M2ePro.url.getErrorsSummary + 'action_ids/' + actionIds + '/' ,
                                {
                                    method:'get',
                                    onSuccess: function(transportSummary)
                                    {
                                        $('listing_container_errors_summary').innerHTML = transportSummary.responseText;
                                        $('listing_container_errors_summary').show();
                                    }
                                });

                        } else if (combineResult == 'warning') {
                            var message = self.M2ePro.text.task_completed_warning_message;
                            message = message.replace('%s', ListingProgressBarObj.getTitle());
                            message = message.replace('%s', self.M2ePro.url.logViewUrl);

                            MagentoMessageObj.addWarning(message);
                        } else {
                            var message = self.M2ePro.text.task_completed_success_message;
                            message = str_replace('%s', ListingProgressBarObj.getTitle(), message);

                            MagentoMessageObj.addSuccess(message);
                        }

                        ListingProgressBarObj.hide();
                        ListingProgressBarObj.reset();
                        GridWrapperObj.unlock();
                        $('loading-mask').setStyle({visibility: 'visible'});

                        self.sendPartsResponses = new Array();

                        self.ListingItemGridHandlerObj.unselectAllAndReload();
                    }
                });

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = '';

        if (part.length <= 2) {

            for (var i=0;i<part.length;i++) {

                if (i != 0) {
                    partExecuteString += ', ';
                }

                var temp = self.ListingItemGridHandlerObj.getProductNameByRowId(part[i]);

                if (temp != '') {
                    if (temp.length > 75) {
                        temp = temp.substr(0, 75) + '...';
                    }
                    partExecuteString += '"' + temp + '"';
                } else {
                    partExecuteString = part.length;
                    break;
                }
            }

        } else {
            partExecuteString = part.length;
        }

        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%s', partExecuteString, self.M2ePro.text.sending_data_message));

        new Ajax.Request( url + 'id/' + self.listingId,
            {
                method: 'post',
                parameters: {
                    selected_products: partString
                },
                onSuccess: function(transport)
                {
                    if (!transport.responseText.isJSON()) {

                        if (transport.responseText != '') {
                            alert(transport.responseText);
                        }

                        ListingProgressBarObj.hide();
                        ListingProgressBarObj.reset();
                        GridWrapperObj.unlock();
                        $('loading-mask').setStyle({visibility: 'visible'});

                        self.sendPartsResponses = new Array();

                        self.ListingItemGridHandlerObj.unselectAllAndReload();

                        return;
                    }

                    var response = transport.responseText.evalJSON(true);

                    if (response.error) {
                        ListingProgressBarObj.hide();
                        ListingProgressBarObj.reset();
                        GridWrapperObj.unlock();
                        $('loading-mask').setStyle({visibility: 'visible'});

                        self.sendPartsResponses = new Array();

                        new Ajax.Request( self.M2ePro.url.unlockListingNow + 'id/' + self.listingId + '/', {
                            method: 'get'
                        });

                        alert(response.message);

                        return;
                    }

                    self.sendPartsResponses[self.sendPartsResponses.length] = response;

                    var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                    if (percents <= 0) {
                        ListingProgressBarObj.setPercents(0,0);
                    } else if (percents >= 100) {
                        ListingProgressBarObj.setPercents(100,0);
                    } else {
                        ListingProgressBarObj.setPercents(percents,1);
                    }

                    setTimeout(function() {
                        self.sendPartsOfProducts(parts,totalPartsCount,url);
                    },500);
                }
            });

        return;
    },

    //----------------------------------

    runListAllProducts : function()
    {
        if (!confirm(CONFIRM)) {
            return;
        }

        this.ListingItemGridHandlerObj.selectAll();

        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            alert(this.M2ePro.text.listing_empty_message);
            return;
        }

        this.startActions(this.M2ePro.text.listing_all_items_message, this.M2ePro.url.runListProducts,selectedProductsParts);
    },

    //----------------------------------

    runListProducts : function()
    {
        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(this.M2ePro.text.listing_selected_items_message, this.M2ePro.url.runListProducts,selectedProductsParts);
    },

    runReviseProducts : function()
    {
        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(this.M2ePro.text.revising_selected_items_message, this.M2ePro.url.runReviseProducts,selectedProductsParts);
    },

    runRelistProducts : function()
    {
        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(this.M2ePro.text.relisting_selected_items_message, this.M2ePro.url.runRelistProducts,selectedProductsParts);
    },

    runStopProducts : function()
    {
        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(this.M2ePro.text.stopping_selected_items_message, this.M2ePro.url.runStopProducts,selectedProductsParts);
    },

    runStopAndRemoveProducts : function()
    {
        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(this.M2ePro.text.stopping_and_removing_selected_items_message, this.M2ePro.url.runStopAndRemoveProducts,selectedProductsParts);
    },

    runDeleteAndRemoveProducts : function()
    {
        var selectedProductsParts = this.ListingItemGridHandlerObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(this.M2ePro.text.deleting_and_removing_selected_items_message, this.M2ePro.url.runDeleteAndRemoveProducts,selectedProductsParts);
    },

    //----------------------------------

    duplicateProducts: function(selectedProductsString)
    {
        this.scroll_page_to_top();
        MagentoMessageObj.clearAll();

        new Ajax.Request(this.M2ePro.url.duplicate_products, {
            method: 'post',
            parameters: {
                component: this.M2ePro.customData.componentMode,
                ids: selectedProductsString
            },
            onSuccess: (function(transport) {
                try {
                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type != 'error') {
                        this.ListingItemGridHandlerObj.unselectAllAndReload();
                    }

                } catch (e) {
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    }

    //----------------------------------
});