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
        new Ajax.Request( M2ePro.url.formSubmit + '?' + $('edit_form').serialize() ,
        {
            method: 'get',
            parameters: {components: components},
            asynchronous: true,
            onSuccess: function(transport)
            {
                MagentoMessageObj.addSuccess(M2ePro.text.synch_settings_saved_successfully);
                if (runSynch != '') {
                    eval('self.'+runSynch+'(components);');
                }
            }
        });
    },

    //----------------------------------

    runAllEnabledNow : function(components)
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_all_enabled_tasks, M2ePro.url.runAllEnabledNow, components);
    },

    //----------------------------------

    runNowTemplates : function(components)
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_templates, M2ePro.url.runNowTemplates, components);
    },

    runNowOrders : function(components)
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_orders, M2ePro.url.runNowOrders, components);
    },

    runNowFeedbacks : function(components)
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_feedbacks, M2ePro.url.runNowFeedbacks, components);
    },

    runNowOtherListings : function(components)
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_3rd_party, M2ePro.url.runNowOtherListings, components);
    },

    runNowMessages : function(components)
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_messages, M2ePro.url.runNowMessages, components);
    },

    //----------------------------------

    changeTemplatesMode : function(component)
    {
        var value = $(component + '_templates_mode').value;

        if (value == '1') {
            $(component + '_templates_run_now_container').show();
        } else {
            $(component + '_templates_run_now_container').hide();
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

    changeMessagesMode : function(component)
    {
        var value = $(component + '_messages_mode').value;

        if (value == '1') {
            $(component + '_messages_run_now_container').show();
        } else {
            $(component + '_messages_run_now_container').hide();
        }
    },

    //----------------------------------

    moveChildBlockContent: function(childBlockId, destinationBlockId)
    {
        if (childBlockId == '' || destinationBlockId == '') {
            return;
        }

        $(destinationBlockId).appendChild($(childBlockId));
    }

    //----------------------------------

});