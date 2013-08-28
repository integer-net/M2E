EbayTemplateGeneralTabHandler = Class.create();
EbayTemplateGeneralTabHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    checkAttributeSetSelection: function()
    {
        if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
            ebayTemplateGeneralEditTabsJsTabs.showTabContent($('ebayTemplateGeneralEditTabs_general'));
        }
    },

    checkMarketplaceSelection: function()
    {
        if ($F('marketplace_id') === '') {
            alert(M2ePro.text.marketplace_not_selected_error);
            ebayTemplateGeneralEditTabsJsTabs.showTabContent($('ebayTemplateGeneralEditTabs_general'));
        }
    },

    checkCategoriesSelection: function()
    {
        if ( $F('categories_mode') === '' ||
            ($F('categories_mode') == EbayTemplateGeneralTabHandlerObj.CATEGORIES_MODE_EBAY && $F('categories_main_id') == 0) ||
            ($F('categories_mode') == EbayTemplateGeneralTabHandlerObj.CATEGORIES_MODE_ATTRIBUTE && $F('categories_main_attribute') === '')) {
            alert(M2ePro.text.main_category_not_selected_error);
            ebayTemplateGeneralEditTabsJsTabs.showTabContent($('ebayTemplateGeneralEditTabs_general'));
        }
    }

    //----------------------------------
});