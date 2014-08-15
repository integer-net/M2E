AttributeSetHandler = Class.create();
AttributeSetHandler.prototype = {

    attrData: '',

    //----------------------------------

    initialize: function(selectId) {
        this.selectId = selectId || 'attribute_sets';

        Validation.add('M2ePro-validate-attribute-sets', M2ePro.translator.translate('You should select Attribute Set first.'), function(value)
        {
            var confirmedAttributeSets = AttributeSetHandlerObj.getConfirmedAttributeSets();

            if (!confirmedAttributeSets.length) {
                return false;
            }

            var selectedAttributeSets = AttributeSetHandlerObj.getSelectedAttributeSets();

            return confirmedAttributeSets == selectedAttributeSets;
        });
    },

    //----------------------------------

    isAttributeSetsConfirmed: function()
    {
        var self = AttributeSetHandlerObj;
        var confirmedAttributeSets = self.getConfirmedAttributeSets(true);

        return confirmedAttributeSets.length > 0;
    },

    getConfirmedAttributeSets: function(returnAsArray)
    {
        returnAsArray = returnAsArray || false;

        var self = AttributeSetHandlerObj;
        var confirmedAttributeSets = $(self.selectId).readAttribute('confirmed-attribute-sets');

        if (confirmedAttributeSets === null || !confirmedAttributeSets.length) {
            if (returnAsArray) {
                return new Array();
            }
            return '';
        }

        if (returnAsArray) {
            return confirmedAttributeSets.split(',');
        }

        return confirmedAttributeSets;
    },

    getSelectedAttributeSets: function(returnAsArray)
    {
        returnAsArray = returnAsArray || false;

        var self = AttributeSetHandlerObj;
        var selectedAttributeSets = [];

        if (!$$('select#' + AttributeSetHandlerObj.selectId)[0]) {
            //template is locked
            selectedAttributeSets = $(AttributeSetHandlerObj.selectId).value.split(',');
        } else {
            $$('select#' + AttributeSetHandlerObj.selectId + ' option').each(function(obj) {
                if (obj.selected) {
                    selectedAttributeSets.push(obj.value);
                }
            });
        }

        if (returnAsArray) {
            return selectedAttributeSets;
        }
        return selectedAttributeSets.join(',');
    },

    //----------------------------------

    changeAttributeSets: function()
    {
        var self = AttributeSetHandlerObj;

        CommonHandlerObj.hideEmptyOption(self.selectId);
        self.showConfirmButton();
    },

    confirmAttributeSets: function()
    {
        var self = AttributeSetHandlerObj;
        var selectedAttributeSets = self.getSelectedAttributeSets();

        if (selectedAttributeSets.length) {
            $(self.selectId).writeAttribute('confirmed-attribute-sets', selectedAttributeSets);
            self.prepareAttributes(selectedAttributeSets);
            self.hideConfirmButton();
        } else {
            alert(M2ePro.translator.translate('You should select Attribute Set first.'));
        }
    },

    selectAllAttributeSets: function()
    {
        var self = AttributeSetHandlerObj;

        $$('#' + self.selectId + ' option').each(function(obj) {
            if (obj.value != '') {
                obj.selected = true;
            }
        });

        self.changeAttributeSets();
    },

    //----------------------------------

    showConfirmButton: function()
    {
        $(this.selectId + '_confirm_button').show();
    },

    hideConfirmButton: function()
    {
        $(this.selectId + '_confirm_button').hide();
    },

    //----------------------------------

    checkAttributeSetSelection: function()
    {
        if (!this.isAttributeSetsConfirmed()) {
            alert(M2ePro.translator.translate('You should select Attribute Set first.'));
            return false;
        }

        return true;
    },

    //----------------------------------

    appendToText: function(ddId, targetId)
    {
        var suffix = ' #' + $(ddId).value + '#';
        $(targetId).value = $(targetId).value + suffix;
    },

    appendToTextarea: function(v)
    {
        var data;

        if (wysiwygtext.isEnabled()) {
            data = tinyMCE.get('description_template').getContent();
            tinyMCE.get('description_template').setContent(data + ' ' + v);
        } else {
            data = $('description_template').value + ' ' + v;
            $('description_template').value = data;
        }
    },

    //----------------------------------

    checkAttributesSelect: function (id, value)
    {
        if ($(id)) {
            if (typeof M2ePro.formData[id] != 'undefined') {
                $(id).value = M2ePro.formData[id];
            }
            if (value) {
                $(id).value = value;
            }
        }
    },

    prepareAttributes: function(attributeSets)
    {
        var self = this;

        if (attributeSets instanceof Array) {
            attributeSets = attributeSets.join(',');
        }

        new Ajax.Request( M2ePro.url.get('adminhtml_general/magentoGetAttributesByAttributeSets',{'attribute_sets': attributeSets}),
        {
            method: 'post',
            asynchronous : false,
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);

                var cachedOptions = '';
                data.each(function(v) {
                    cachedOptions += '<option value="' + v.code + '">' + v.label + '</option>\n';
                });

                self.attrData = cachedOptions;
            }
        });
    },

    //----------------------------------

    renderAttributes: function (name, insertTo, value, width)
    {
        var style = width ? ' style="width: ' + width + 'px;"' : '';
        var txt = '<select name="' + name + '" id="' + name + '"' + style + '>\n';

        txt += this.attrData;
        txt += '</select>';

        $(insertTo).innerHTML = txt;
        this.checkAttributesSelect(name, value);
    },

    renderAttributesWithEmptyHiddenOption: function (name, insertTo, value, width)
    {
        var style = width ? ' style="width: ' + width + 'px;"' : '';
        var txt = '<select name="' + name + '" id="' + name + '" class="M2ePro-required-when-visible"' + style + '>\n';

        txt += '<option style="display: none;"></option>\n';
        txt += this.attrData;
        txt += '</select>';

        $(insertTo).innerHTML = txt;
        this.checkAttributesSelect(name, value);
    },

    renderAttributesWithEmptyOption: function(name, insertTo, value, notRequiried)
    {
        var className = notRequiried ? '' : ' class="M2ePro-required-when-visible"';
        var txt = '<select name="' + name + '" id="' + name + '" ' + className + '>\n';

        txt += '<option class="empty"></option>\n';
        txt += this.attrData;
        txt += '</select>';

        if ($(insertTo + '_note') != null && $$('#' + insertTo + '_note').length != 0) {
            $(insertTo).innerHTML = txt + $(insertTo + '_note').innerHTML;
        } else {
            $(insertTo).innerHTML = txt;
        }

        this.checkAttributesSelect(name, value);
    }

    //----------------------------------
}