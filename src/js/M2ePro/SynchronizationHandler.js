SynchronizationHandler = Class.create();
SynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(synchProgressObj)
    {
        this.synchProgressObj = synchProgressObj;
        this.synchProgressObj.addProcessingNowWarning();
    },

    //----------------------------------

    completeStep: function()
    {
        window.opener.completeStep = 1;
        window.close();
    },

    saveSettings : function(runSynch, components)
    {
        MagentoMessageObj.clearAll();
        runSynch  = runSynch  || '';
        components = components || '';

        components = Object.isString(components)
                     ? [components]
                     : components;
        components = Object.toJSON(components);

        CommonHandlerObj.scroll_page_to_top();

        var self = this;
        new Ajax.Request( M2ePro.url.get('formSubmit', $('edit_form').serialize(true)) ,
        {
            method: 'get',
            parameters: {components: components},
            asynchronous: true,
            onSuccess: function(transport)
            {
                MagentoMessageObj.addSuccess(M2ePro.translator.translate('Synchronization settings have been saved.'));
                if (runSynch != '') {
                    eval('self.'+runSynch+'(components);');
                }
            }
        });
    },

    //----------------------------------

    runAllEnabledNow : function(components)
    {
        this.synchProgressObj.runTask(
            M2ePro.translator.translate('Running All Enabled Tasks'),
            M2ePro.url.get('runAllEnabledNow'),
            components
        );
    },

    //----------------------------------

    runNowTemplates : function(components)
    {
        this.synchProgressObj.runTask(
            M2ePro.translator.translate('Inventory Synchronization'),
            M2ePro.url.get('runNowTemplates'),
            components
        );
    },

    runNowOrders : function(components)
    {
        this.synchProgressObj.runTask(
            M2ePro.translator.translate('Orders Synchronization'),
            M2ePro.url.get('runNowOrders'),
            components
        );
    },

    runNowFeedbacks : function(components)
    {
        this.synchProgressObj.runTask(
            M2ePro.translator.translate('Feedback Synchronization'),
            M2ePro.url.get('runNowFeedbacks'),
            components
        );
    },

    runNowOtherListings : function(components)
    {
        this.synchProgressObj.runTask(
            M2ePro.translator.translate('3rd Party Listings Synchronization'),
            M2ePro.url.get('runNowOtherListings'),
            components
        );
    },

    //----------------------------------

    changeTemplatesMode : function(component)
    {
        var value = $(component + '_templates_mode').value;

        var reviseAllNotification = $('block_notice_'+component+'_synchronization_revise_all');

        if (value == '1') {
            $(component + '_templates_run_now_container').show();
            reviseAllNotification && reviseAllNotification.show();
        } else {
            $(component + '_templates_run_now_container').hide();
            reviseAllNotification && reviseAllNotification.hide();
        }
    },

    changeOrdersMode : function(component)
    {
        var value = $(component + '_orders_mode').value;

        if (value == '1') {
            $(component + '_orders_run_now_container').show();
        } else {
            $(component + '_orders_run_now_container').hide();
        }
    },

    changeFeedbacksMode : function(component)
    {
        var value = $(component + '_feedbacks_mode').value;

        if (value == '1') {
            $(component + '_feedbacks_run_now_container').show();
        } else {
            $(component + '_feedbacks_run_now_container').hide();
        }
    },

    changeOtherListingsMode : function(component)
    {
        var value = $(component + '_other_listings_mode').value;

        if (value == '1') {
            $(component + '_other_listings_run_now_container').show();

            if (component == 'ebay') {
                $('ebay_other_listings_synchronization_settings_button_container').show();
            }
        } else {
            $(component + '_other_listings_run_now_container').hide();

            if (component == 'ebay') {
                $(component + '_other_listings_synchronization_settings_button_container').hide();
            }
        }
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

    showReviseAllConfirmPopup: function(component)
    {
        this.reviseAllConfirmPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Revise All'),
            top: 160,
            height: 80,
            width: 650,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this.reviseAllConfirmPopUp.options.destroyOnClose = false;
        $('modal_dialog_message').insert($(component + '_revise_all_confirm_popup').show());
    },

    runReviseAll: function(component)
    {
        new Ajax.Request(M2ePro.url.get('runReviseAll'), {
            parameters: {component: component},
            onSuccess: function(transport)
            {
                this.initReviseAllInfo(
                    true, transport.responseText.evalJSON()['start_date'],
                    null, component
                );
            }.bind(this)
        });
    },

    initReviseAllInfo: function(inProgress, startDate, endDate, component)
    {
        $(component + '_revise_all_end').hide();
        if (inProgress) {
            $(component + '_revise_all_start').show();
            $(component + '_revise_all_start_date').update(startDate);
        } else {
            $(component + '_revise_all_start').hide();
            if (endDate) {
                $(component + '_revise_all_end').show();
                $(component + '_revise_all_end_date').update(endDate);
            }
        }
    }

    //----------------------------------

});