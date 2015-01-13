WizardHandler = Class.create();
WizardHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize : function(currentStatus, currentStep, hiddenSteps)
    {
        this.currentStatus = currentStatus;

        this.steps = {};
        this.steps.current = currentStep;
        this.steps.hidden = hiddenSteps || [];
        this.steps.nicks = [];
    },

    //----------------------------------

    skip : function(url)
    {
        if (!confirm(M2ePro.translator.translate('Note: If you close the wizard, it never starts again. You will be required to set all settings manually. Press Cancel to continue working with wizard.'))) {
            return;
        }

        setLocation(url);
    },

    complete : function(url)
    {
        setLocation(url);
    },

    //----------------------------------

    setStatus : function(status, callback)
    {
        new Ajax.Request( M2ePro.url.get('setStatus') ,
        {
            method: 'get',
            parameters: {
                status: status
            },
            asynchronous: true,
            onSuccess: (function(transport) {
                var response = transport.responseText.evalJSON();

                if (response.type == 'error') {
                    this.scroll_page_to_top();
                    return MagentoMessageObj.addError(response.message);
                }

                this.currentStatus = status;

                if (typeof callback == 'function') {
                    callback();
                }

            }).bind(this)
        })
    },

    setStep : function(step, callback)
    {
        new Ajax.Request( M2ePro.url.get('setStep') ,
        {
            method: 'get',
            parameters: {
                step: step
            },
            asynchronous: true,
            onSuccess: (function(transport) {
                var response = transport.responseText.evalJSON();

                if (response.type == 'error') {
                    this.scroll_page_to_top();
                    return MagentoMessageObj.addError(response.message);
                }

                this.steps.current = step;

                if (typeof callback == 'function') {
                    callback();
                }

                this.renderStep(step);

            }).bind(this)
        })
    },

    //----------------------------------

    addStep : function(step, stepContainerId)
    {
        var self = WizardHandlerObj;

        if (self.steps.hidden.indexOf(step) != -1) {
            return;
        }

        self.steps[step] = stepContainerId;
        self.steps.nicks.push(step);
        self.renderStep(step);
    },

    removeStep : function(step)
    {
        delete this.steps[step];
        if (this.steps.nicks.indexOf(step) != -1) {
            this.steps.nicks.splice(this.steps.nicks.indexOf(step),1);
        }
    },

    getNextStepByNick : function(step)
    {
        var self = WizardHandlerObj;
        var stepIndex = self.steps.nicks.indexOf(step);

        if (stepIndex == -1) {
            return null;
        }

        var nextStepNick = self.steps.nicks[stepIndex + 1];

        if (typeof nextStepNick == 'undefined') {
            return null;
        }

        return nextStepNick;
    },

    //----------------------------------

    renderStep : function(step)
    {
        var self = WizardHandlerObj;
        var stepContainerId = self.steps[step];

        if (typeof stepContainerId == 'undefined') {
            return;
        }

        // Render step subtitle
        //----------------
        var stepNumber = self.steps.nicks.indexOf(step) + 1;
        var subtitle = '[' + M2ePro.translator.translate('Step') + ' ' + stepNumber + ']';

        $(stepContainerId).writeAttribute('subtitle', subtitle);

        if (typeof $$('#' + stepContainerId + ' span.subtitle')[0] != 'undefined') {
            $$('#' + stepContainerId + ' span.subtitle')[0].innerHTML = subtitle;
        }
        //----------------

        $$('#'+stepContainerId+' .step_completed').each(function(obj) {
            obj.hide();
        });
        $$('#'+stepContainerId+' .step_skip').each(function(obj) {
            obj.hide();
        });
        $$('#'+stepContainerId+' .step_process').each(function(obj) {
            obj.hide();
        });
        $$('#'+stepContainerId+' .step_incomplete').each(function(obj) {
            obj.hide();
        });

        var stepIndex = self.steps.all.indexOf(step);
        var currentStepIndex = self.steps.all.indexOf(self.steps.current);

        if (currentStepIndex >= stepIndex) {
            $(stepContainerId).show();
        } else {
            $(stepContainerId).hide();
        }

        if ((currentStepIndex > stepIndex) ||
            self.currentStatus == M2ePro.php.constant('Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED') ||
            self.currentStatus == M2ePro.php.constant('Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED')) {
            $$('#'+stepContainerId+' .step_completed').each(function(obj) {
                obj.show();
            });
            $$('#'+stepContainerId+' .step_container_buttons').each(function(obj) {
                obj.remove();
            });
            $(stepContainerId).writeAttribute('style','background-color: #F2EFEF !important; border-color: #008035 !important;');
        } else {
            $$('#'+stepContainerId+' .step_skip').each(function(obj) {
                obj.show();
            });
            $$('#'+stepContainerId+' .step_process').each(function(obj) {
                obj.show();
            });
            if (window.completeStep == 0) {
                $$('#'+stepContainerId+' .step_incomplete').each(function(obj) {
                    obj.show();
                });
            }
        }
    },

    //----------------------------------

    processStep : function(stepWindowUrl, step, callback)
    {
        var self = WizardHandlerObj;
        var win = window.open(stepWindowUrl);

        window.completeStep = 0;

        var intervalId = setInterval(function(){
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            if (window.completeStep == 1) {
                var nextStepNick = self.getNextStepByNick(step);

                if (nextStepNick) {
                    return self.setStep(nextStepNick, function() {
                        if (typeof callback == 'function') {
                            callback();
                        }

                        self.renderStep(step);
                    });
                }

                self.setStatus(M2ePro.php.constant('Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED'),function() {
                    self.renderStep(step);
                    self.setStep(null,callback)
                })

            } else {
                self.renderStep(step);
            }
        }, 1000);
    },

    skipStep : function(step, callback)
    {
        var self = WizardHandlerObj;
        var nextStepNick = self.getNextStepByNick(step);

        if (nextStepNick) {
            return self.setStep(nextStepNick, function() {
                if (typeof callback == 'function') {
                    callback();
                }
                self.renderStep(step);
            });
        }

        self.setStatus(M2ePro.php.constant('Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED'),function() {
            if (typeof callback == 'function') {
                callback();
            }
            self.renderStep(step);
        });
    }

    //----------------------------------
});