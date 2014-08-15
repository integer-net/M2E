AmazonListingGridHandler = Class.create(CommonListingGridHandler, {

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
        this.actionHandler = new AmazonListingActionHandler(this);
        this.productSearchHandler = new AmazonListingProductSearchHandler(this);

        this.actions = Object.extend(this.actions,{

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),

            assignGeneralIdAction: (function() { this.productSearchHandler.searchGeneralIdAuto(this.getSelectedProductsString())}).bind(this),
            newGeneralIdAction: (function() { this.productSearchHandler.addNewGeneralId(this.getSelectedProductsString())}).bind(this),
            unassignGeneralIdAction: (function() { this.productSearchHandler.unmapFromGeneralId(this.getSelectedProductsString())}).bind(this)

        });

    }

    //----------------------------------

});