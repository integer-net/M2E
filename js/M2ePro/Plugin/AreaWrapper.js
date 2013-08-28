AreaWrapper = Class.create();
AreaWrapper.prototype = {

    //----------------------------------

    initialize : function(containerId)
    {
        if (typeof containerId == 'undefined') {
            containerId = '';
        }

        this.containerId = containerId;
        this.wrapperId = this.containerId + '_wrapper';

        this.makeWrapperHtml();
    },

    //----------------------------------

    makeWrapperHtml : function()
    {
        var html = '<div id="' + this.wrapperId + '" class="area_wrapper" style="display: none;">&nbsp;</div>';
        $(this.containerId).insert ({'before':html} );
    },

    addDivClearBothToContainer : function()
    {
        $(this.containerId).innerHTML += '<div style="clear: both;"></div>';
    },

    //----------------------------------

    lock : function()
    {
        $(this.wrapperId).show();

        var positionContainer = $(this.containerId).cumulativeOffset();
        var widthContainer = $(this.containerId).getWidth();
        var heightContainer = $(this.containerId).getHeight();

        $(this.wrapperId).setStyle({left: positionContainer[0]+'px'});
        $(this.wrapperId).setStyle({top: positionContainer[1]+'px'});
        $(this.wrapperId).setStyle({width: widthContainer+'px'});
        $(this.wrapperId).setStyle({height: heightContainer+'px'});
    },

    unlock : function()
    {
        $(this.wrapperId).hide();
    }

    //----------------------------------
}