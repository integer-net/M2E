CommonListing = Class.create();
CommonListing.prototype = {

    //----------------------------------

    initialize: function(tabsComponent) {
        this.tabsComponent = tabsComponent
    },

    //----------------------------------

    getActiveTab: function() {
        var activeTabId = this.tabsComponent.activeTab.id;
        return activeTabId.replace(this.tabsComponent.containerId + '_', '');
    },

    createListing: function(url) {
        setLocation(url + 'component/' + this.getActiveTab());
    },

    viewLogs: function(url) {
        window.open(url + 'channel/' + this.getActiveTab())
    }
};
