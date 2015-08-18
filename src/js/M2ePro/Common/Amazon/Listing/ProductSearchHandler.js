CommonAmazonListingProductSearchHandler = Class.create(ActionHandler, {

    //----------------------------------

    initialize: function($super,gridHandler)
    {
        var self = this;

        $super(gridHandler);

        self.searchBlock = $('productSearch_pop_up_content').outerHTML;
        $('productSearch_pop_up_content').remove();
    },

    initMenuEvents: function()
    {
        $('productSearchMenu_cancel_button').observe('click', function() {
            popUp.close();
        });
    },

    initSearchEvents: function()
    {
        var self = this;

        $('productSearch_cancel_button').observe('click',function() {
            popUp.close();
        });

        $('productSearch_submit_button').observe('click',function(event) {
            self.searchGeneralIdManual(self.params.productId);
        });

        $('productSearch_reset_button').observe('click',function(event) {
            $('query').value = '';
            $('productSearch_grid').innerHTML = '';
        });

        $('productSearch_back_button').observe('click',function(event) {
            popUp.close();
            self.openPopUp(0, self.params.title, self.params.productId);
        });

        $('query').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.searchGeneralIdManual(self.params.productId);
        });
    },

    //----------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options,options);
        return this;
    },

    //----------------------------------

    params: {autoMapErrorFlag: false},

    //----------------------------------

    openPopUp: function(mode, title, productId, errorMsg)
    {
        MagentoMessageObj.clearAll();

        var self = this;

        this.gridHandler.unselectAll();

        this.params = {
            mode: mode,
            title: title,
            productId: productId,
            size_menu: {
                width: 535,
                height: (typeof errorMsg == 'undefined') ? 340 : 400
            },
            size_main: {
                width: 750,
                height: 510
            },
            autoMapErrorFlag: false
        };

        popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 70,
            width: (mode ==0) ? this.params.size_menu.width : this.params.size_main.width,
            height: (mode ==0) ? this.params.size_menu.height : this.params.size_main.height,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        popUp.options.destroyOnClose = true;

        if (mode == 0) {
            new Ajax.Request(self.options.url.getSearchAsinMenu, {
                method: 'post',
                parameters: {
                    product_id: productId
                },
                onSuccess: function(transport) {
                    $('modal_dialog_message').insert(transport.responseText);
                    self.initMenuEvents();
                    $('productSearchMenu_error_block').hide();
                    if (errorMsg != undefined) {
                        $('productSearchMenu_error_message').update(errorMsg);
                        $('productSearchMenu_error_block').show();
                    }

                    self.autoHeightFix();
                }
            });
        } else {
            $('modal_dialog_message').insert(self.searchBlock);
            $('productSearch_pop_up_content').show();
            $('productSearch_form').hide();
            $('productSearch_back_button').hide();
            $('productSearch_cleanSuggest_button').show();
            $('suggested_asin_grid_help_block').show();

            $('productSearch_buttons').show();
            new Ajax.Request(self.options.url.suggestedAsinGrid, {
                method: 'post',
                parameters: {
                    product_id: productId
                },
                onSuccess: function(transport) {
                    $('productSearch_grid').update(transport.responseText);
                    $('productSearch_cancel_button').observe('click',function() {
                        popUp.close();
                    });

                    self.autoHeightFix();
                }
            });
        };

    },

    //----------------------------------

    clearSearchResultsAndOpenSearchMenu: function() {
        var self = this;

        if (confirm(self.options.text.confirm)) {
            popUp.close();
            self.unmapFromGeneralId(self.params.productId, function() {
                self.openPopUp(0, self.params.title, self.params.productId);
            });
        }
    },

    //----------------------------------

    clearSearchResultsAndManualSearch: function() {
        var self = this;

        popUp.close();
        self.unmapFromGeneralId(self.params.productId, function() {
            self.showSearchManualPrompt(self.params.title, self.params.productId);
        });
    },

    //----------------------------------

    showSearchManualPrompt: function(title, listingProductId)
    {
        var self = this,
            title = title || self.params.title;

        if(typeof popUp != 'undefined') {
            popUp.close();
        }

        if (listingProductId) {
            this.params = {
                mode: 0,
                title: title,
                productId: listingProductId,
                size_menu: {
                    width: 535,
                    height: 400
                },
                size_main: {
                    width: 750,
                    height: 500
                },
                autoMapErrorFlag: false
            };
        }

        popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 100,
            width: this.params.size_main.width,
            height: this.params.size_main.height,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        popUp.options.destroyOnClose = true;

        $('modal_dialog_message').insert(self.searchBlock);
        $('productSearch_pop_up_content').show();
        self.initSearchEvents();
        //search manual
        $('productSearch_form').show();
        $('productSearch_back_button').show();
        $('productSearch_buttons').show();
        $('productSearch_error_block').hide();
        $('productSearch_cleanSuggest_button').hide();
        $('suggested_asin_grid_help_block').hide();
        $('query').value = '';

        if (listingProductId) {
            $('productSearch_back_button').hide();
        }

        setTimeout(function() {$('query').focus();},250);

        self.autoHeightFix();
    },

    showSearchGeneralIdAutoPrompt: function()
    {
        if (confirm(this.options.text.confirm)) {
            popUp.close();
            this.searchGeneralIdAuto(this.params.productId);
        }
    },

    showUnmapFromGeneralIdPrompt: function(productId)
    {
        MagentoMessageObj.clearAll();
        var self = this;

        if (confirm(self.options.text.confirm)) {
            this.unmapFromGeneralId(productId);
        }
    },

    addNewGeneralId: function(listingProductIds)
    {
        var self = this;

        if (!self.options.customData.isNewAsinAvailable) {
            return alert(self.options.text.new_asin_not_available.replace('%code%',self.options.customData.marketplace.code));
        }

        listingProductIds = listingProductIds || self.params.productId;

        new Ajax.Request(self.options.url.mapToNewAsin, {
            method: 'post',
            parameters: {
                products_ids: listingProductIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if(typeof popUp != 'undefined') {
                    popUp.close();
                }

                self.gridHandler.unselectAllAndReload();
                self.flagSuccess = true;

                if(response.products_ids.length > 0) {
                    ListingGridHandlerObj.templateDescriptionHandler.openPopUp(
                        0, self.options.text.templateDescriptionPopupTitle,
                        response.products_ids, response.data, 1);
                } else {
                    if(response.messages.length > 0) {
                        MagentoMessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }
                }
            }
        });
    },

    //----------------------------------

    searchGeneralIdManual: function(productId)
    {
        var self = this;
        var query = $('query').value;

        MagentoMessageObj.clearAll();

        if (query == '') {
            $('query').focus();
            alert(self.options.text.enter_productSearch_query);
            return;
        }

        $('productSearch_error_block').hide();
        new Ajax.Request(self.options.url.searchAsinManual, {
            method: 'post',
            parameters: {
                query: query,
                product_id: productId
            },
            onSuccess: function(transport) {

                transport = transport.responseText.evalJSON();

                if(transport.result == 'success') {
                    $('productSearch_grid').update(transport.data);
                } else {
                    $('productSearch_error_message').update(transport.data);
                    $('productSearch_error_block').show();
                }
            }
        });
    },

    searchGeneralIdAuto: function(productIds)
    {
        MagentoMessageObj.clearAll();
        var self = this;

        var selectedProductsString =  productIds.toString();
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            return;
        }

        var maxProductsInPart = 10;

        var result = new Array();
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        var selectedProductsParts = result;

        ListingProgressBarObj.reset();
        ListingProgressBarObj.show(self.options.text.automap_asin_progress_title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.params.autoMapErrorFlag = false;

        self.sendPartsOfProducts(selectedProductsParts, selectedProductsParts.length, selectedProductsString);
    },

    sendPartsOfProducts: function(parts, totalPartsCount, selectedProductsString)
    {
        var self = this;

        if (parts.length == 0) {

            ListingProgressBarObj.setStatus(self.options.text.task_completed_message);

            GridWrapperObj.unlock();
            $('loading-mask').setStyle({visibility: 'visible'});

            self.gridHandler.unselectAllAndReload();

            if (self.params.autoMapErrorFlag == true) {
                MagentoMessageObj.addError(self.options.text.automap_error_message);
            }

            setTimeout(function() {
                ListingProgressBarObj.hide();
                ListingProgressBarObj.reset();
            }, 2000);

            new Ajax.Request(self.options.url.getProductsSearchStatus, {
                method: 'post',
                parameters: {
                    products_ids: selectedProductsString
                },
                onSuccess: function(transport) {
                    if (!transport.responseText.isJSON()) {
                        alert(transport.responseText);
                        return;
                    }

                    var response = transport.responseText.evalJSON();

                    if (response.messages) {
                        MagentoMessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }
                }
            });

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = part.length;
        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, self.options.text.automap_asin_search_products));

        new Ajax.Request(self.options.url.searchAsinAuto, {
            method: 'post',
            parameters: {
                products_ids: partString
            },
            onSuccess: function(transport) {

                if (transport.responseText == 1) {
                    self.params.autoMapErrorFlag = true;
                }

                var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100,0);
                } else {
                    ListingProgressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsOfProducts(parts, totalPartsCount, selectedProductsString);
                },500);
            }
        });
    },

    //----------------------------------

    mapToGeneralId: function(productId, generalId, optionsData)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        if(optionsData === undefined) {
            optionsData = '';
        }

        new Ajax.Request(self.options.url.mapToAsin, {
            method: 'post',
            parameters: {
                product_id: productId,
                general_id: generalId,
                options_data: decodeURIComponent(optionsData),
                search_type: $('amazon_asin_search_type').value,
                search_value: $('amazon_asin_search_value').value
            },
            onSuccess: function(transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();

                    if (response['vocabulary_attributes']) {
                        self.openVocabularyAttributesPopUp(response['vocabulary_attributes']);
                    }

                    if (response['vocabulary_attribute_options']) {
                        self.openVocabularyOptionsPopUp(response['vocabulary_attribute_options']);
                    }

                    self.gridHandler.unselectAllAndReload();
                    return;
                }

                if (transport.responseText == 0) {
                    self.gridHandler.unselectAllAndReload();
                } else {
                    alert(transport.responseText);
                }
            }
        });

        popUp.close();
    },

    openVocabularyAttributesPopUp: function (attributes)
    {
        var attributesHtml = '';
        $H(attributes).each(function(element) {
            attributesHtml += '<li>'+element.key+' > '+element.value+'</li>';
        });

        attributesHtml = '<ul>'+attributesHtml+'</ul>';
        var vocabularyPopUpHtml = str_replace('%attributes%', attributesHtml, $('vocabulary_attributes_pupup_template').innerHTML);

        vocabularyAttributesPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Remember Attributes Accordance',
            top: 5,
            width: 400,
            height: 220,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                this.reloadSettings();
                this.reloadVariationsGrid();
            }.bind(this)
        });
        vocabularyAttributesPopUp.options.destroyOnClose = true;

        $('modal_dialog_message').update(vocabularyPopUpHtml);

        $('vocabulary_attributes_data').value = Object.toJSON(attributes);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    addAttributesToVocabulary: function(needAdd)
    {
        var self = this;

        var isRemember = $('vocabulary_attributes_remember_checkbox').checked;

        if (!needAdd && !isRemember) {
            Windows.getFocusedWindow().close();
            return;
        }

        new Ajax.Request(self.options.url.addAttributesToVocabulary, {
            method: 'post',
            parameters: {
                attributes : $('vocabulary_attributes_data').value,
                need_add:    needAdd ? 1 : 0,
                is_remember: isRemember ? 1 : 0
            },
            onSuccess: function (transport) {
                vocabularyAttributesPopUp.close();
            }
        });
    },

    openVocabularyOptionsPopUp: function (options)
    {
        vocabularyOptionsPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Remember Options Accordance',
            top: 15,
            width: 400,
            height: 220,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                this.reloadSettings();
                this.reloadVariationsGrid();
            }.bind(this)
        });
        vocabularyOptionsPopUp.options.destroyOnClose = true;

        $('vocabulary_options_data').value = Object.toJSON(options);

        var optionsHtml = '';
        $H(options).each(function(element) {

            var valuesHtml = '';
            $H(element.value).each(function (value) {
                valuesHtml += value.key + ' > ' + value.value;
            });

            optionsHtml += '<li>'+element.key+': '+valuesHtml+'</li>';
        });

        optionsHtml = '<ul>'+optionsHtml+'</ul>';

        var bodyHtml = str_replace('%options%', optionsHtml, $('vocabulary_options_pupup_template').innerHTML);

        $('modal_dialog_message').update(bodyHtml);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    addOptionsToVocabulary: function(needAdd)
    {
        var self = this;

        var isRemember = $('vocabulary_options_remember_checkbox').checked;

        if (!needAdd && !isRemember) {
            Windows.getFocusedWindow().close();
            return;
        }

        new Ajax.Request(self.options.url.addOptionsToVocabulary, {
            method: 'post',
            parameters: {
                options_data : $('vocabulary_options_data').value,
                need_add:    needAdd ? 1 : 0,
                is_remember: isRemember ? 1 : 0
            },
            onSuccess: function (transport) {
                vocabularyOptionsPopUp.close();
            }
        });
    },

    unmapFromGeneralId: function(productIds, afterDoneFunction)
    {
        var self = this;

        this.gridHandler.unselectAll();

        self.flagSuccess = false;

        new Ajax.Request(self.options.url.unmapFromAsin, {
            method: 'post',
            parameters: {
                products_ids: productIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();
                self.flagSuccess = true;

                var response = transport.responseText.evalJSON();

                MagentoMessageObj.clearAll();
                MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);
            },
            onComplete: function() {
                if (self.flagSuccess == true && afterDoneFunction != undefined) {
                    afterDoneFunction();
                }
            }
        });
    },

    //----------------------------------

    specificsChange: function(select)
    {
        var self = this;

        var idParts = explode('_', select.id);
        var id = idParts[2];
        var specifics = [];
        var selectedAsin = '';

        $(select.id) && self.hideEmptyOption($(select.id));

        self.validateSpecifics(id);

        var asins = JSON.parse(decodeHtmlentities($('asins_' + id).innerHTML));

        $('parent_asin_text_'+id).hide();
        $('map_link_error_icon_'+id).hide();

        $$('.specifics_' + id).each(function(el) {
            var specificName = explode('_', el.id);
            specificName = specificName[1];

            specifics[specificName] = el.value;
        });

        for (var spec in asins) {
            var productSpecifics = asins[spec].specifics;

            var found = true;
            for (var sName in productSpecifics) {

                if (productSpecifics[sName] != specifics[sName]) {
                    found = false;
                    break;
                }
            }

            if (found) {
                selectedAsin = spec;
                break;
            }
        }

        if (selectedAsin === '') {
            $('map_link_error_icon_'+id).show();
            $('asin_link_'+id).innerHTML = $('parent_asin_'+id).innerHTML;
            $('parent_asin_text_'+id).show();
            return $('map_link_' + id).innerHTML = '<span style="color: #808080">' + self.options.text.assign + '</span>';
        }

        $('asin_link_'+id).innerHTML = selectedAsin;

        var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
        mapLinkTemplate = mapLinkTemplate.replace('%general_id%', selectedAsin);

        asins = addslashes(encodeURIComponent(JSON.stringify(asins)));

        mapLinkTemplate = mapLinkTemplate.replace('%options_data%', asins);
        $('map_link_' + id).innerHTML = mapLinkTemplate;
    },

    validateSpecifics: function(id, variations, i)
    {
        var variation = $H(variations || decodeHtmlentities($('channel_variations_tree_' + id).innerHTML).evalJSON()),
            attributes = $$('.specifics_name_' + id),
            options = $$('.specifics_' + id),
            index = i || 0;

        if (index === 0) {
            options.each(function(el) {
                el.disable();
            });
        }

        if (!attributes[index] || !options[index]) {
            return;
        }

        var attr = variation.keys()[0];

        var oldValue = decodeHtmlentities(options[index].value);
        options[index].update();
        options[index].enable();
        options[index].appendChild(new Element('option', {style: 'display: none'}));

        $H(variation.get(attr)).each(function(option) {
            options[index].appendChild(new Element('option', {value: option[0]})).insert(option[0]);

            if (option[0] == oldValue) {
                options[index].value = oldValue;
            }
        });

        if (oldValue) {
            index++;
            this.validateSpecifics(id, variation.get(attr)[oldValue], index);
        }
    },

    //----------------------------------

    attributesChange: function(select)
    {
        var self = this;

        var idParts = explode('_', select.id);
        var id = idParts[4];
        var optionsData = {
            matched_attributes: {},
            variations: null
        };

        $(select.id) && self.hideEmptyOption($(select.id));

        $('map_link_error_icon_'+id).hide();

        var existedValues = [];
        $$('.amazon_product_attribute_' + id).each(function(el) {
            var attributeNumber = explode('_', el.id);
            attributeNumber = attributeNumber[3];

            if(el.value != '' && existedValues.indexOf(el.value) === -1) {
                var magentoAttribute = $('magento_product_attribute_'+attributeNumber+'_'+id);
                optionsData.matched_attributes[magentoAttribute.value] = el.value;
                existedValues.push(el.value);
            } else {
                optionsData = '';
                throw $break;
            }
        });

        if (optionsData === '') {
            $('map_link_error_icon_'+id).show();
            return $('map_link_' + id).innerHTML = '<span style="color: #808080">' + self.options.text.assign + '</span>';
        }

        optionsData.variations = JSON.parse(decodeHtmlentities($('variations_' + id).innerHTML));
        optionsData = addslashes(encodeURIComponent(JSON.stringify(optionsData)));

        var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
        mapLinkTemplate = mapLinkTemplate.replace('%options_data%', optionsData);
        $('map_link_' + id).innerHTML = mapLinkTemplate;
    },

    //----------------------------------

    showAsinCategories: function (link, rowId, asin, productId)
    {
        var self = this;

        new Ajax.Request(self.options.url.getCategoriesByAsin, {
            method: 'post',
            parameters: {
                asin: asin,
                product_id: productId
            },
            onSuccess: function(transport) {

                link.hide();

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                var categoriesRow = $('asin_categories_' + rowId);

                if (response.data == '') {
                    $('asin_categories_not_found_' + rowId).show();
                } else {
                    var i = 3;
                    response.data.each(function(item) {
                        var str = item.title;
                        if (item.path) {
                            str = item.path + ' > ' + str;
                        }

                        str = str + ' (' + item.id + ')';

                        var row = new Element('p');
                        row.setStyle({
                            'color'     : 'grey'
                        });

                        categoriesRow.appendChild(row).insert(str);

                        i--;
                        if (i <= 0) {
                            throw $break;
                        }
                    });
                }
            }
        });
    }
});