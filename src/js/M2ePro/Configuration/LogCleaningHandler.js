ConfigurationLogCleaningHandler = Class.create();
ConfigurationLogCleaningHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    runNowLog : function(log)
    {
        configEditForm.submit(M2ePro.url.get('formSubmit',{'task': 'run_now','log': log}));
    },

    clearAllLog : function(log)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        configEditForm.submit(M2ePro.url.get('formSubmit',{'task': 'clear_all','log': log}));
    },

    //----------------------------------

    changeModeLog : function(log)
    {
        var value = $(log+'_log_mode').value;

        if (value == '1') {
            $(log+'_log_days_container').style.display = '';
            $(log+'_log_button_run_now_container').style.display = '';
        } else {
            $(log+'_log_days_container').style.display = 'none';
            $(log+'_log_button_run_now_container').style.display = 'none';
        }
    }

    //----------------------------------
});