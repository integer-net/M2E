LogCleaningHandler = Class.create();
LogCleaningHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    runNowLogs : function()
    {
        MagentoMessageObj.addSuccess(M2ePro.text.clearing_all_logs_started_message);
        editForm.submit(M2ePro.url.formSubmit + 'task/run_now_logs/');
    },

    clearAllLogs : function()
    {
        if (!confirm(CONFIRM)) {
            return;
        }

        MagentoMessageObj.addSuccess(M2ePro.text.clearing_all_logs_started_message);
        editForm.submit(M2ePro.url.formSubmit + 'task/clear_all_logs/');
    },

    //----------------------------------

    runNowLog : function(log)
    {
        MagentoMessageObj.addSuccess(str_replace('%log%', str_replace('_',' ',log), M2ePro.text.clearing_log_started_message));
        editForm.submit(M2ePro.url.formSubmit + 'task/run_now/log/'+log+'/');
    },

    clearAllLog : function(log)
    {
        if (!confirm(CONFIRM)) {
            return;
        }

        MagentoMessageObj.addSuccess(str_replace('%log%', str_replace('_',' ',log), M2ePro.text.clearing_log_started_message));
        editForm.submit(M2ePro.url.formSubmit + 'task/clear_all/log/'+log+'/');
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