BuyTemplateNewProductAttributeHandler = Class.create();
BuyTemplateNewProductAttributeHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    popups: [],

    //----------------------------------

    initialize: function(M2ePro)
    {
        this.TYPE_MULTISELECT = 2;
        this.TYPE_SELECT = 1;
        this.TYPE_INT = 3;
        this.TYPE_DECIMAL = 5;
        this.TYPE_STRING = 4;

        this.TYPE_IS_REQUIRED = 1;

        this.attributesContainer = $('buy_attr_container');

        this.M2ePro = M2ePro;
        var self = this;
        var requiredGroupedSelect = {};
        this.rakutenCategoryId = 0;

        Validation.add('M2ePro-attributes-validation-int', 'Invalid input data. Integer value required.', function(value, element) {
        if (!element.up('tr').visible()) {
            return true;
        }
        return self['intTypeValidator'](value,element);
        });

        Validation.add('M2ePro-attributes-validation-float', 'Invalid input data. Decimal value required. Example 12.05', function(value, element) {
            if (!element.up('tr').visible()) {
                return true;
            }
            return self['floatTypeValidator'](value,element);
        });

        Validation.add('M2ePro-attributes-validation-string', 'Invalid input data. String value required.', function(value, element) {
            if (!element.up('tr').visible()) {
                return true;
            }
            return self['stringTypeValidator'](value,element);
        });

        Validation.add('multi_select_validator', 'This is a required field.', function(value,element) {
            if (!element.up('tr').visible()) {
                return true;
            }
            return self['multiSelectTypeValidator'](value,element);
        });
    },

    //----------------------------------

    intTypeValidator: function(value,element) {

        var pattern = /[^\d+]/;
        if (res = pattern.exec(value) != null) {
            return false;
        }

        value = value.replace(',','.');

        if (isNaN(parseInt(value)) ||
            substr_count(value,'.') > 0) {
            return false;
        }

        return true;
    },

    stringTypeValidator: function(value,element) {
        return true;
    },

    floatTypeValidator: function(value, element) {
        var pattern = /[^\d.]+/;
        if (res = pattern.exec(value) != null) {
            return false;
        }

        if (isNaN(parseFloat(value)) ||
            substr_count(value,'.') > 1 ||
            value.substr(-1) == '.') {
            return false;
        }

        return true;
    },

    requiredGroupTypeValidator: function(value, element, group)
    {
        var countOfSelected = 0;
        var selects = $$('.' + group);

        selects.each(function(select){
            if (select.value != BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE && $(select).value != '') {
                countOfSelected ++;
            }
        });

        if (countOfSelected > 0) {
            return true;
        }
        return false;
    },

    multiSelectTypeValidator: function(value,element)
    {
        if (element.value != '') {
            return true;
        }
        return false;
    },

    //----------------------------------

    getAttributes: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        new Ajax.Request(self.M2ePro.url.getAttributes,
            {
                method : 'get',
                asynchronous : true,
                parameters : {
                    category_id : nodeId
                },
                onSuccess: function(transport) {
                    callback.call(self,transport);
                }
            });
    },

    clearAttributes: function ()
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;
        var trs = self.attributesContainer.childElements();
        for (var i = 0; i < trs.length; i++) {
            trs[i].remove();
        }
    },

    showAttributes: function(categoryId)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;
        var generalAttributesContainer = self.attributesContainer;
        self.rakutenCategoryId = categoryId;
        self.clearAttributes();

        if (categoryId <= 0) {
            var tr = generalAttributesContainer.appendChild(new Element('tr'));
            var td = tr.appendChild(new Element ('td'));
            var label = td.appendChild(new Element ('label')).insert('Select Category first.');
            return;
        }

        new Ajax.Request(BuyTemplateNewProductHandlerObj.M2ePro.url.getAttributes,
            {
                method : 'get',
                asynchronous : true,
                parameters : {
                    category_id : categoryId
                },
                onSuccess: function(transport) {
                    var attributes = transport.responseText.evalJSON();
                    var attributesList = attributes[0].attributes.evalJSON();

                    if (BuyTemplateNewProductHandlerObj.M2ePro.formData.attributes.length > 0) {
                        self.renderAttributes(attributesList,generalAttributesContainer);
                        self.renderAttributesEditMode(BuyTemplateNewProductHandlerObj.M2ePro.formData.attributes);
                    } else {
                        self.renderAttributes(attributesList,generalAttributesContainer);
                    }
                }
            });
    },

    renderAttributesEditMode: function(attributes)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        attributes.each(function(attribute) {

            var select = $('attributes[' + attribute.attribute_name + '][mode]')
                .value = attribute.mode;

            if (attribute.mode == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_RECOMMENDED_VALUE) {
                $('select_' + attribute.attribute_name).show();
                var recommended_value = attribute.recommended_value.evalJSON();
                var options = $$('#recommended_value_' + attribute.attribute_name.replace(/[\s()]/gi,'_') + ' option');
                var len = options.length;

                for (var i = 0; i < len; i++) {
                    recommended_value.each(function(value){
                        if (options[i].value == value) {
                            options[i].selected = true;
                        }
                    });
                }
            } else if (attribute.mode == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_VALUE) {
                $('input_' + attribute.attribute_name).show();
                $('custom_value_' + attribute.attribute_name).value = attribute.custom_value;
            } else if (attribute.mode == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE) {
                $('attribute_' + attribute.attribute_name).show();
                $('custom_attribute_' + attribute.attribute_name).value = attribute.custom_attribute;
            } else {
                console.log(attribute);
            }
            })
    },

    renderAttributes: function(attributes, generalAttributesContainer)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;
        var countOfAttributes = attributes.length;

        if (countOfAttributes > 0) {
            var flag = true;
            var iterations = 0;

            attributes.each(function(attribute) {
                iterations ++;
                var requiredGroupId = '';

                if (attribute.required_group_id != '0' && !(typeof attribute.required_group_id === 'undefined')) {
                    requiredGroupId = attribute.required_group_id;
                    if (flag) {
                        var tr = generalAttributesContainer.appendChild(new Element('tr'));
                        var td = tr.appendChild(new Element ('td',{'colspan': '2','style': 'padding: 15px 0'}));
                        td.appendChild(new Element('label')).insert('<b>At least one of the following attributes must be chosen:</b> <span class="required">*</span>');
                    }
                    flag = false;
                } else {
                    flag = true;
                }

            switch (parseInt(attribute.type)) {

                case self.TYPE_MULTISELECT:

                    var label = self.renderLabels(attribute, generalAttributesContainer);
                    var tr = label.up(1);

                    var td_value = tr.appendChild(new Element('td',{'class': 'value'}));
                    var select = td_value.appendChild(
                        new Element('select',
                            {'name': 'attributes[' + attribute.title + '][mode]',
                            'id': 'attributes[' + attribute.title + '][mode]',
                            'class': 'select attributes required-entry ' + requiredGroupId}));

                    if (attribute.is_required == self.TYPE_IS_REQUIRED) {
                        select.appendChild(new Element('option',{'style': 'display: none; '}));
                    } else {
                        select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE})).insert('None');
                    }
                    select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_RECOMMENDED_VALUE})).insert(self.M2ePro.text.recommended_value);
                    select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_VALUE})).insert(self.M2ePro.text.custom_value);
                    select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE})).insert(self.M2ePro.text.custom_attribute);

                    self.renderTypeMultiSelect(attribute, generalAttributesContainer);
                    label = self.renderInputField(attribute, generalAttributesContainer);
                    self.renderHelpIconAlowedValues(attribute.values, label, 'Multiple values ​​must be separated by comma.');

                    select.observe('change',function(){
                        var multi = $('select_'+ attribute.title);
                        var attrib = $('attribute_' + attribute.title);
                        var input = $('input_'+ attribute.title);

                        if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE) {
                            multi.hide();
                            attrib.hide();
                            input.hide()
                        } else if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_RECOMMENDED_VALUE) {
                            multi.show();
                            attrib.hide();
                            input.hide()
                        } else if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE) {
                            multi.hide();
                            attrib.show();
                            input.hide()
                        } else {
                            multi.hide();
                            attrib.hide();
                            input.show()
                        }
                    });

                    break;

                case self.TYPE_SELECT:

                    var label = self.renderLabels(attribute, generalAttributesContainer);
                    var tr = label.up(1);

                    var td_value = tr.appendChild(new Element('td',{'class': 'value'}));
                    var select = td_value.appendChild(
                        new Element('select',
                            {'name': 'attributes[' + attribute.title + '][mode]',
                            'id': 'attributes[' + attribute.title + '][mode]',
                            'class': 'select attributes required-entry ' + requiredGroupId}));

                    if (attribute.is_required == self.TYPE_IS_REQUIRED) {
                        select.appendChild(new Element('option',{'style': 'display: none; '}));
                    } else {
                        select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE})).insert('None');
                    }
                    select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_RECOMMENDED_VALUE})).insert(self.M2ePro.text.recommended_value);
                    select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_VALUE})).insert(self.M2ePro.text.custom_value);
                    select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE})).insert(self.M2ePro.text.custom_attribute);

                    self.renderTypeSelect(attribute, generalAttributesContainer);
                    label = self.renderInputField(attribute, generalAttributesContainer);
                    self.renderHelpIconAlowedValues(attribute.values, label, '')

                    select.observe('change',function(){
                        var sel = $('select_'+ attribute.title);
                        var attrib = $('attribute_' + attribute.title);
                        var input = $('input_'+ attribute.title);

                        if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE) {
                            sel.hide();
                            attrib.hide();
                            input.hide();
                        } else if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_RECOMMENDED_VALUE) {
                            sel.show();
                            attrib.hide();
                            input.hide();
                        } else if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE) {
                            sel.hide();
                            attrib.show();
                            input.hide();
                        } else {
                            sel.hide();
                            attrib.hide();
                            input.show();
                        }
                    });

                    break;

                case self.TYPE_INT:

                    var dataDefinition = {};

                    dataDefinition.definition = 'Any integer value';
                    dataDefinition.tips = '';
                    dataDefinition.example = '33';

                    var label = self.renderTypeCustom(attribute, generalAttributesContainer,dataDefinition, requiredGroupId);
                    break;

                case self.TYPE_DECIMAL:

                    var dataDefinition = {};

                    dataDefinition.definition = 'Any decimal value';
                    dataDefinition.tips = '';
                    dataDefinition.example = '10.99';

                    var label = self.renderTypeCustom(attribute, generalAttributesContainer,dataDefinition, requiredGroupId);
                    break;

                case self.TYPE_STRING:

                    var dataDefinition = {};
                    dataDefinition.definition = 'Any string value';
                    dataDefinition.tips = '';
                    dataDefinition.example = 'Red, Small, Long, Male, XXL';

                    var label = self.renderTypeCustom(attribute, generalAttributesContainer,dataDefinition, requiredGroupId);
                    break;

                default:
                    self.renderDefaultNoType(attribute, generalAttributesContainer);
                    break;
            }

            if (requiredGroupId != '') {
                Validation.add(requiredGroupId, 'At least one of these attributes is required.', function(value, element) {
                    return self['requiredGroupTypeValidator'](value,element,requiredGroupId);
                });
            } else {
                if (iterations < countOfAttributes) {
                    var line = self.renderLine(generalAttributesContainer);
                }
            }
        });
        }
    },

    renderTypeMultiSelect: function(attribute, generalAttributesContainer)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;
        var tr = generalAttributesContainer.appendChild(new Element('tr',{'id': 'select_' + attribute.title,'style': 'display: none;'}));
        var td_multi = tr.appendChild(new Element('td',{'class': 'label'}));
        var label = td_multi.appendChild(new Element('label')).insert(self.M2ePro.text.recommended_value + '<span class="required">*</span> : ');
        var td_value = tr.appendChild(new Element('td',{'class': 'value'}));

        var recommended_values = td_value.appendChild(new Element('select',
            {'multiple': 'multiple',
                'name': 'attributes[' + attribute.title + '][recommended_value][]',
                'id': 'recommended_value_' + attribute.title.replace(/[\s()]/gi,'_'),
                'class': 'select multi_select_validator',
                'style': 'width: 280px; height: 150px'}));
        var values = attribute.values.evalJSON();
        values.each(function(value){
            recommended_values.appendChild(new Element('option',{'value': value})).insert(value);
        })

        // -- custom attributes
        var label = self.renderMagentoAttributes(attribute, generalAttributesContainer);
        self.renderHelpIconAlowedValues(attribute.values, label,'Multiple values ​​must be separated by comma.')
    },

    renderTypeSelect: function (attribute, generalAttributesContainer)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var tr = generalAttributesContainer.appendChild(new Element('tr',{'id': 'select_' + attribute.title,'style': 'display: none;'}));
        var td_multi = tr.appendChild(new Element('td',{'class': 'label'}));
        var label = td_multi.appendChild(new Element('label')).insert(self.M2ePro.text.recommended_value + '<span class="required">*</span> : ');
        var td_value = tr.appendChild(new Element('td',{'class': 'value'}));

        var recommended_values = td_value.appendChild(new Element('select',
            {'name': 'attributes[' + attribute.title + '][recommended_value][]',
            'id': 'recommended_value_' + attribute.title.replace(/[\s()]/gi,'_'),
            'class': 'select M2ePro-required-when-visible',
            'style': 'width: 280px'}));

        var values = attribute.values.evalJSON();
        values.each(function(value){
            recommended_values.appendChild(new Element('option',{'value': value})).insert(value);
        })

        // -- custom attributes
        var label = self.renderMagentoAttributes(attribute, generalAttributesContainer);
        self.renderHelpIconAlowedValues(attribute.values, label,'')
    },

    renderTypeCustom: function(attribute, generalAttributesContainer, dataDefinition, requiredGroupId)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var label = self.renderLabels(attribute, generalAttributesContainer);
        var tr = label.up(1);

        var td_value = tr.appendChild(new Element('td',{'class': 'value'}));
        var select = td_value.appendChild(
            new Element('select',
                {'name': 'attributes[' + attribute.title + '][mode]',
                 'id': 'attributes[' + attribute.title + '][mode]',
                 'class': 'select attributes required-entry ' + requiredGroupId}));

        if (attribute.is_required == self.TYPE_IS_REQUIRED) {
            select.appendChild(new Element('option',{'style': 'display: none; '}));
        } else {
            select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE})).insert('None');
        }
        select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_VALUE})).insert(self.M2ePro.text.custom_value);
        select.appendChild(new Element('option',{'value': BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE})).insert(self.M2ePro.text.custom_attribute);

        var tr = generalAttributesContainer.appendChild(new Element('tr',{'id': 'input_' + attribute.title,'style': 'display: none;'}));
        var td_multi = tr.appendChild(new Element('td',{'class': 'label'}));
        var label = td_multi.appendChild(new Element('label')).insert(self.M2ePro.text.custom_value + '<span class="required">*</span> : ');

        self.renderHelpIconDataDefinition(dataDefinition,attribute.title,label);

        var td_value = tr.appendChild(new Element('td',{'class': 'value'}));

        // -- Validator
        var className = null;
        if (attribute.type == self.TYPE_INT) {
            className = 'M2ePro-attributes-validation-int';

        } else if (attribute.type == self.TYPE_DECIMAL) {
            className = 'M2ePro-attributes-validation-float';

        } else if (attribute.type == self.TYPE_STRING) {
            className = 'M2ePro-attributes-validation-string';
        }
        // --

        var input = td_value.appendChild(new Element('input',{
            'id': 'custom_value_' + attribute.title,
            'name': 'attributes[' + attribute.title + '][custom_value]',
            'type': 'text',
            'class': 'input-text M2ePro-required-when-visible ' + className}));

        select.observe('change',function(){
            var input = $('input_'+ attribute.title);
            var attrib = $('attribute_' + attribute.title);

            if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_NONE) {
                input.hide();
                attrib.hide();
            } else if (this.value == BuyTemplateNewProductHandlerObj.ATTRIBUTE_MODE_CUSTOM_VALUE) {
                input.show();
                attrib.hide();
            } else {
                input.hide();
                attrib.show();
            }
        });

        // -- custom attributes
        var attributesLabel = self.renderMagentoAttributes(attribute, generalAttributesContainer);
        self.renderHelpIconDataDefinition(dataDefinition,attribute.title,attributesLabel);
    },

    //---------------------------------------

    renderLabels: function(attribute, generalAttributesContainer)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var tr = generalAttributesContainer.appendChild(new Element('tr'));
        var td = tr.appendChild(new Element('td',{'class': 'label'}));
        var label = td.appendChild(new Element('label')).insert(attribute.title + ': ' + (attribute.is_required == self.TYPE_IS_REQUIRED ? '<span class="required">*</span>' : ''));
        return label;
    },

    renderInputField: function(attribute, generalAttributesContainer)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var tr = generalAttributesContainer.appendChild(new Element('tr',{'id': 'input_' + attribute.title,'style': 'display: none;'}));
        var td_multi = tr.appendChild(new Element('td',{'class': 'label'}));
        var label = td_multi.appendChild(new Element('label')).insert(self.M2ePro.text.custom_value + '<span class="required">*</span> : ');
        var td_value = tr.appendChild(new Element('td',{'class': 'value'}));

        var input = td_value.appendChild(new Element('input',{'id': 'custom_value_' + attribute.title,
            'name': 'attributes[' + attribute.title + '][custom_value]',
            'type': 'text',
            'class': 'input-text M2ePro-required-when-visible'}));

        return label;
    },

    //---------------------------------------

    renderDefaultNoType: function (attribute, generalAttributesContainer)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        generalAttributesContainer
            .appendChild(new Element('tr'))
            .appendChild(new Element('td'))
            .update('The category does not have attributes.');
    },

    renderLine: function(container)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var tr = container.appendChild(new Element('tr'));
        var td = tr.appendChild(new Element ('td',{'colspan': '2','style': 'padding: 15px 0'}));
        td.appendChild(new Element('hr',{'style': 'border: 1px solid silver; border-bottom: none;'}));
    },

    //----------------------------------------------

    renderMagentoAttributes: function(attribute, container)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var tr = container.appendChild(new Element('tr',{'id': 'attribute_' + attribute.title,'style': 'display: none;'}));
        var td = tr.appendChild(new Element('td',{'class': 'label'}));
        var label = td.appendChild(new Element('label')).insert(self.M2ePro.text.custom_attribute + '<span class="required">*</span> : ');
        var td = tr.appendChild(new Element('td',{'class': 'value'}));

        var select = td.appendChild(new Element('select',{
            'id': 'custom_attribute_' + attribute.title,
            'name': 'attributes[' + attribute.title + '][custom_attribute]',
            'class': 'attributes M2ePro-required-when-visible select',
            'style': 'width: 280px'
        }));

        select.insert('<option style="display: none;"></option>\n' + BuyTemplateNewProductHandlerObj.attributeSetHandler.attrData);

        return label;
    },

    //----------------------------------------------

    renderHelpIconDataDefinition: function(dataDefinition,title,container) {
        if (!dataDefinition.definition) {
            return;
        }

        var definition = dataDefinition.definition;
        var tips = dataDefinition.tips;
        var examples = dataDefinition.example;

        container.insert('&nbsp;(');

        var helpIcon = container.appendChild(new Element('a',{
            'href': 'javascript:',
            'title': 'Help'
        }));

        helpIcon.insert('?');

        container.insert(')');

        var win;
        var self = this;

        helpIcon.observe('click',function() {
            var position = helpIcon.positionedOffset();

            var winContent = new Element('div');

            winContent.innerHTML += '<div style="padding: 3px 0"></div><h2>' + self.M2ePro.text.definition + '</h2>';
            winContent.innerHTML += '<div>' + definition + '</div>';

            if (tips) {
                winContent.innerHTML += '<div style="padding: 5px 0"></div><h2>' + self.M2ePro.text.tips + '</h2>';
                winContent.innerHTML += '<div>' + tips + '</div>'
            }
            if (examples) {
                winContent.innerHTML += '<div style="padding: 5px 0"></div><h2>' + self.M2ePro.text.examples + '</h2>';
                winContent.innerHTML += '<div>' + examples + '</div>'
            }

            win = win || new Window({
                className: "magento",
                zIndex: 100,
                title: title + ' ' + self.M2ePro.text.helpful_info,
                width: 400,
                top: position.top - 30,
                left: position.left + 30
            });

            win.setHTMLContent(winContent.outerHTML);

            win.height = win.content.firstChild.getStyle('height');

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

    renderHelpIconAlowedValues: function(values,container,notes)
    {
        container.insert('&nbsp;(');

        var helpIcon = container.appendChild(new Element('a',{
            'href': 'javascript:',
            'title': 'Help'
        }));

        helpIcon.insert('?');

        container.insert(')');

        var win;
        var self = this;
        var notesHeight = 20;

        helpIcon.observe('click',function() {
            var position = helpIcon.positionedOffset()

            win = win || new Window({
                className: "magento",
                zIndex: 100,
                title: self.M2ePro.text.allowed_values,
                top: position.top - 200,
                left: position.left + 30,
                width: 350
            });

            var winContent = new Element('ul',{'style': 'text-align: center; margin-top: 10px'});

            var valuesIn = values.evalJSON();
            valuesIn.each(function(value) {
                winContent.insert('<li><p>' + value + '</p></li>');
            });

            if (notes.length > 0) {
                winContent.innerHTML += '<div style="padding: 5px 0"></div><h3>Notes:</h3>';
                winContent.innerHTML += '<div style="text-align: center"><h4>' + notes + '</h4></div>'
                notesHeight = 100;
            }

            win.setHTMLContent(winContent.outerHTML);

            if (valuesIn.length * 20 + 100 < 300) {
                win.height = valuesIn.length * 20 + notesHeight;
            } else {
                win.height = 300;
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
    }

    //----------------------------------------------
});