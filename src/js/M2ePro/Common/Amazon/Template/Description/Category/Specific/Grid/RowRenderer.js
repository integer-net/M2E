CommonAmazonTemplateDescriptionCategorySpecificGridRowRenderer = Class.create(CommonAmazonTemplateDescriptionCategorySpecificRenderer, {

    //----------------------------------

    attributeHandler: null,

    // --------------------------------

    process: function()
    {
        if (this.specificHandler.isSpecificRendered(this.indexedXPath) && !this.isValueForceSet()) {
            return '';
        }

        if (!this.load()) {
            return '';
        }

        this.renderParentSpecific();

        if (this.specificHandler.isSpecificRendered(this.indexedXPath) && !this.isValueForceSet()) {
            return '';
        }

        if (this.specificHandler.isSpecificRendered(this.indexedXPath)) {

            this.forceSelectAndDisable(this.getForceSetValue());

            this.hideButton($(this.indexedXPath + '_remove_button'));

            var myEvent = new CustomEvent('undeleteble-specific-appear');
            $(this.getParentIndexedXpath()).dispatchEvent(myEvent);

            return '';
        }

        this.renderSelf();

        if (this.isValueForceSet()) {
            this.forceSelectAndDisable(this.getForceSetValue());
        }

        this.observeToolTips(this.indexedXPath);

        this.checkSelection();
        this.renderSpecificAttributes();
    },

    // --------------------------------

    load: function($super)
    {
        this.attributeHandler = AttributeHandlerObj;
        return $super();
    },

    //###################################

    renderParentSpecific: function()
    {
        if (this.specific.parent_specific_id == null) {
            return '';
        }

        if (!this.dictionaryHelper.isSpecificTypeContainer(this.parentSpecific)) {
            return '';
        }

        var parentBlockRenderer = new CommonAmazonTemplateDescriptionCategorySpecificBlockRenderer();
        parentBlockRenderer.setSpecificsHandler(this.specificHandler);
        parentBlockRenderer.setIndexedXpath(this.getParentIndexedXpath());

        parentBlockRenderer.process();
    },

    renderSelf: function()
    {
        this.renderLabel();
        this.renderChooseMode();
        this.renderValueInputs();

        //-- affects the appearance of the actions buttons
        this.specificHandler.markSpecificAsRendered(this.indexedXPath);

        this.renderButtons();

        //--
        $(this.indexedXPath).observe('my-duplicate-is-rendered', this.onMyDuplicateRendered.bind(this));
        //--

        //-- like grid visibility or view of 'Add Specific' container
        this.throwEventsToParents();
    },

    renderSpecificAttributes: function()
    {
        var self = this;

        if (!this.specific.params.hasOwnProperty('attributes')) {
            return '';
        }

        this.specific.params.attributes.each(function(attribute, index) {

            var renderer = new CommonAmazonTemplateDescriptionCategorySpecificGridRowAttributeRenderer();
            renderer.setSpecificsHandler(self.specificHandler);
            renderer.setIndexedXpath(self.indexedXPath);

            renderer.attribute      = attribute;
            renderer.attributeIndex = index;

            renderer.process();
        });
    },

    //###################################

    renderLabel: function()
    {
        var td = new Element('td');
        var title = this.specific.title;

        if (this.dictionaryHelper.isSpecificRequired(this.specific) || this.isValueForceSet()) {
            title += ' <span class="required">*</span>';
        } else if (this.dictionaryHelper.isSpecificDesired(this.specific)) {
            title += ' <span style="color: grey; font-style: italic;">(' + M2ePro.translator.translate('Desired') + ')</span>';
        }

        td.appendChild((new Element('span').insert(title)));

        var note = this.getDefinitionNote(this.specific.data_definition);
        if (note) {
            var toolTip = this.getToolTipBlock(this.indexedXPath + '_definition_note', note);
            toolTip.show();
            td.appendChild(toolTip);
        }

        var notice = this.getSpecificOverritenNotice();
        if (notice) {
            td.appendChild(notice);
        }

        this.getRowContainer().appendChild(td);
    },

    // --------------------------------

    renderChooseMode: function()
    {
        var select = new Element('select', {
            'id'          : this.indexedXPath +'_mode',
            'indexedxpath': this.indexedXPath,
            'class'       : 'M2ePro-required-when-visible',
            'style'       : 'width: 93.2%;'
        });

        select.appendChild(new Element('option', {'style': 'display: none'}));

        if (this.specific.recommended_values.length > 0) {
            select.appendChild(new Element('option', {'value': this.MODE_RECOMMENDED_VALUE}))
                  .insert(M2ePro.translator.translate('Recommended Values'));
        }

        select.appendChild(new Element('option', {'value': this.MODE_CUSTOM_VALUE}))
              .insert(M2ePro.translator.translate('Custom Value'));

        select.appendChild(new Element('option', {'value': this.MODE_CUSTOM_ATTRIBUTE}))
              .insert(M2ePro.translator.translate('Custom Attribute'));

        select.observe('change', this.onChangeChooseMode.bind(this));
        this.getRowContainer().appendChild(new Element('td')).appendChild(select);
    },

    onChangeChooseMode: function(event)
    {
        var customAttribute     = $(this.indexedXPath + '_' + this.MODE_CUSTOM_ATTRIBUTE),
            customAttributeNote = $(this.indexedXPath + '_custom_attribute_note');

        var customValue     = $(this.indexedXPath + '_' + this.MODE_CUSTOM_VALUE),
            customValueNote = $(this.indexedXPath + '_custom_value_note');

        var recommendedValue = $(this.indexedXPath + '_' + this.MODE_RECOMMENDED_VALUE);

        customAttribute     && customAttribute.hide();
        customAttributeNote && customAttributeNote.hide();

        customValue     && customValue.hide();
        customValueNote && customValueNote.hide();

        recommendedValue && recommendedValue.hide();

        if (event.target.value == this.MODE_CUSTOM_VALUE) {
            customValue     && customValue.show();
            customValueNote && customValueNote.show();
        }
        if (event.target.value == this.MODE_CUSTOM_ATTRIBUTE) {
            customAttribute     && customAttribute.show();
            customAttributeNote && customAttributeNote.show();
        }
        if (event.target.value == this.MODE_RECOMMENDED_VALUE) {
            recommendedValue && recommendedValue.show();
        }
    },

    // --------------------------------

    renderValueInputs: function()
    {
        var td = this.getRowContainer().appendChild(new Element('td'));

        // --
        if (this.dictionaryHelper.isSpecificTypeText(this.specific)) {

            var note = this.getCustomValueTypeNote();
            if (note) td.appendChild(this.getToolTipBlock(this.indexedXPath + '_custom_value_note', note));

            td.appendChild(this.getTextTypeInput());
        }

        if (this.dictionaryHelper.isSpecificTypeSelect(this.specific)) {
            td.appendChild(this.getSelectTypeInput());
        }
        // --

        // --
        note = this.getCustomAttributeTypeNote();
        if (note) td.appendChild(this.getToolTipBlock(this.indexedXPath + '_custom_attribute_note', note));

        td.appendChild(this.getCustomAttributeSelect());
        // --

        td.appendChild(this.getRecommendedValuesSelect());
    },

    // --------------------------------

    getTextTypeInput: function()
    {
        if (this.dictionaryHelper.isSpecificTypeTextArea(this.specific)) {

            var input = new Element('textarea', {
                'id'            : this.indexedXPath +'_'+ this.MODE_CUSTOM_VALUE,
                'indexedxpath'  : this.indexedXPath,
                'specific_id'   : this.specific.specific_id,
                'specific_type' : this.specific.params.type,
                'mode'          : this.MODE_CUSTOM_VALUE,
                'class'         : 'M2ePro-required-when-visible M2ePro-specifics-validation',
                'style'         : 'width: 91.4%; display: none;'
            });

        } else {

            var input = new Element('input', {
                'id'            : this.indexedXPath +'_'+ this.MODE_CUSTOM_VALUE,
                'indexedxpath'  : this.indexedXPath,
                'specific_id'   : this.specific.specific_id,
                'mode'          : this.MODE_CUSTOM_VALUE,
                'specific_type' : this.specific.params.type,
                'type'          : 'text',
                'class'         : 'input-text M2ePro-required-when-visible M2ePro-specifics-validation',
                'style'         : 'display: none; width: 91.4%;'
            });

            this.specific.params.type == 'date_time' && Calendar.setup({
                'inputField': input,
                'ifFormat': "%Y-%m-%d %H:%M:%S",
                'showsTime': true,
                'button': input,
                'align': 'Bl',
                'singleClick': true
            });

            this.specific.params.type == 'date' && Calendar.setup({
                'inputField': input,
                'ifFormat': "%Y-%m-%d",
                'showsTime': true,
                'button': input,
                'align': 'Bl',
                'singleClick': true
            });
        }

        input.observe('change', this.onChangeValue.bind(this));
        return input;
    },

    getSelectTypeInput: function()
    {
        var self = this;

        var select = new Element('select', {
            'id'          : this.indexedXPath +'_'+ this.MODE_CUSTOM_VALUE,
            'indexedxpath': this.indexedXPath,
            'specific_id' : this.specific.specific_id,
            'mode'        : this.MODE_CUSTOM_VALUE,
            'class'       : 'M2ePro-required-when-visible',
            'style'       : 'display: none; width: 93.2%;'
        });

        select.appendChild(new Element('option', {'style': 'display: none;'}));

        var specificOptions = this.specific.values;
        specificOptions.each(function(option) {

            var label = option == 'true' ? 'Yes' : (option == 'false' ? 'No' : option),
                tempOption = new Element('option', {'value': option});

            select.appendChild(tempOption).insert(label);
        });

        select.observe('change', this.onChangeValue.bind(this));
        return select;
    },

    getCustomAttributeSelect: function()
    {
        var select = new Element('select', {
            'id'          : this.indexedXPath +'_'+ this.MODE_CUSTOM_ATTRIBUTE,
            'indexedxpath': this.indexedXPath,
            'specific_id' : this.specific.specific_id,
            'mode'        : this.MODE_CUSTOM_ATTRIBUTE,
            'class'       : 'attributes M2ePro-required-when-visible',
            'style'       : 'display: none; width: 93.2%;'
        });

        select.appendChild(new Element('option', {'style': 'display: none', 'value': ''}));
        this.attributeHandler.availableAttributes.each(function(el) {
            select.appendChild(new Element('option', {'value': el.code})).insert(el.label);
        });
        select.value = '';

        select.observe('change', this.onChangeValue.bind(this));
        return select;
    },

    getRecommendedValuesSelect: function()
    {
        var select = new Element('select', {
            'id'          : this.indexedXPath +'_'+ this.MODE_RECOMMENDED_VALUE,
            'indexedxpath': this.indexedXPath,
            'specific_id' : this.specific.specific_id,
            'mode'        : this.MODE_RECOMMENDED_VALUE,
            'class'       : 'M2ePro-required-when-visible',
            'style'       : 'display: none; width: 93.2%;'
        });

        select.appendChild(new Element('option', {'style': 'display: none', 'value': ''}));
        this.specific.recommended_values.each(function(value) {
            select.appendChild(new Element('option', {'value': value})).insert(value);
        });
        select.value = '';

        select.observe('change', this.onChangeValue.bind(this));
        return select;
    },

    onChangeValue: function(event)
    {
        var selectedObj = {};

        selectedObj['mode'] = event.target.getAttribute('mode');
        selectedObj['type'] = event.target.getAttribute('specific_type');
        selectedObj[selectedObj.mode] = event.target.value;

        this.specificHandler.markSpecificAsSelected(this.indexedXPath, selectedObj);
    },

    // --------------------------------

    renderButtons: function()
    {
        var td = this.getRowContainer().appendChild(new Element('td'));

        var cloneButton = this.getCloneButton();
        if(cloneButton !== null) td.appendChild(cloneButton);

        var removeButton = this.getRemoveButton();
        if(removeButton !== null) td.appendChild(removeButton);
    },

    // --------------------------------

    throwEventsToParents: function()
    {
        var myEvent,
            parentXpath;

        // --
        myEvent = new CustomEvent('child-specific-rendered');
        parentXpath = this.getParentIndexedXpath();

        $(parentXpath + '_grid').dispatchEvent(myEvent);
        $(parentXpath + '_add_row').dispatchEvent(myEvent);
        // --

        // -- my duplicate is already rendered
        this.touchMyNeighbors();
        // -

        // --
        if (this.isValueForceSet()) {

            this.hideButton($(this.indexedXPath + '_remove_button'));

            myEvent = new CustomEvent('undeleteble-specific-appear');
            $(this.getParentIndexedXpath()).dispatchEvent(myEvent);
        }
        // --
    },

    //###################################

    checkSelection: function()
    {
        if (this.specific.values.length == 1) {
            this.forceSelectAndDisable(this.specific.values[0]);
            return '';
        }

        if (!this.specificHandler.isMarkedAsSelected(this.indexedXPath) &&
            !this.specificHandler.isInFormData(this.indexedXPath)) {
            return '';
        }

        var selectionInfo = this.specificHandler.getSelectionInfo(this.indexedXPath);

        var id = this.indexedXPath + '_mode';
        $(id).value = selectionInfo.mode;
        this.simulateAction($(id), 'change');

        if (selectionInfo.mode == this.MODE_CUSTOM_VALUE) {
            id = this.indexedXPath +'_'+ this.MODE_CUSTOM_VALUE;
            $(id).value = selectionInfo['custom_value'];
            this.simulateAction($(id), 'change');
        }

        if (selectionInfo.mode == this.MODE_CUSTOM_ATTRIBUTE) {
            id = this.indexedXPath +'_'+ this.MODE_CUSTOM_ATTRIBUTE;
            $(id).value = selectionInfo['custom_attribute'];
            this.simulateAction($(id), 'change');
        }

        if (selectionInfo.mode == this.MODE_RECOMMENDED_VALUE) {
            id = this.indexedXPath +'_'+ this.MODE_RECOMMENDED_VALUE;
            $(id).value = selectionInfo['recommended_value'];
            this.simulateAction($(id), 'change');
        }
    },

    forceSelectAndDisable: function(value)
    {
        if (!value) {
            return;
        }

        var modeSelect = $(this.indexedXPath + '_mode');
        modeSelect.value = this.MODE_CUSTOM_VALUE;
        this.simulateAction(modeSelect, 'change');
        modeSelect.setAttribute('disabled','disabled');

        var valueObj = $(this.indexedXPath +'_'+ this.MODE_CUSTOM_VALUE);
        valueObj.value = value;
        this.simulateAction(valueObj, 'change');
        valueObj.setAttribute('disabled', 'disabled');
    },

    //###################################

    getToolTipBlock: function(id, messageHtml)
    {
        var container = new Element('div', {
            'id'   : id,
            'style': 'float: right; display: none;'
        });

        container.appendChild(new Element('img', {
            'src'   : M2ePro.url.get('m2epro_skin_url') + '/images/tool-tip-icon.png',
            'class' : 'tool-tip-image'
        }));

        var htmlCont = container.appendChild(new Element('span', {
            'class' : 'tool-tip-message tip-left',
            'style' : 'display: none; max-width: 500px;'
        }));

        htmlCont.appendChild(new Element('img', {
            'src': M2ePro.url.get('m2epro_skin_url') + '/images/help.png'
        }));
        htmlCont.appendChild(new Element('span')).insert(messageHtml);

        return container;
    },

    // --------------------------------

    getCustomValueTypeNote: function()
    {
        if (this.specific.data_definition.definition) {
            return null;
        }

        if (this.specific.params.type == 'int') return this.getIntTypeNote(this.specific.params);
        if (this.specific.params.type == 'float') return this.getFloatTypeNote(this.specific.params);
        if (this.specific.params.type == 'string') return this.getStringTypeNote(this.specific.params);
        if (this.specific.params.type == 'date_time') return this.getDatTimeTypeNote(this.specific.params);

        return this.getAnyTypeNote(this.specific.params);
    },

    getIntTypeNote: function(params)
    {
        var notes = [];

        var handler = {
            'type': function() {
                notes[0] = M2ePro.translator.translate('Type: Numeric.') + ' ';
            },
            'min_value': function(restriction) {
                notes[1] = M2ePro.translator.translate('Min:') + ' ' + restriction + '. ';
            },
            'max_value': function(restriction) {
                notes[2] = M2ePro.translator.translate('Max:') + ' ' + restriction + '. ';
            },
            'total_digits': function(restriction) {
                notes[3] = M2ePro.translator.translate('Total digits (not more):') + ' ' + restriction + '. ';
            }
        };

        for (var paramName in params) {
            params.hasOwnProperty(paramName) && handler[paramName] && handler[paramName](params[paramName]);
        }

        return notes.join('');
    },

    getFloatTypeNote: function(params)
    {
        var notes = [];

        var handler = {
            'type': function() {
                notes[0] = M2ePro.translator.translate('Type: Numeric floating point.') + ' ';
            },
            'min_value': function(restriction) {
                notes[1] = M2ePro.translator.translate('Min:') + ' ' + restriction + '. ';
            },
            'max_value': function(restriction) {
                notes[2] = M2ePro.translator.translate('Max:') + ' ' + restriction + '. ';
            },
            'decimal_places': function(restriction) {
                notes[3] = M2ePro.translator.translate('Decimal places (not more):') + ' ' + restriction.value + '. ';
            },
            'total_digits': function(restriction) {
                notes[4] = M2ePro.translator.translate('Total digits (not more):') + ' ' + restriction + '. ';
            }
        };

        for (var paramName in params) {
            params.hasOwnProperty(paramName) && handler[paramName] && handler[paramName](params[paramName]);
        }

        return notes.join('');
    },

    getStringTypeNote: function(params)
    {
        var notes = [];

        var handler = {
            'type': function() {
                notes[0] = M2ePro.translator.translate('Type: String.') + ' ';
            },
            'min_length': function(restriction) {
                notes[1] = restriction != 1 ? M2ePro.translator.translate('Min length:') + ' ' + restriction : '';
            },
            'max_length': function(restriction) {
                notes[2] = M2ePro.translator.translate('Max length:') + ' ' + restriction;
            },
            'pattern': function(restriction) {
                if (restriction == '[a-zA-Z][a-zA-Z]|unknown') {
                    notes[3] = M2ePro.translator.translate('Two uppercase letters or "unknown".');
                }
            }
        };

        for (var paramName in params) {
            params.hasOwnProperty(paramName) && handler[paramName] && handler[paramName](params[paramName]);
        }

        return notes.join('');
    },

    getDatTimeTypeNote: function(params)
    {
        var notes = [];

        var handler = {
            'type': function(restriction) {
                notes.push(M2ePro.translator.translate('Type: Date time. Format: YYYY-MM-DD hh:mm:ss'));
            }
        };

        for (var paramName in params) {
            params.hasOwnProperty(paramName) && handler[paramName] && handler[paramName](params[paramName]);
        }

        return notes.join('');
    },

    getAnyTypeNote: function(params)
    {
        return M2ePro.translator.translate('Can take any value.');
    },

    // --------------------------------

    getCustomAttributeTypeNote: function()
    {
        if (this.specific.values.length <= 0 && this.specific.recommended_values.length <= 0) {
            return null;
        }

        var span = new Element('span');
        var title = this.specific.values.length > 0 ? M2ePro.translator.translate('Allowed Values') : M2ePro.translator.translate('Recommended Values');

        span.appendChild(new Element('span')).insert('<b>' + title + ': </b>');

        var ul = span.appendChild(new Element('ul'));
        var noteValues = this.specific.values.length > 0 ? this.specific.values : this.specific.recommended_values;

        noteValues.each(function(value) {
            ul.appendChild(new Element('li')).insert(value);
        });

        return span.outerHTML;
    },

    // --------------------------------

    getDefinitionNote: function(definitionPart)
    {
        if (!definitionPart.definition) {
            return;
        }

        var div = new Element('div');

        div.appendChild(new Element('div', {style: 'padding: 2px 0; margin-top: 5px;'}))
            .insert('<b>' + M2ePro.translator.translate('Definition:') + '</b>');
        div.appendChild(new Element('div'))
           .insert(definitionPart.definition);

        if (definitionPart.tips && definitionPart.tips.match(/[a-z0-9]/i)) {

            div.appendChild(new Element('div', {style: 'padding: 2px 0; margin-top: 5px;'}))
                .insert('<b>' + M2ePro.translator.translate('Tips:') + '</b>');
            div.appendChild(new Element('div'))
                .insert(definitionPart.tips);
        }

        if (definitionPart.example && definitionPart.example.match(/[a-z0-9]/i)) {

            div.appendChild(new Element('div', {style: 'padding: 2px 0; margin-top: 5px;'}))
                .insert('<b>' + M2ePro.translator.translate('Examples:') + '</b>');
            div.appendChild(new Element('div'))
                .insert(definitionPart.example);
        }

        return div.outerHTML;
    },

    getSpecificOverritenNotice: function()
    {
        if (!this.specificHandler.canSpecificBeOverwrittenByVariationTheme(this.specific)) {
            return null;
        }

        var container = new Element('div', {
            'style': 'float: right; margin-right: 3px; margin-top: 1px;'
        });

        container.appendChild(new Element('img', {
            'src'   : M2ePro.url.get('m2epro_skin_url') + '/images/warning.png',
            'class' : 'tool-tip-image'
        }));

        var htmlCont = container.appendChild(new Element('span', {
            'class' : 'tool-tip-message tip-left',
            'style' : 'display: none; max-width: 500px; border-color: #ffd967; background-color: #fffbf0;'
        }));

        var variationThemesList = this.specificHandler.themeAttributes[this.specific.xml_tag];
        var message = '<b>' + M2ePro.translator.translate('In case this Description Policy will ' +
            'be used to create New Amazon Child Products, value of this Specific can be automatically' +
            ' overwritten by M2E Pro. Below there is a list of Variation Themes for which the value of' +
            ' this Specific will be overwritten if you are using one of them in your Listing.') + '</b>';
        message += '<br/><br/>' + variationThemesList.join(', ');

        htmlCont.appendChild(new Element('img', {
            'src'   : M2ePro.url.get('m2epro_skin_url') + '/images/i_notice.gif',
            'style' : 'margin-top: -21px;'
        }));
        htmlCont.appendChild(new Element('span')).insert(message);

        return container;
    },

    //###################################

    observeToolTips: function(indexedXpath)
    {
        $$('tr[id="' + indexedXpath + '"] .tool-tip-image').each(function(element) {
            element.observe('mouseover', MagentoFieldTipObj.showToolTip);
            element.observe('mouseout', MagentoFieldTipObj.onToolTipIconMouseLeave);
        });

        $$('tr[id="' + indexedXpath + '"] .tool-tip-message').each(function(element) {
            element.observe('mouseout', MagentoFieldTipObj.onToolTipMouseLeave);
            element.observe('mouseover', MagentoFieldTipObj.onToolTipMouseEnter);
        });
    },

    //###################################

    removeAction: function($super, event)
    {
        // -- for attributes removing
        var myEvent = new CustomEvent('parent-specific-row-is-removed');
        $(this.indexedXPath).dispatchEvent(myEvent);
        // --

        var deleteResult = $super(event);
        this.throwEventsToParents();

        return deleteResult;
    },

    cloneAction: function($super, event)
    {
        var newIndexedXpath = $super(event);
        this.observeToolTips(newIndexedXpath);

        var myEvent = new CustomEvent('parent-specific-row-is-cloned', { 'new_indexed_xpath': newIndexedXpath });
        $(this.indexedXPath).dispatchEvent(myEvent);

        return newIndexedXpath;
    },

    // --------------------------------

    getRowContainer: function()
    {
        if ($(this.indexedXPath)) {
            return $(this.indexedXPath);
        }

        var grid = $$('table[id="'+ this.getParentIndexedXpath() +'_grid"] table.border tbody').first();
        return grid.appendChild(new Element('tr', {id: this.indexedXPath}));
    }

    // --------------------------------
});