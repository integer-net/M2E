EbayConfigurationHandler = Class.create();
EbayConfigurationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    isMultiCurrencyPresented: function()
    {
        return Boolean(M2ePro.formData.multiCurrencyCount);
    },

    //----------------------------------

    viewModeChange: function()
    {
        var hidingBlocks = $$('#magento_block_ebay_configuration_general_notification',
                              '#magento_block_ebay_configuration_general_selling',
                              '#magento_block_ebay_configuration_general_parts_compatibility');

        hidingBlocks.invoke('hide');
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            hidingBlocks.invoke('show');
        }
    }

    //----------------------------------

});