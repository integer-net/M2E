var debugKeys = [68, 69, 66, 85, 71];
var debugPressedKeys = [];

document.observe('keydown', function(event) {

    if (debugPressedKeys.length < debugKeys.length) {
        if (debugKeys[debugPressedKeys.length] == event.keyCode) {
            debugPressedKeys.push(event.keyCode);
            if (debugPressedKeys.length == debugKeys.length) {

                if (!$('magento_block_debug_information')) {
                    new Ajax.Request(M2ePro.url.get('adminhtml_order/getDebugInformation'), {
                        method: 'get',
                        asynchronous: true,
                        onSuccess: function(transport)
                        {
                            $$('.content-header')[0].insert({
                                after: transport.responseText
                            });
                        }
                    });
                }

                debugPressedKeys = [];
            }
        } else {
            debugPressedKeys = [];
        }
    }
});