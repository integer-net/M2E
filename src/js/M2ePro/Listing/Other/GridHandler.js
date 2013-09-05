ListingOtherGridHandler = Class.create(GridHandler, {

    //----------------------------------

    productTitleCellIndex: 2,

    //----------------------------------

    prepareActions: function()
    {
        this.movingHandler      = new ListingMovingHandler(this);
        this.autoMappingHandler = new ListingOtherAutoMappingHandler(this);

        this.actions = {
            movingAction : this.movingHandler.run.bind(this.movingHandler),
            autoMappingAction : this.autoMappingHandler.run.bind(this.autoMappingHandler)
        };
    }

    //----------------------------------
});