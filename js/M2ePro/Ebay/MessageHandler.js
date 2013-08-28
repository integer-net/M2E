MessageHandler = Class.create();
MessageHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.messagesInfo = {};

        Validation.add('M2ePro-validate-message-max-length', M2ePro.text.message_max_length_error, function(value) {
            if (value.length < 1 || value.length > 2000) {
                return false;
            }
            return true;
        });
    },

    //----------------------------------

    getMessageInfo: function(messageId)
    {
        var self = MessageHandlerObj;

        if (typeof self.messagesInfo[messageId] == 'undefined') {
            new Ajax.Request( M2ePro.url.getMessageInfo,
            {
                method: 'get',
                asynchronous: true,
                parameters: {
                    message_id: messageId
                },
                onSuccess: function(transport)
                {
                    self.messagesInfo[messageId] = transport.responseText.evalJSON()['message_info'];
                }
            });
        }

        return self.messagesInfo[messageId];
    },

    //----------------------------------

    showMessageText: function(messageId)
    {
        var messageInfo = MessageHandlerObj.getMessageInfo(messageId);
        MessageHandlerObj.openMessageWindow(messageInfo['text'], M2ePro.text.message_text_title);
    },

    //----------------------------------

    showMessageResponse: function(messageId,response)
    {
        var messageInfo = MessageHandlerObj.getMessageInfo(messageId);
        MessageHandlerObj.openMessageWindow(messageInfo['responses'][response], M2ePro.text.message_response_title);
    },

    //----------------------------------

    openMessageWindow: function(data,title)
    {
        dialog_image_window = Dialog.info('', {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 250,
            width: 400,
            height: 220,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: "message-info"
        });

        $$('#message-info_content #modal_dialog_message')[0].insert(data);
    },

    //----------------------------------

    openMessage: function(self,messageId,messageSubject)
    {
        editForm.validator.reset();

        var urlItemId = trim($(self).up('td').previous(3).innerHTML);

        $('item_id').innerHTML = urlItemId;
        $('message_subject').innerHTML = messageSubject;
        $('message_id').value = messageId;
        $('message_text').value = '';

        $('magento_block_ebay_messages_response').show();

        var urlLastSymbol = window.location.href.charAt(window.location.href.length-1);
        if (urlLastSymbol == '#') {
            window.location.href = window.location.href;
        } else {
            window.location.href += '#';
        }
    },

    //----------------------------------

    cancelMessage: function()
    {
        $('magento_block_ebay_messages_response').hide();
    },

    //----------------------------------

    sendMessage: function()
    {
        MagentoMessageObj.clearAll();

        if (editForm.validate()) {
            var self = this;
            new Ajax.Request( M2ePro.url.formSubmit + '?' + $('edit_form').serialize(),
            {
                method: 'get',
                asynchronous: true,
                onSuccess: function(transport)
                {
                    MagentoMessageObj.addSuccess(M2ePro.text.message_sent_successfully);

                    self.cancelMessage();

                    messagesGridJsObject.reload();
                }
            });
        }
    }

    //----------------------------------

});