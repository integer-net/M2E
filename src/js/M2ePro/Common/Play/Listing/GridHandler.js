PlayListingGridHandler = Class.create(CommonListingGridHandler, {

    //----------------------------------

    getComponent: function()
    {
        return 'play';
    },

    //----------------------------------

    getMaxProductsInPart: function()
    {
        return 100;
    },

    //----------------------------------

    prepareActions: function($super)
    {
        $super();
        this.movingHandler = new ListingMovingHandler(this);
        this.productSearchHandler = new PlayListingProductSearchHandler(this);

        this.actions = Object.extend(this.actions,{

            movingAction: this.movingHandler.run.bind(this.movingHandler),

            assignGeneralIdAction: (function() { this.productSearchHandler.searchGeneralIdAuto(this.getSelectedProductsString())}).bind(this),
            unassignGeneralIdAction: (function() { this.productSearchHandler.unmapFromGeneralId(this.getSelectedProductsString())}).bind(this)

        });

    }

    //----------------------------------

});