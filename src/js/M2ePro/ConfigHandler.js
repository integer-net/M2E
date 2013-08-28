ConfigHandler = Class.create();
ConfigHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    saveConfig : function()
    {
        MagentoMessageObj.clearAll();

        if (editForm.validate()) {

            var self = this;
            new Ajax.Request( M2ePro.url.formSubmit + '?' + $('edit_form').serialize() ,
            {
                method:'get',
                asynchronous: true,
                onSuccess: function(transport)
                {
                    if ($('config_mode').value == 'add') {
                        MagentoMessageObj.addSuccess(M2ePro.text.config_data_successfully_added_message);
                    } else {
                        MagentoMessageObj.addSuccess(M2ePro.text.config_data_successfully_updated_message);
                    }

                    self.cancelEdit();

                    configViewGridJsObject.reload();
                }
            });
        }
    },

    removeConfig : function(group,key)
    {
        if (!confirm(M2ePro.text.config_data_confirm_delete_message)) {
            return;
        }

        MagentoMessageObj.clearAll();

        var self = this;
        new Ajax.Request( M2ePro.url.deleteAction + '?config_group=' + group + '&config_key=' + key ,
        {
            method:'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                MagentoMessageObj.addSuccess(M2ePro.text.config_data_successfully_deleted_message);
                configViewGridJsObject.reload();
            }
        });
    },

    //----------------------------------

    setForAdd : function()
    {
        $('config_container').show();
        $('config_edit_form_header').innerHTML = M2ePro.text.config_data_add_message;

        $('config_mode').value = 'add';
        $('config_id').value = '';

        $('config_group').value = '';
        $('config_key').value = '';
        $('config_value').value = '';
        $('config_notice').value = '';

        CommonHandlerObj.scroll_page_to_top();
    },

    setForUpdate : function(id,group,key,value,notice)
    {
        $('config_container').show();
        $('config_edit_form_header').innerHTML = M2ePro.text.config_data_edit_message;

        $('config_mode').value = 'edit';
        $('config_id').value = id;

        $('config_group').value = group;
        $('config_key').value = key;
        $('config_value').value = value;
        $('config_notice').value = notice;

        CommonHandlerObj.scroll_page_to_top();
    },

    cancelEdit : function()
    {
        $('config_container').hide();
    }

    //----------------------------------
});