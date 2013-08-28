LicenseHandler = Class.create();
LicenseHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    changeLicenseKey: function()
    {
        $('license_text_key_container').hide();
        $('license_input_key_container').show();
        $('change_license_key_container').hide();
        $('confirm_license_key_container').show();
    },

    //----------------------------------

    completeStep : function()
    {
        var self = this;
        var checkResult = false;

        new Ajax.Request( M2ePro.url.checkLicense ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                checkResult = transport.responseText.evalJSON()['ok'];
                if (checkResult) {
                    window.opener.completeStep = 1;
                    window.close();
                } else {
                    MagentoMessageObj.addError(M2ePro.text.license_validation_error);
                }
            }
        });
    }

    //----------------------------------
});