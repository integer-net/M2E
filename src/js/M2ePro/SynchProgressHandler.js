SynchProgressHandler = Class.create();
SynchProgressHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(progressBarObj, wrapperObj)
    {
        this.stateExecuting = 'executing';
        this.stateInactive = 'inactive';

        this.resultTypeError = 'error';
        this.resultTypeWarning = 'warning';
        this.resultTypeSuccess = 'success';

        this.progressBarObj = progressBarObj;
        this.wrapperObj = wrapperObj;
        this.loadingMask = $('loading-mask');
    },

    //----------------------------------

    start: function(title, status)
    {
        title = title || '';
        status = status || '';

        var self = this;

        self.progressBarObj.reset();

        if (title != '') {
            self.progressBarObj.setTitle(title);
        }
        if (status != '') {
            self.progressBarObj.setStatus(status);
        }

        self.progressBarObj.show();

        self.wrapperObj.lock();
        self.loadingMask.setStyle({visibility: 'hidden'});
    },

    end: function()
    {
        var self = this;

        self.progressBarObj.reset();
        self.progressBarObj.hide();

        self.wrapperObj.unlock();
        self.loadingMask.setStyle({visibility: 'visible'});
    },

    //----------------------------------

    initPageCheckState: function(callBackWhenEnd)
    {
        callBackWhenEnd = callBackWhenEnd || '';

        var self = this;
        new Ajax.Request(M2ePro.url.get('adminhtml_general/synchCheckState'), {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {

                if (transport.responseText == self.stateExecuting) {

                    self.start(
                        M2ePro.translator.translate('Another Synchronization Is Already Running.'),
                        M2ePro.translator.translate('Getting information. Please wait ...')
                    );

                    setTimeout(function() {
                        self.startGetExecutingInfo(callBackWhenEnd);
                    },2000);

                } else {

                    self.end();

                    if (callBackWhenEnd != '') {
                        eval(callBackWhenEnd);
                    }

                }
            }
        });
    },

    //----------------------------------

    runTask: function(title, url, components, callBackWhenEnd)
    {
        title = title || '';
        url = url || '';
        components = components || '';
        callBackWhenEnd = callBackWhenEnd || '';

        if (url == '') {
            return;
        }

        var self = this;
        new Ajax.Request(M2ePro.url.get('adminhtml_general/synchCheckState'), {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {

                if (transport.responseText == self.stateExecuting) {

                    self.start(
                        M2ePro.translator.translate('Another Synchronization Is Already Running.'),
                        M2ePro.translator.translate('Getting information. Please wait ...')
                    );

                    setTimeout(function() {
                        self.startGetExecutingInfo('self.runTask(\'' + title + '\',\'' + url + '\',\'' + components + '\',\'' + callBackWhenEnd + '\');');
                    },2000);

                } else {

                    self.start(title, M2ePro.translator.translate('Preparing to start. Please wait ...'));

                    new Ajax.Request(url, {
                        parameters: {components: components},
                        method: 'get',
                        asynchronous: true
                    });

                    setTimeout(function() {
                        self.startGetExecutingInfo(callBackWhenEnd);
                    },2000);
                }
            }
        });
    },

    //----------------------------------

    startGetExecutingInfo: function(callBackWhenEnd)
    {
        callBackWhenEnd = callBackWhenEnd || '';

        var self = this;
        new Ajax.Request(M2ePro.url.get('adminhtml_general/synchGetExecutingInfo'), {
            method:'get',
            asynchronous: true,
            onSuccess: function(transport) {

                var data = transport.responseText.evalJSON(true);

                if (data.mode == self.stateExecuting) {

                    self.progressBarObj.setTitle(data.title);
                    if (data.percents <= 0) {
                        self.progressBarObj.setPercents(0,0);
                    } else if (data.percents >= 100) {
                        self.progressBarObj.setPercents(100,0);
                    } else {
                        self.progressBarObj.setPercents(data.percents,1);
                    }
                    self.progressBarObj.setStatus(data.status);

                    self.wrapperObj.lock();
                    self.loadingMask.setStyle({visibility: 'hidden'});

                    setTimeout(function() {
                        self.startGetExecutingInfo(callBackWhenEnd);
                    },3000);

                } else {

                    self.progressBarObj.setPercents(100,0);

                    //-----------------
                    setTimeout(function() {

                        if (callBackWhenEnd != '') {
                            eval(callBackWhenEnd);
                        } else {

                            new Ajax.Request(M2ePro.url.get('adminhtml_general/synchGetLastResult'), {
                                method: 'get',
                                asynchronous: true,
                                onSuccess: function(transport) {
                                    self.end();
                                    self.printFinalMessage(transport.responseText);
                                    self.addProcessingNowWarning();
                                }
                            });
                        }
                    },1500);
                    //-----------------
                }
            }
        });
    },

    //----------------------------------

    printFinalMessage: function(resultType)
    {
        var self = this;
        if (resultType == self.resultTypeError) {
            MagentoMessageObj.addError(str_replace(
                '%url%',
                M2ePro.url.get('logViewUrl'),
                M2ePro.translator.translate('Synchronization ended with errors. <a target="_blank" href="%url%">View Log</a> for details.')
            ));
        } else if (resultType == self.resultTypeWarning) {
            MagentoMessageObj.addWarning(str_replace(
                '%url%',
                M2ePro.url.get('logViewUrl'),
                M2ePro.translator.translate('Synchronization ended with warnings. <a target="_blank" href="%url%">View Log</a> for details.')
            ));
        } else {
            MagentoMessageObj.addSuccess(M2ePro.translator.translate('Synchronization has successfully ended.'));
        }
    },

    //----------------------------------

    addProcessingNowWarning: function()
    {
        new Ajax.Request(M2ePro.url.get('synchCheckProcessingNow'), {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {

                var messages = transport.responseText.evalJSON().messages;

                if (messages.length < 1) {
                    return;
                }

                messages.each(function(message) {
                    MagentoMessageObj.addWarning(message);
                });
            }
        });
}

    //----------------------------------
});