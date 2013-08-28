VideoTutorialHandler = Class.create();
VideoTutorialHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro,popUpBlockId,title,callbackWhenClose)
    {
        this.M2ePro = M2ePro;
        this.title = title;
        this.popUpBlockId = popUpBlockId;
        this.callbackWhenClose = callbackWhenClose;
    },

    //----------------------------------

    openPopUp: function()
    {
        if (this.M2ePro.text.confim_offer_show_video) {
            if (!confirm(this.M2ePro.text.confim_offer_show_video)) {
                this.callbackWhenClose();
                return;
            }
        }

        var self = this;
        this.popUp = Dialog.info('', {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: this.title,
            top: 30,
            width: 900,
            height: 525,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                if (self.M2ePro.text.confim_close_video) {
                    return confirm(self.M2ePro.text.confim_close_video);
                }
                return true;
            },
            onClose: function() {
                self.callbackWhenClose();
            }
        });

        $('modal_dialog_message').update($(this.popUpBlockId).innerHTML);
    },

    closePopUp: function()
    {
        if (this.M2ePro.text.confim_close_video) {
            if (!confirm(this.M2ePro.text.confim_close_video)) {
                return;
            }
        }

        this.popUp.close();
        this.callbackWhenClose();
    }

    //----------------------------------
});