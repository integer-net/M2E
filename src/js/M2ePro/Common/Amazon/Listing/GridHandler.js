CommonAmazonListingGridHandler = Class.create(CommonListingGridHandler, {

    //----------------------------------

    getComponent: function()
    {
        return 'amazon';
    },

    //----------------------------------

    getMaxProductsInPart: function()
    {
        return 1000;
    },

    //----------------------------------

    prepareActions: function($super)
    {
        $super();
        this.movingHandler = new ListingMovingHandler(this);
        this.actionHandler = new CommonAmazonListingActionHandler(this);
        this.productSearchHandler = new CommonAmazonListingProductSearchHandler(this);
        this.templateDescriptionHandler = new CommonAmazonListingTemplateDescriptionHandler(this);
        this.variationProductManageHandler = new CommonAmazonListingVariationProductManageHandler(this);

        this.actions = Object.extend(this.actions, {

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),

            assignTemplateDescriptionIdAction: (function() { this.templateDescriptionHandler.validateProductsForTemplateDescriptionAssign(this.getSelectedProductsString())}).bind(this),
            unassignTemplateDescriptionIdAction: (function() { this.templateDescriptionHandler.unassignFromTemplateDescrition(this.getSelectedProductsString())}).bind(this),

            assignGeneralIdAction: (function() { this.productSearchHandler.searchGeneralIdAuto(this.getSelectedProductsString())}).bind(this),
            newGeneralIdAction: (function() { this.productSearchHandler.addNewGeneralId(this.getSelectedProductsString())}).bind(this),
            unassignGeneralIdAction: (function() { this.productSearchHandler.unmapFromGeneralId(this.getSelectedProductsString())}).bind(this)

        });

    }

    //----------------------------------
});