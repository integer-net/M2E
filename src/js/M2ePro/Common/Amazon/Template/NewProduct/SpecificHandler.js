CommonAmazonTemplateNewProductSpecificHandler = Class.create();
CommonAmazonTemplateNewProductSpecificHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    popups: [],

    //----------------------------------

    initialize: function(M2ePro)
    {
        // ugly hack
        if (version_compare(Prototype.Version,'1.7') < 0) {
            for (var property in Selector.xpath.operators) {
                Selector.xpath.operators[property] = Selector.xpath.operators[property].split('#{3}').join('#{4}');
            }
            Selector.patterns['attr'] = /\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\](?![^\[]*\])(?![^\(]*\))/;
        }
        // -------

        this.TYPE_TEXT = 1;
        this.TYPE_SELECT = 2;
        this.TYPE_CONTAINER = 3;
        this.TYPE_CONTAINER_SELECT = 4;

        this.specificsContainer = $('specifics_container');
        this.specificsMainContainer = $('magento_block_specific_edit_general');

        var self = this;

        Validation.add('M2ePro-container-select-choose', M2ePro.translator.translate('This is a required field. Please press Choose button.'), function(value, el) {
            return el.disabled;
        });

        Validation.add('M2ePro-specifics-validation', M2ePro.translator.translate('Incorrect data.'), function(value, element) {
            if (!element.up('tr').visible()) {
                return true;
            }

            var params = self.specifics[element.getAttribute('specific_id')].params.evalJSON();
            return self[params.type + 'TypeValidator'](value,params,element);
        });

        Validation.add('M2ePro-specificAttributes-validation', M2ePro.translator.translate('Incorrect data.'), function(value, element) {
            if (!element.up('tr').visible()) {
                return true;
            }

            var params = self.specifics[element.getAttribute('specific_id')].params.evalJSON().attributes[element.getAttribute('index')];
            return self[params.type + 'TypeValidator'](value,params,element);
        });
    },

    //----------------------------------

    attribute_sets_confirm: function()
    {
        $$('select.attributes').each(function(el) {
            el.innerHTML = AttributeSetHandlerObj.attrData;
            el.value = '';
        });
    },

    //----------------------------------

    intTypeValidator: function(value,params,element) {

        value = value.replace(',','.');

        if (isNaN(parseInt(value)) ||
            substr_count(value,'.') > 0) {
            return false;
        }

        var validators = {
            'min_value': function(value,restriction)
            {
                return parseInt(value) >= parseInt(restriction);
            },
            'max_value': function(value,restriction)
            {
                return parseInt(value) <= parseInt(restriction);
            },
            'total_digits': function(value,restriction)
            {
                return value.length <= parseInt(restriction);
            }
        };

        for (var paramName in params) {
            if (params.hasOwnProperty(paramName) && validators[paramName]) {
                if (!validators[paramName](value,params[paramName])) {
                    return false;
                }
            }
        }

        return true;
    },

    date_timeTypeValidator: function(value,params,element) {
        return /^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/g.test(value);
    },

    stringTypeValidator: function(value,params,element) {

        var validators = {
            'min_length': function(value,restriction)
            {
                return value.length >= parseInt(restriction);
            },
            'max_length': function(value,restriction)
            {
                return value.length <= parseInt(restriction);
            },
            'pattern': function(value,restriction)
            {
                return value.match(new RegExp('^' + restriction + '$'));
            }
        };

        for (var paramName in params) {
            if (params.hasOwnProperty(paramName) && validators[paramName]) {
                if (!validators[paramName](value,params[paramName])) {
                    return false;
                }
            }
        }

        return true;
    },

    floatTypeValidator: function(value,params,element) {

        value = value.replace(',','.');

        if (isNaN(parseFloat(value)) ||
            substr_count(value,'.') > 1 ||
            value.substr(-1) == '.') {
            return false;
        }

        var validators = {
            'min_value': function(value,restriction)
            {
                return parseFloat(value) >= parseFloat(restriction);
            },
            'max_value': function(value,restriction)
            {
                return parseFloat(value) <= parseFloat(restriction);
            },
            'total_digits': function(value,restriction)
            {
                return value.replace('.','').length <= restriction;
            },
            'decimal_places': function(value,restriction)
            {
                return value.indexOf('.') != -1 ? (value.replace(/\d*\./,'').length <= restriction.value) : true;
            }
        };

        for (var paramName in params) {
            if (params.hasOwnProperty(paramName) && validators[paramName]) {
                if (!validators[paramName](value,params[paramName])) {
                    return false;
                }
            }
        }

        return true;
    },

    undefinedTypeValidator: function() {
        return true;
    },

    //----------------------------------

    run: function(xsd_hash)
    {
        this.xsd_hash = xsd_hash;
        this.parent = {'specific_id': null};
        this.specificsContainer.update();
        this.getSpecifics();
    },

    //----------------------------------

    getSpecifics: function()
    {
        var self = this;

        new Ajax.Request( M2ePro.url.get('adminhtml_common_amazon_template_newProduct/getSpecifics') ,
            {
                method: 'get',
                asynchronous: true,
                parameters: {
                    xsd_hash: self.xsd_hash
            },
            onSuccess: function(transport) {
                try {
                    self.specificsMainContainer.show();
                    self.specifics = transport.responseText.evalJSON();

                    M2ePro.formData.specifics.length > 0
                        ? self.renderSpecificsEditMode()
                        : self.renderSpecifics(self.getChildSpecifics(self.parent),self.specificsContainer);

                } catch (e) {
                    console.log(e.stack);
                }
            }
        });
    },

    //----------------------------------

    renderSpecificsEditMode: function()
    {
        var self = this;

        M2ePro.formData.specifics.each(function(specific) {

            try {
                var dictionarySpecific = self.getDictionarySpecific(specific.xpath);

                // checking if this specific is parent
                if (dictionarySpecific.parent_specific_id == null) {
                    self.renderSpecifics([dictionarySpecific],self.specificsContainer,null,true);

                    return '';
                }

                // checking if this specific is already rendered
                if (self.isAlreadyRendered(specific)) {

                    dictionarySpecific.type == self.TYPE_TEXT && self.setValues(specific);
                    dictionarySpecific.type == self.TYPE_SELECT && self.setValues(specific);

                    return '';
                }

                var orig;

                // checking if this specific was cloned
                orig = dictionarySpecific.max_occurs > 1 && self.getOriginalSpecific(specific);

                if (orig) {
                    orig.parentNode
                        .down('button[add_button_id="' + dictionarySpecific.specific_id + '"]').simulate('click');

                    var origIndex = parseInt(orig.up('tr[index_number]').getAttribute('index_number'));
                    var realXpath = specific.xpath.replace(/\d*$/,origIndex + 1);
                    var specificXpath = specific.xpath;

                    M2ePro.formData.specifics.each(function(tmpSpecific) {
                        tmpSpecific.xpath = tmpSpecific.xpath.replace(specificXpath,realXpath);
                    });

                    specific.xpath = realXpath;

                    dictionarySpecific.type == self.TYPE_TEXT && self.setValues(specific);
                    dictionarySpecific.type == self.TYPE_SELECT && self.setValues(specific);

                    return '';
                }

                self.makeParentXpath(specific);
                self.makeParentId(specific);

                var select;

                // checking if this specific was chosen
                select = self.getChosenSelect(specific);
                if (select) {
                    select.value = dictionarySpecific.specific_id;
                    select.next('button[add_button_id="' + specific.parentId + '"]').simulate('click');

                    dictionarySpecific.type == self.TYPE_TEXT && self.setValues(specific);
                    dictionarySpecific.type == self.TYPE_SELECT && self.setValues(specific);

                    return '';
                }

                // otherwise it was added
                select = self.getAddedSelect(specific);
                select.value = dictionarySpecific.specific_id;
                select.next('button[add_button_id="' + specific.parentId + '"]').simulate('click');

                dictionarySpecific.type == self.TYPE_TEXT && self.setValues(specific);
                dictionarySpecific.type == self.TYPE_SELECT && self.setValues(specific);
            } catch (e) {
                return ''; // continue on fail
            }
        });
    },

    getDictionarySpecific: function(xpath)
    {
        var dictionarySpecific;

        xpath = xpath.replace(/\/\-\d{1,}\//g,'/')
                     .replace(/\-\d*/g,'');

        $H(this.specifics).each(function(specificData) {
            var specific = specificData.pop();
            if (specific.xpath == xpath) {
                dictionarySpecific = specific;
                throw $break;
            }
        });

        return dictionarySpecific;
    },

    isAlreadyRendered: function(specific)
    {
        var prevSpecific = M2ePro.formData.specifics[M2ePro.formData.specifics.indexOf(specific) - 1];

        if (prevSpecific.xpath.indexOf(specific.xpath.replace(/\-\d*$/,'')) == -1) {

            var specificXpath = specific.xpath;
            var realXpath = specific.xpath.replace(/\d*$/,1);

            M2ePro.formData.specifics.each(function(tmpSpecific) {
                tmpSpecific.xpath = tmpSpecific.xpath.replace(specificXpath,realXpath);
            });

            specific.xpath = realXpath;
        }

        return this.specificsContainer.down("*[name='specifics[" + specific.xpath + "][mode]']");
    },

    getOriginalSpecific: function(specific)
    {
        var i = specific.xpath.match(/\d*$/).shift();

        while (i > 1) {
            var originalXpath = specific.xpath.replace(/\d*$/,--i);

            var original = this.specificsContainer.down("*[name='specifics[" + originalXpath + "][mode]']");

            if (original) {
                return original;
            }
        }

        return null;
    },

    makeParentXpath: function(specific)
    {
        specific.parentXpath = specific.xpath.split('/');
        specific.parentXpath.pop();
        specific.parentXpath = specific.parentXpath.join('/');
    },

    makeParentId: function(specific)
    {
        specific.parentId = this.getDictionarySpecific(specific.parentXpath).specific_id;
    },

    getChosenSelect: function(specific)
    {
        try {
            return this.specificsContainer
                .down("*[name='specifics[" + specific.parentXpath + "][mode]']")
                .parentNode.down('select[specific_id="' + specific.parentId + '"]');
        } catch (e) {
           return null;
        }
    },

    getAddedSelect: function(specific)
    {
        try {
            return this.specificsContainer
                .down("*[name='specifics[" + specific.parentXpath + "][mode]']")
                .up('table').down('select[specific_id="' + specific.parentId + '"]');
        } catch (e) {
            specific.parentXpath = specific.parentXpath.split('/');
            specific.parentXpath.pop();
            specific.parentXpath = specific.parentXpath.join('/');
            this.makeParentId(specific);

            return this.specificsContainer
                .down("*[name='specifics[" + specific.parentXpath + "][mode]']")
                .up('table').down('select[specific_id="' + specific.parentId + '"]');
        }
    },

    //----------------------------------

    setValues: function(specific)
    {
        var selectMode = this.specificsContainer.down("*[name='specifics[" + specific.xpath + "][mode]']");
            selectMode.value = specific.mode;
            selectMode.simulate('change');

        var table = selectMode.up('table');
            table.down("*[name='specifics[" + specific.xpath + "][" + specific.mode + "]']")
                 .value = specific[specific.mode];

        specific.attributes.evalJSON().each(function(attribute,attrIndex) {
            for (var attributeName in attribute);

            var selectAttrMode = table.down("*[name='specifics[" + specific.xpath + "][attributes][" + attrIndex + "][" + attributeName + "][mode]']");
                selectAttrMode.value = attribute[attributeName].mode;
                selectAttrMode.simulate('change');

            table.down("*[name='specifics[" + specific.xpath + "][attributes][" + attrIndex + "][" + attributeName + "][" + attribute[attributeName].mode + "]']")
                 .value = attribute[attributeName][attribute[attributeName].mode];
        });
    },

    //----------------------------------

    renderSpecifics: function(specifics,container,renderCallback,force)
    {
        if (specifics.length < 1) {
            return '';
        }

        var self = this;
        renderCallback = renderCallback || self.renderSpecific;

        var tempSpecifics = self.getRequiredAndUnrequiredSpecifics(specifics);

        var requiredSpecifics   = force ? specifics : tempSpecifics.required;
        var unrequiredSpecifics = force ? [] : tempSpecifics.unrequired;

        requiredSpecifics.length > 0 && self.renderRequiredSpecifics(requiredSpecifics,container,renderCallback);
        unrequiredSpecifics.length > 0 && self.renderUnrequiredSpecifics(unrequiredSpecifics,container);
    },

    //----------------------------------

    getRequiredAndUnrequiredSpecifics: function(specifics)
    {
        var requiredSpecifics = [];
        var unrequiredSpecifics = [];

        specifics.each(function(specific) {

            if (specific.min_occurs == 1 ||
                specific.title == 'Product Type') {
                requiredSpecifics.push(specific)
            } else {
                unrequiredSpecifics.push(specific);
            }
        });

        return {'required': requiredSpecifics,
                'unrequired': unrequiredSpecifics};
    },

    //----------------------------------

    renderRequiredSpecifics: function(specifics,container,renderCallback)
    {
        var self = this;

        specifics.each(function(specific) {

            switch (parseInt(specific.type)) {

                case self.TYPE_TEXT:
                    renderCallback.call(self,self.getTextTypeContent,specific,container);
                    break;

                case self.TYPE_SELECT:
                    renderCallback.call(self,self.getSelectTypeContent,specific,container);
                    break;

                case self.TYPE_CONTAINER:
                    self.getContainerTypeContent(specific,container);
                    break;

                case self.TYPE_CONTAINER_SELECT:
                    self.getContainerSelectTypeContent(specific,container);
                    break;
            }
        });
    },

    //----------------------------------

    renderUnrequiredSpecifics: function(specifics,container,renderCallback)
    {
        var self = this;
        var parent_specific_id = specifics[0].parent_specific_id;

        var labelDiv = new Element('div',{'style': 'padding: 15px 0'});
        var label = labelDiv.appendChild(new Element('label',{'style': 'font-weight: bold; font-style: italic'})
                            .insert(M2ePro.translator.translate('Optional Specifics') + ': '));

        var contentDiv = new Element('div',{'style': 'padding: 15px 0'});
        var select = contentDiv.appendChild(new Element('select',{'specific_id': parent_specific_id}));

        select.appendChild(new Element('option',{'style': 'display: none; '}));
        specifics.each(function(specific) {
            var specificTitle = specific.title;
            if (parseInt(specific.data_definition.evalJSON().is_desired)) {
                specificTitle += ' (' + M2ePro.translator.translate('Desired') + ')';
            }
            select.appendChild(new Element('option',{'value': specific.specific_id})).insert(specificTitle);
        });

//        select.observe('change',function() {
//            select.next('button').show();
//        });

        var tr = container.appendChild(new Element('tr'));

        tr.appendChild(new Element('td',{'class': 'label'})).insert(labelDiv);
        tr.appendChild(new Element('td',{'class': 'value'})).insert(contentDiv);

        self.appendAddButton({'specific_id': parent_specific_id},contentDiv,container);
    },

    //----------------------------------

    renderSpecific: function(contentCallback,specific,container)
    {
        container = this.newContainer(container,{
            'tr': {'specific_id': specific.specific_id,'index_number': 1},
            'table': {
                'class': 'form-list',
                'style': 'width: 100%',
                'cellspacing': 0,
                'cellpadding': 0
            }
        });

        specific.container = container;

        this.makePath(specific);
        this.renderHr(container);
        this.renderChooseMode(specific,container);
        this.renderModeCustomValue(specific,container,contentCallback);
        this.renderModeCustomAttribute(specific,container);
        this.renderModeRecommendedValue(specific,container);
        this.renderAttributes(specific,container);

        if (specific.title == 'Parentage') {

            var chooseModeSelect = container.down('tr[mode="none"] select');
            var customValueSelect = container.down('tr[mode="custom_value"] select');

            if (customValueSelect.down('option[value="base-product"]')) {
                chooseModeSelect.value = 'custom_value';
                chooseModeSelect.simulate('change');
                customValueSelect.value = 'base-product';
                container.hide();
            }
        }
    },

    //----------------------------------

    getLabel: function(specific)
    {
        var title = specific.title + ': <span class="required">*</span>';
        var div = new Element('div');

        var label = div.appendChild((new Element('label').insert(title)));

        this.renderHelpIconDataDefinition(specific.data_definition.evalJSON(),specific.title,label);

        return div;
    },

    //----------------------------------

    getTextTypeContent: function(specific)
    {
        var div = new Element('div');
        var params = specific.params.evalJSON();

        if (params.max_length &&  params.max_length >= 100) {
            var textarea = div.appendChild(new Element('textarea',{
                'specific_id': specific.specific_id,
                'name': 'specifics[' + specific.path + "][custom_value]",
                'class': 'M2ePro-required-when-visible M2ePro-specifics-validation',
                'style': 'width: 350px'
            }));
        } else {
            var input = div.appendChild(new Element('input',{
                'specific_id': specific.specific_id,
                'name': 'specifics[' + specific.path + "][custom_value]",
                'type': 'text',
                'class': 'input-text M2ePro-required-when-visible M2ePro-specifics-validation'
            }));

            params.type == 'date_time' && Calendar.setup({
                'inputField': input,
                'ifFormat': "%Y-%m-%d %H:%M:%S",
                'showsTime': true,
                'button': input,
                'align': 'Bl',
                'singleClick' : true
            });
        }

        div.appendChild(new Element('input',{
            'specific_id': specific.specific_id,
            'name': 'specifics[' + specific.path + "][type]",
            'type': 'hidden',
            'value': params.type
        }));

        var temp = specific.data_definition.evalJSON();

        if (!temp.definition) {
            var note = this['get' + ucwords(params.type) + 'TypeNote'](params);

            div.appendChild(new Element('p',{'class': 'note'}))
               .appendChild(new Element('span'))
               .insert(note);
        }

        return div;
    },

    getSelectTypeContent: function(specific)
    {
        var div = new Element('div');

        var select = div.appendChild(new Element('select',{
            'specific_id': specific.specific_id,
            'name': 'specifics[' + specific.path + "][custom_value]",
            'class': 'M2ePro-required-when-visible'
        }));

        select.appendChild(new Element('option',{'style': 'display: none; '}));

        specific.values.evalJSON().each(function(value) {
            var label = value == 'true'  ? 'Yes' :
                       (value == 'false' ? 'No'  : value);

            select.appendChild(new Element('option',{'value': value})).insert(label);
        });

        return div;
    },

    getContainerTypeContent: function(specific,container)
    {
        var self = this;

        container = self.newContainer(container,{
            'tr': {
                'specific_id': specific.specific_id,
                'index_number': 1
            },
            'table': {
                'class': 'form-list',
                'style': specific.parent_specific_id == null ? 'width: 100%' : 'width: 100%; padding: 0; margin-top: 15px; border-right: 1px solid #D6D6D6 !important; border-bottom: 1px solid #D6D6D6 !important; border-left: 1px solid #D6D6D6 !important; margin: 10px 0;',
                'cellspacing': 0,
                'cellpadding': 0
            }
        });

        specific.container = container;

        var div = new Element('div',{
            'style': specific.parent_specific_id == null ? '' : 'padding: 2px 0 2px 10px; color: white; background: #6F8992; border-bottom: 1px solid #D6D6D6 !important'
        });

        specific.parent_specific_id == null && console.log(specific.title);

        if (specific.parent_specific_id != null) {
            div.appendChild(new Element('span',{'style': 'font-weight: bold'}))
                .insert(specific.title);
            self.appendCloneButton(specific,div,container);
            self.appendRemoveButton(specific,div,container);
            div.appendChild(new Element('div',{'style': 'clear: both'}));
        }

        self.makePath(specific);
        div.appendChild(new Element('input',{'type': 'hidden','name': 'specifics[' + specific.path + '][mode]','value':'none'}));

        container.appendChild(new Element('tr'))
                 .appendChild(new Element('td',{'colspan': 2})).insert(div);

        self.renderSpecifics(self.getChildSpecifics(specific),self.newContainer(container,{
            'tr': {},
            'table': {
                'style': 'padding: 7px 20px 20px 20px; width: 100%',
                'class': 'form-list',
                'cellspacing': 0,
                'cellpadding': 0
            }
        }));
    },

    getContainerSelectTypeContent: function(specific,container)
    {
        var self = this;
        var childSpecifics = self.getChildSpecifics(specific);

        var div = new Element('div');
        var isRequired = (specific.min_occurs > 0 || specific.title == 'Product Type');

        var select = div.appendChild(new Element('select',{
            'specific_id': specific.specific_id,
            'class': (isRequired ? 'M2ePro-required-when-visible' : '') + ' M2ePro-container-select-choose'
        }));

        select.appendChild(new Element('option',{'style': 'display: none; '}));

        childSpecifics.each(function(childSpecific) {
            select.appendChild(new Element('option',{'value': childSpecific.specific_id}))
                  .insert(childSpecific.title);
        });

        select.observe('change',function() {
            select.next('button').show();
        });

        container = self.newContainer(container,{
            'tr': {
                'specific_id': specific.specific_id,
                'index_number': 1
            },
            'table': {
                'class': 'form-list',
                'style': 'width: 100%',
                'cellspacing': 0,
                'cellpadding': 0
            }
        });

        specific.container = container;

        self.makePath(specific);
        div.appendChild(new Element('input',{'type': 'hidden','name': 'specifics[' + specific.path + '][mode]','value':'none'}));

        var tr = container.appendChild(new Element('tr'));

        tr.appendChild(new Element('td',{'class': 'label'})).insert(self.getLabel(specific));
        tr.appendChild(new Element('td',{'class': 'value'})).insert(div);

        self.appendChooseButton(specific,div,container);
    },

    //----------------------------------

    getChildSpecifics: function(parent)
    {
        var specifics = [];
        $H(this.specifics).each(function(data) {
            data[1].parent_specific_id == parent.specific_id && specifics.push(data[1]);
        });

        specifics.sort(function(a,b) {

            var aIsDesired = parseInt(a.data_definition.evalJSON().is_desired);
            var bIsDesired = parseInt(b.data_definition.evalJSON().is_desired);

            if (aIsDesired) {
                if (bIsDesired) {
                    return ( ( a.title == b.title ) ? 0 : ( ( a.title > b.title ) ? 1 : -1 ) );
                }
                return -1;
            } else if (bIsDesired) {
                return 1;
            }

            if (a.title == 'Product Type') {
                return -1;
            }
            return ( ( a.title == b.title ) ? 0 : ( ( a.title > b.title ) ? 1 : -1 ) );
        });

        return specifics;
    },

    //----------------------------------

    newContainer: function(oldContainer,attributes)
    {
        return oldContainer
            .appendChild(new Element('tr',attributes.tr))
            .appendChild(new Element('td',{'colspan': 2}))
            .appendChild(new Element('table',attributes.table));
    },

    //----------------------------------

    appendChooseButton: function(specific,div,container)
    {
        var self = this;

        var chooseButton = div.appendChild(new Element('button',{
            'type': 'button',
            'add_button_id': specific.specific_id,
            'class': 'scalable add',
            'style': 'margin-left: 5px; display: none'
        }));
        chooseButton.appendChild(new Element('span')).insert(M2ePro.translator.translate('Choose'));

        chooseButton.observe('click',(function() {

            var newContainer = new Element('table',{'specific_id':specific.specific_id,'class': 'form-list','style': 'margin: 0 0 0 0px; width: 100%','cellspacing': 0,'cellpadding': 0});
            var refTr = div.up('tr');

            refTr.insert({'after': (function() {
                var newTr = new Element('tr');
                newTr.appendChild(new Element('td',{'colspan': 2}))
                     .appendChild(newContainer);
                return newTr;
            })()});

            return function() {
                var select = refTr.down('select[specific_id="' + this.getAttribute('add_button_id') + '"]');
                self.chooseButtonClick(select,newContainer,this);
            }
        })());
    },

    appendAddButton: function(specific,div,container)
    {
        var self = this;

        var addButton = div.appendChild(new Element('button',{
            'type': 'button',
            'add_button_id': specific.specific_id,
            'class': 'scalable add',
            'style': 'margin-left: 5px'
        }));
        addButton.appendChild(new Element('span')).insert(M2ePro.translator.translate('Add'));

        addButton.observe('click',(function() {

            var newContainer = self.newContainer(container,{
                'tr': {},
                'table': {
                    'specific_id': specific.specific_id,
                    'class': 'form-list',
                    'style': 'margin: 0 0 0 0px; width: 100%',
                    'cellspacing': 0,
                    'cellpadding': 0
                }
            });

            return function() {
                var select = container.down('select[specific_id="' + this.getAttribute('add_button_id') + '"]');
                self.addButtonClick(select,newContainer,this);
            }
        })());
    },

    appendCloneButton: function(specific,div,container)
    {
        var self = this;
        var cloneButton = div.appendChild(new Element('button',{
            'type': 'button',
            'add_button_id': specific.specific_id,
            'class': 'scalable add',
            'style': 'margin-left: 5px'
        }));
        cloneButton.appendChild(new Element('span')).insert(M2ePro.translator.translate('Duplicate'));

        cloneButton.observe('click',function() {
            specific.type != self.TYPE_CONTAINER
                ? self.cloneButtonClick(specific,div,container)
                : self.containerCloneButtonClick(specific,div,container);
        });

        var occurs = container.up('table').select('tr[specific_id="' + specific.specific_id + '"]').length;

        occurs >= parseInt(specific.max_occurs) && cloneButton.hide();
    },

    appendRemoveButton: function(specific,div,container)
    {
        var self = this;
        var removeButton = div.appendChild(new Element('button',{
            'type': 'button',
            'remove_button_id': specific.specific_id,
            'class': 'scalable delete',
            'style': 'margin-left: 5px;'
        }));

        if (specific.type == self.TYPE_CONTAINER) {
            removeButton.setStyle({
                'float': 'right',
                'marginRight': '5px'
            })
        }

        removeButton.appendChild(new Element('span')).insert(M2ePro.translator.translate('Delete'));

        removeButton.observe('click',function() {
            self.removeButtonClick(specific,div,container);
        });

        var occurs = container.up('table').select('tr[specific_id="' + specific.specific_id + '"]').length;

        if (specific.min_occurs == 1) {
            occurs == 1 && removeButton.hide();
        }

        specific.title == 'Product Type' && removeButton.hide();
    },

    //----------------------------------

    chooseButtonClick: function(select,container,button)
    {
        button.hide();
        var specific = this.specifics[select.value];
        select.disabled = true;
        this.renderSpecifics([specific],container);
        container.down('button[remove_button_id="' + specific.specific_id + '"]').show();
    },

    addButtonClick: function(select,container,button)
    {
//        button.hide();

        if (!select.value) {
            return;
        }

        var specific = this.specifics[select.value];
        var option = select.down('option[value="' + select.value + '"]');

        option.hide();

        select.firstChild.selected = true;
        this.isAllOptionsHidden(select) && select.writeAttribute('disabled');
        this.renderSpecifics([specific],container,null,true);
    },

    cloneButtonClick: function(specific,div,container)
    {
        var self = this;
        var style = container.getAttribute('style');

        container = container.up('table');
        div.down('button').hide();

//        container.select('button[remove_button_id="' + specific.specific_id + '"]').each(function(button) {
//            button.show();
//        });

        this.renderSpecifics([specific],container,function(contentCallback,specific,container) {

            var refTr = div.up('tr[specific_id="' + specific.specific_id + '"]');
            var newContainer = new Element('table',{
                'class': 'form-list',
                'style': style,
                'cellspacing': 0,
                'cellpadding': 0
            });
            var newTr = new Element('tr',{
                'specific_id': specific.specific_id,
                'index_number': parseInt(refTr.getAttribute('index_number')) + 1
            });

            newTr.appendChild(new Element('td',{'colspan': 2}))
                 .appendChild(newContainer);

            refTr.insert({'after': newTr});

            container = newContainer;

            specific.container = container;

            self.makePath(specific);
            self.renderHr(container);
            self.renderChooseMode(specific,container);
            self.renderModeCustomValue(specific,container,contentCallback);
            self.renderModeCustomAttribute(specific,container);
            self.renderModeRecommendedValue(specific,container);
            self.renderAttributes(specific,container);
        },true);
    },

    containerCloneButtonClick: function(specific,div,container)
    {
        var self  = this;
        var style = container.getAttribute('style');

        container = container.up('table');
        div.down('button').hide();

        container.select('button[remove_button_id="' + specific.specific_id + '"]').each(function(button) {
            button.show();
        });

        var refTr = div.up('tr[specific_id="' + specific.specific_id + '"]');

        var newContainer = new Element('table',{'class': 'form-list','style': style,'cellspacing': 0,'cellpadding': 0});
        var newTr = new Element('tr',{'specific_id': specific.specific_id,'index_number': parseInt(refTr.getAttribute('index_number')) + 1});

        newTr.appendChild(new Element('td',{'colspan': 2}))
             .appendChild(newContainer);

        refTr.insert({'after': newTr});
        container = newContainer;

        specific.container = container;
        self.makePath(specific);

        div = new Element('div',{'style': 'padding: 2px 0 2px 10px; color: white; background: #6F8992; border-bottom: 1px solid #D6D6D6 !important'});
        div.appendChild(new Element('span',{'style': 'font-weight: bold'})).insert(specific.title);
        div.appendChild(new Element('input',{'type': 'hidden','name': 'specifics[' + specific.path + '][mode]','value':'none'}));

        self.appendCloneButton(specific,div,container);
        self.appendRemoveButton(specific,div,container);
        div.appendChild(new Element('div',{'style': 'clear: both'}));

        container.appendChild(new Element('tr')).appendChild(new Element('td',{'colspan': 2})).insert(div);

        self.renderSpecifics(self.getChildSpecifics(specific),self.newContainer(container,{
            'tr': {},
            'table': {'style': 'padding: 7px 20px 20px 20px; width: 100%','class': 'form-list','cellspacing': 0,'cellpadding': 0}
        }));
    },

    removeButtonClick: function(specific,div,container)
    {
        container = container.up('table');

        var parentTr = div.up('tr[specific_id="' + specific.specific_id +'"]');

        var select = container
            .up('table')
            .down('select[specific_id="' + specific.parent_specific_id + '"]');

        select = select || container.up('table').down('select[specific_id="' + this.specifics[specific.parent_specific_id].parent_specific_id + '"]');

        select.removeAttribute('disabled');
        select.firstChild.selected = true;

        parentTr.parentNode.removeChild(parentTr);

        var option = select.down('option[value="' + specific.specific_id + '"]');
        var founds = container.select('tr[specific_id="' + specific.specific_id + '"]');

        if (founds.length == 0) {
            option.show();
        } else if (founds.length == 1 && specific.min_occurs == 1) {
            var found = founds.shift();
            found.down('button[remove_button_id="' + specific.specific_id + '"]').hide();
            found.down('button[add_button_id="' + specific.specific_id + '"]').show();
        } else {
            founds.pop().down('button[add_button_id="' + specific.specific_id + '"]').show();
        }

        this.isAllOptionsHidden(select) || select.removeAttribute('disabled');
    },

    //----------------------------------

    isAllOptionsHidden: function(select)
    {
        var isAllOptionsHidden = true;
        for (var i = 0; i < select.options.length; i++) {
            if (select.options.item(i).style.display == '') {
                isAllOptionsHidden = false;
                break;
            }
        }
        return isAllOptionsHidden;
    },

    //----------------------------------

    makePath: function(specific)
    {
        function makePath(parent,specific) {
            if (!parent) {
                return;
            }

            var parentContainer = parent.container && parent.container.up('tr[specific_id="' + parent.specific_id + '"]');
            var indexNumber = parentContainer
                ? parseInt(parentContainer.getAttribute('index_number'))
                : 1;

            specific.path = '/' + parent.xml_tag  + '-' + indexNumber + specific.path;

            parent && makePath.call(this,parent.parent_specific_id && this.specifics[parent.parent_specific_id],specific);
        }

        specific.path = '';
        makePath.call(this,specific,specific);
    },

    //----------------------------------

    renderChooseMode: function(specific,container)
    {
        var div = new Element('div');
        var select = div.appendChild(new Element('select',{
            'name': 'specifics[' + specific.path + "][mode]",
            'class': 'M2ePro-required-when-visible'
        }));

        select.appendChild(new Element('option',{'style': 'display: none'}));

        var temp = select.appendChild(new Element('option',{'value': 'recommended_value','style': 'display: none'})).insert(M2ePro.translator.translate('Recommended Values'));

        if (specific.recommended_values && specific.recommended_values.evalJSON().length > 0) {
            temp.show();
        }

        select.appendChild(new Element('option',{'value': 'custom_value'})).insert(M2ePro.translator.translate('Custom Value'));
        select.appendChild(new Element('option',{'value': 'custom_attribute'})).insert(M2ePro.translator.translate('Custom Attribute'));

        var tr = container.appendChild(new Element('tr', {
            mode: 'none'
        }));

        tr.appendChild(new Element('td',{'class': 'label'})).insert(this.getLabel(specific));
        tr.appendChild(new Element('td',{'class': 'value'})).insert(div);

        select.observe('change',function() {
            if (this.value == 'custom_value') {
                container.down('tr[mode="custom_value"]').show();
                container.down('tr[mode="custom_attribute"]').hide();
                container.down('tr[mode="recommended_value"]').hide();
            }
            if (this.value == 'custom_attribute') {
                container.down('tr[mode="custom_attribute"]').show();
                container.down('tr[mode="custom_value"]').hide();
                container.down('tr[mode="recommended_value"]').hide();
            }
            if (this.value == 'recommended_value') {
                container.down('tr[mode="recommended_value"]').show();
                container.down('tr[mode="custom_attribute"]').hide();
                container.down('tr[mode="custom_value"]').hide();
            }
        });

        this.appendCloneButton(specific,div,container);
        this.appendRemoveButton(specific,div,container);
    },

    renderModeCustomValue: function(specific,container,contentCallback)
    {
        var tr = container.appendChild(new Element('tr',{'mode':'custom_value','style': 'display: none'}));
        var div = new Element('div');

        div.appendChild(new Element('label')).insert(M2ePro.translator.translate('Custom Value') + ': <span class="required">*</span>');

        tr.appendChild(new Element('td',{'class': 'label'})).insert(div);
        tr.appendChild(new Element('td',{'class': 'value'})).insert(contentCallback.call(this,specific));
    },

    renderModeCustomAttribute: function(specific,container)
    {
        var tr = container.appendChild(new Element('tr',{'mode':'custom_attribute','style': 'display: none'}));
        var div = new Element('div');

        var label = div.appendChild(new Element('label')).insert(M2ePro.translator.translate('Custom Attribute') + ': <span class="required">*</span>');

        var values = specific.values.evalJSON();
        var recommendedValues = specific.recommended_values.evalJSON();

        if (values.length > 0) {
            this.renderHelpIconAllowedValues(values,label);
        } else if (recommendedValues.length > 0) {
            this.renderHelpIconRecommendedValues(recommendedValues,label);
        }

        tr.appendChild(new Element('td',{'class': 'label'})).insert(div);

        tr.appendChild(new Element('td',{'class': 'value'})).insert(this.getMagentoAttributes(
            'specifics[' + specific.path + "][custom_attribute]",
            true
        ));
    },

    renderModeRecommendedValue: function(specific,container)
    {
        var recommendedValues = specific.recommended_values.evalJSON();

        var tr = container.appendChild(new Element('tr',{'mode':'recommended_value','style': 'display: none'}));

        if (recommendedValues.length < 1) {
            return tr.hide();
        }

        var div = new Element('div');

        div.appendChild(new Element('label')).insert(M2ePro.translator.translate('Recommended Values') + ': <span class="required">*</span>');

        tr.appendChild(new Element('td',{'class': 'label'})).insert(div);

        div = new Element('div');

        var select = div.appendChild(new Element('select',{
            'name': 'specifics[' + specific.path + "][recommended_value]",
            'class' : 'M2ePro-required-when-visible'
        }));

        select.appendChild(new Element('option',{'style': 'display: none'}));

        recommendedValues.each(function(value) {
            select.appendChild(new Element('option',{'value': value})).insert(value);
        });

        tr.appendChild(new Element('td',{'class': 'value'})).insert(div);
    },

    renderAttributes: function(specific,container)
    {
        var self = this;
        var attributes = specific.params.evalJSON().attributes;
        if (!attributes) {
            return '';
        }

        attributes.each(function(attribute,index) {
            self.renderAttributeChooseMode(attribute,index,specific,container);
            self.renderAttributesModeCustomValue(attribute,index,specific,container);
            self.renderAttributesModeCustomAttribute(attribute,index,specific,container);
        });
    },

    renderAttributeChooseMode: function(attribute,index,specific,container)
    {
        var div = new Element('div');
        var select = div.appendChild(new Element('select',{
            'class': attribute.required ? 'M2ePro-required-when-visible' : '',
            'name': 'specifics[' + specific.path + "][attributes][" + index + "][" + attribute.title + "][mode]"
        }));

        select.appendChild(new Element('option',{'style': 'display: none'}));
        select.appendChild(new Element('option',{'value': 'custom_value'})).insert(M2ePro.translator.translate('Custom Value'));
        select.appendChild(new Element('option',{'value': 'custom_attribute'})).insert(M2ePro.translator.translate('Custom Attribute'));

        var tr = container.appendChild(new Element('tr'));
        var label = new Element('label').insert(specific.title + ' (' + attribute.title + '): ' + (attribute.required ? '<span class="required">*</span>' : ''));

        this.renderHelpIconDataDefinition(attribute.data_definition,attribute.title,label);

        tr.appendChild(new Element('td',{'class': 'label'})).insert(label);
        tr.appendChild(new Element('td',{'class': 'value'})).insert(div);

        select.observe('change',function() {
            this.value == 'custom_value' && container.down('tr[mode="attribute_custom_value"]').show() && container.down('tr[mode="attribute_custom_attribute"]').hide();
            this.value == 'custom_attribute' && container.down('tr[mode="attribute_custom_attribute"]').show() && container.down('tr[mode="attribute_custom_value"]').hide();
        });
    },

    renderAttributesModeCustomValue: function(attribute,index,specific,container)
    {
        var tr = container.appendChild(new Element('tr',{'mode':'attribute_custom_value','style': 'display: none'}));
        var div = new Element('div');

        div.appendChild(new Element('label')).insert(M2ePro.translator.translate('Custom Value') +  ': ' + (attribute.required ? '<span class="required">*</span>' : ''));

        tr.appendChild(new Element('td',{'class': 'label'})).insert(div);
        tr.appendChild(new Element('td',{'class': 'value'})).insert(this.getAttributeContent(attribute,index,specific));
    },

    renderAttributesModeCustomAttribute: function(attribute,index,specific,container)
    {
        var tr = container.appendChild(new Element('tr',{'mode':'attribute_custom_attribute','style': 'display: none'}));
        var div = new Element('div');

        var label = div.appendChild(new Element('label')).insert(M2ePro.translator.translate('Custom Attribute') + ': ' + (attribute.required ? '<span class="required">*</span>' : ''));

        if (attribute.values) {
            var values = attribute.values.evalJSON();
            values.length > 0 && this.renderHelpIconAllowedValues(values,label);
        }

        tr.appendChild(new Element('td',{'class': 'label'})).insert(div);

        tr.appendChild(new Element('td',{'class': 'value'})).insert(this.getMagentoAttributes(
            'specifics[' + specific.path + "][attributes][" + index + "][" + attribute.title + "][custom_attribute]",
            attribute.required
        ));
    },

    getAttributeContent: function(attribute,index,specific)
    {
        var div = new Element('div');

        if (attribute.values) {
            var select = div.appendChild(new Element('select',{
                'name': 'specifics[' + specific.path + "][attributes][" + index + "][" + attribute.title + "][custom_value]",
                'class': attribute.required ? 'M2ePro-required-when-visible' : ''
            }));
            select.appendChild(new Element('option',{'style': 'display: none'}));
            attribute.values.evalJSON().each(function(value) {
                select.appendChild(new Element('option',{'value': value}).insert(value));
            });
        } else {
            div.appendChild(new Element('input',{
                'specific_id': specific.specific_id,
                'index': index,
                'type': 'text',
                'class': 'input-text M2ePro-specificAttributes-validation' + (attribute.required ? ' M2ePro-required-when-visible' : ''),
                'name': 'specifics[' + specific.path + "][attributes][" + index + "][" + attribute.title + "][custom_value]"
            }));

            if (!attribute.data_definition.definition) {
                var note = this['get' + ucwords(attribute.type) + 'TypeNote'](attribute);

                div.appendChild(new Element('p',{'class': 'note'}))
                   .appendChild(new Element('span'))
                   .insert(note);
            }
        }

        return div;
    },

    //----------------------------------

    renderHr: function(container)
    {
        var prev = container.up('tr').previous();

        if (!prev || !prev.getAttribute('specific_id')) {
            return;
        }

        if (this.specifics[prev.getAttribute('specific_id')].type == this.TYPE_CONTAINER ||
            this.specifics[prev.getAttribute('specific_id')].type == this.TYPE_CONTAINER_SELECT) {
            return;
        }

        container.appendChild(new Element('tr'))
                 .appendChild(new Element('td',{'colspan': 2,'style': 'padding: 15px 0 !important'}))
                 .appendChild(new Element('hr',{'style': 'border: 1px solid silver; border-bottom: none;'}));
    },

    //----------------------------------

    getMagentoAttributes: function(name,isRequired,params,values)
    {
        var container = new Element('div');

        var div    = container.appendChild(new Element('div',{
            'style': 'float: left'
        }));

        var select = div.appendChild(new Element('select',{
            'name': name,
            'class': 'attributes' + (isRequired ? ' M2ePro-required-when-visible' : '')
        }));

        select.insert(AttributeSetHandlerObj.attrData);

        return container;
    },

    //----------------------------------

    renderPopUp: function(container,title,contentCallback,heightCallback,widthCallback)
    {
        container.insert('&nbsp;(');

        var helpIcon = container.appendChild(new Element('a',{
            'href': 'javascript:',
            'title': M2ePro.translator.translate('Help')
        }));

        helpIcon.insert('?');

        container.insert(')');

        var win;
        var self = this;

        helpIcon.observe('click',function() {
            var position = helpIcon.positionedOffset();

            win = win || new Window({
                className: "magento",
                zIndex: 100,
                title: title,
                top: position.top - 30,
                left: position.left + 30
            });

            win.setHTMLContent(contentCallback());

            if (heightCallback) {
                win.height = heightCallback(win);
            } else {
                win.height = 250;
            }

            if (widthCallback) {
                win.width = widthCallback(win);
            } else {
                win.width = 250;
            }

            if (win.visible) {
                win.hide();
            } else {
                self.popups.each(function(popup) {
                    popup.close();
                });
                win.show();
            }

            self.popups = [win];
        });
    },

    renderHelpIconAllowedValues: function(values,container)
    {
        this.renderPopUp(container, M2ePro.translator.translate('Allowed Values') + ': ',function() {
            var winContent = new Element('ul',{'style': 'text-align: center; margin-top: 10px'});

            values.each(function(value) {
                winContent.insert('<li><h4>' + value + '</h4></li>');
            });

            return winContent.outerHTML;
        });
    },

    renderHelpIconRecommendedValues: function(values,container)
    {
        this.renderPopUp(container, M2ePro.translator.translate('Recommended Values') + ': ',function() {
            var winContent = new Element('ul',{'style': 'text-align: center; margin-top: 10px'});

            values.each(function(value) {
                winContent.insert('<li><h4>' + value + '</h4></li>');
            });

            return winContent.outerHTML;
        });
    },

    renderHelpIconDataDefinition: function(dataDefinition,title,container) {
        if (!dataDefinition.definition) {
            return;
        }

        var definition = dataDefinition.definition;
        var tips = dataDefinition.tips;
        var examples = dataDefinition.example;

        this.renderPopUp(container,title + ' ' + M2ePro.translator.translate('Helpful Info:'),function() {
            var winContent = new Element('div');

            winContent.innerHTML += '<div style="padding: 3px 0"></div><h2>' + M2ePro.translator.translate('Definition:') + '</h2>';
            winContent.innerHTML += '<div>' + definition + '</div>';

            if (tips) {
                winContent.innerHTML += '<div style="padding: 5px 0"></div><h2>' + M2ePro.translator.translate('Tips:') + '</h2>';
                winContent.innerHTML += '<div>' + tips + '</div>'
            }
            if (examples) {
                winContent.innerHTML += '<div style="padding: 5px 0"></div><h2>' + M2ePro.translator.translate('Examples:') + '</h2>';
                winContent.innerHTML += '<div>' + examples + '</div>'
            }

            return winContent.outerHTML;
        },function(win) {
            return win.content.firstChild.getStyle('height');
        },function() {
            return 400;
        });
    },

    //----------------------------------

    getUndefinedTypeNote: function(params)
    {
        return M2ePro.translator.translate('Can take any value.');
    },

    getIntTypeNote: function(params)
    {
        var notes = [];

        var handler = {
            'type': function()
            {
                notes[0] = M2ePro.translator.translate('Type: Numeric.') + ' ';
            },
            'min_value': function(restriction)
            {
                notes[1] = M2ePro.translator.translate('Min:') + ' ' + restriction + '. ';
            },
            'max_value': function(restriction)
            {
                notes[2] = M2ePro.translator.translate('Max:') + ' ' + restriction + '. ';
            },
            'total_digits': function(restriction)
            {
                notes[3] = M2ePro.translator.translate('Total digits (not more):') + ' ' + restriction + '. ';
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
            'type': function()
            {
                notes[0] = M2ePro.translator.translate('Type: String.') + ' ';
            },
            'min_length': function(restriction)
            {
                notes[1] = restriction != 1 ? M2ePro.translator.translate('Min length:') + ' ' + restriction : '';
            },
            'max_length': function(restriction)
            {
                notes[2] = M2ePro.translator.translate('Max length:') + ' ' + restriction;
            },
            'pattern': function(restriction)
            {
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

    getDate_timeTypeNote: function(params)
    {
        var notes = [];

        var handler = {
            'type': function(restriction)
            {
                notes.push(M2ePro.translator.translate('Type: Date time. Format: YYYY-MM-DD hh:mm:ss'));
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
            'type': function()
            {
                notes[0] = M2ePro.translator.translate('Type: Numeric floating point.') + ' ';
            },
            'min_value': function(restriction)
            {
                notes[1] = M2ePro.translator.translate('Min:') + ' ' + restriction + '. ';
            },
            'max_value': function(restriction)
            {
                notes[2] = M2ePro.translator.translate('Max:') + ' ' + restriction + '. ';
            },
            'decimal_places': function(restriction)
            {
                notes[3] = M2ePro.translator.translate('Decimal places (not more):') + ' ' + restriction.value + '. ';
            },
            'total_digits': function(restriction)
            {
                notes[4] = M2ePro.translator.translate('Total digits (not more):') + ' ' + restriction + '. ';
            }
        };

        for (var paramName in params) {
            params.hasOwnProperty(paramName) && handler[paramName] && handler[paramName](params[paramName]);
        }

        return notes.join('');
    }

    //----------------------------------

});