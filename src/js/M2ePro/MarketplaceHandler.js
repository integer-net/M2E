MarketplaceHandler = Class.create();
MarketplaceHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(synchProgressObj)
    {
        this.synchProgressObj = synchProgressObj;

        this.marketplacesForUpdate = new Array();
        this.marketplacesForUpdateCurrentIndex = 0;

        this.synchErrors = 0;
        this.synchWarnings = 0;
        this.synchSuccess = 0;
    },

    //----------------------------------

    moveChildBlockContent: function(childBlockId, destinationBlockId)
    {
        if (childBlockId == '' || destinationBlockId == '') {
            return;
        }

        $(destinationBlockId).appendChild($(childBlockId));
    },

    //----------------------------------

    saveSettings : function(runSynch)
    {
        MagentoMessageObj.clearAll();
        runSynch = runSynch || '';

        CommonHandlerObj.scroll_page_to_top();

        var self = this;
        new Ajax.Request( M2ePro.url.get('formSubmit', $('edit_form').serialize(true)) ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                MagentoMessageObj.addSuccess(M2ePro.translator.translate('Marketplaces settings have been saved.'));
                if (runSynch != '') {
                    eval('self.'+runSynch+'();');
                }
            }
        });
    },

    //----------------------------------

    completeStep : function()
    {
        var self = this;
        var isMarketplaceSelected = false;

        $$('.marketplace_status_select').each(function(obj) {
            if (obj.value == 1) {
                isMarketplaceSelected = true;
            }
        });

        if (isMarketplaceSelected) {

            self.saveSettings('runSynchNow');

            var intervalId = setInterval(function(){
                if (typeof self.marketplacesUpdateFinished != 'undefined' && self.marketplacesUpdateFinished) {
                    clearInterval(intervalId);
                    window.opener.completeStep = 1;
                    window.close();
                }
            }, 1000);
        } else {
            MagentoMessageObj.addError(M2ePro.translator.translate('You must select at least one marketplace you will work with.'));
        }
    },

    //----------------------------------

    runSynchNow : function()
    {
        var self = this;

        self.marketplacesForUpdate = new Array();
        self.marketplacesForUpdateCurrentIndex = 0;

        $$('select.marketplace_status_select').each(function(marketplaceObj) {
            var marketplaceId = marketplaceObj.readAttribute('marketplace_id');
            var marketplaceState = marketplaceObj.value;

            if (!marketplaceId) {
                return;
            }

            if (marketplaceState == 1) {
                $('synch_info_wait_'+marketplaceId).show();
                $('synch_info_process_'+marketplaceId).hide();
                $('synch_info_complete_'+marketplaceId).hide();
                self.marketplacesForUpdate[self.marketplacesForUpdate.length] = marketplaceId;
            } else {
                $('synch_info_wait_'+marketplaceId).hide();
                $('synch_info_process_'+marketplaceId).hide();
                $('synch_info_complete_'+marketplaceId).hide();
            }
        });

        if (self.marketplacesForUpdate.length == 0) {
            return;
        }

        self.marketplacesForUpdateCurrentIndex = 0;

        self.synchErrors = 0;
        self.synchWarnings = 0;
        self.synchSuccess = 0;

        self.runNextMarketplaceNow();
    },

    runNextMarketplaceNow : function()
    {
        var self = this;

        if (self.marketplacesForUpdateCurrentIndex > 0) {

            $('synch_info_wait_'+self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex-1]).hide();
            $('synch_info_process_'+self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex-1]).hide();
            $('synch_info_complete_'+self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex-1]).show();

            var tempEndFlag = 0;
            if (self.marketplacesForUpdateCurrentIndex >= self.marketplacesForUpdate.length) {
                tempEndFlag = 1;
            }

            new Ajax.Request( M2ePro.url.get('adminhtml_general/synchGetLastResult') ,
            {
                method:'get',
                asynchronous: true,
                onSuccess: function(transport) {
                    if (transport.responseText == self.synchProgressObj.resultTypeError) {
                        self.synchErrors++;
                    } else if (transport.responseText == self.synchProgressObj.resultTypeWarning) {
                        self.synchWarnings++;
                    } else {
                        self.synchSuccess++;
                    }

                    if (tempEndFlag == 1) {
                        if (self.synchErrors > 0) {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeError);
                        } else if (self.synchWarnings > 0) {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeWarning);
                        } else {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeSuccess);
                        }
                        self.synchErrors = 0;
                        self.synchWarnings = 0;
                        self.synchSuccess = 0;
                    }
                }
            });
        }

        if (self.marketplacesForUpdateCurrentIndex >= self.marketplacesForUpdate.length) {

            self.marketplacesForUpdate = new Array();
            self.marketplacesForUpdateCurrentIndex = 0;
            self.marketplacesUpdateFinished = true;

            self.synchProgressObj.end();

            return;
        }

        var marketplaceId = self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex];
        self.marketplacesForUpdateCurrentIndex++;

        $('synch_info_wait_'+marketplaceId).hide();
        $('synch_info_process_'+marketplaceId).show();
        $('synch_info_complete_'+marketplaceId).hide();

        var titleProgressBar = $('marketplace_title_'+marketplaceId).innerHTML;
        var marketplaceComponentName = $('status_'+marketplaceId).readAttribute('markeptlace_component_name');

        if (marketplaceComponentName != '') {
            titleProgressBar = marketplaceComponentName + ' ' + titleProgressBar;
        }

        self.synchProgressObj.runTask(
            titleProgressBar,
            M2ePro.url.get('runSynchNow', {'marketplace_id': marketplaceId}),
            '', 'MarketplaceHandlerObj.runNextMarketplaceNow();'
        );
    },

    runSingleMarketplaceSynchronization: function(runNowButton)
    {
        var self = this;
        var marketplaceStatusSelect = $(runNowButton).up('tr').select('.marketplace_status_select')[0];

        self.saveSettings();

        self.marketplacesForUpdate = [marketplaceStatusSelect.readAttribute('marketplace_id')];
        self.marketplacesForUpdateCurrentIndex = 0;

        self.synchErrors = 0;
        self.synchWarnings = 0;
        self.synchSuccess = 0;

        self.runNextMarketplaceNow();
    },

    //----------------------------------

    changeStatus : function(element, marketplaceId)
    {
        var marketplaceId = element.readAttribute('marketplace_id');
        var runSingleButton = $('run_single_button_' + marketplaceId);

        if (element.value == '1') {
            element.removeClassName('lacklustre_selected');
            element.addClassName('hightlight_selected');
            runSingleButton && runSingleButton.show();
        } else {
            element.removeClassName('hightlight_selected');
            element.addClassName('lacklustre_selected');
            $('synch_info_complete_'+marketplaceId).hide();
            runSingleButton && runSingleButton.hide();
        }
    }

    //----------------------------------
});