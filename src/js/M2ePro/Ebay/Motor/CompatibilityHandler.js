EbayMotorCompatibilityHandler = Class.create();
EbayMotorCompatibilityHandler.prototype = Object.extend(new CommonHandler(), {

    listingId: null,
    compatibilityType: null,
    compatibilityGridId: null,
    productsGridId: null,
    isEmptyCompatibilityAttribute: false,
    mode: 'add',
    savedNotes: {},

    //----------------------------------

    initialize: function (listingId, compatibilityType, compatibilityGridId, productsGridId, isEmptyCompatibilityAttribute) {
        this.listingId = listingId;
        this.compatibilityType = compatibilityType;
        this.compatibilityGridId = compatibilityGridId;
        this.productsGridId = productsGridId;
        this.isEmptyCompatibilityAttribute = isEmptyCompatibilityAttribute;
    },

    //----------------------------------

    setMode: function (mode) {
        this.mode = mode;
    },

    //----------------------------------

    initProductGrid: function () {
        var self = this;
        var grid = eval(self.productsGridId + 'JsObject');

        if (!grid.massaction) {
            grid.massaction = eval(self.productsGridId + '_massactionJsObject');
        }
    },

    initCompatibilityGrid: function () {
        var self = this;
        var grid = eval(self.compatibilityGridId + 'JsObject');

        if (!grid.massaction) {
            grid.massaction = eval(self.compatibilityGridId + '_massactionJsObject');
        }

        grid.massaction.updateCount = grid.massaction.updateCount.wrap(
            function (callOriginal) {
                callOriginal();

                var attributeContent = [];

                grid.massaction.getCheckedValues().split(',').each(function (id) {
                    if (!id) {
                        return;
                    }

                    var idString = id;
                    if ($('note_view_' + id) &&
                        $('note_view_' + id).innerHTML.length > 0 &&
                        $('note_view_' + id).innerHTML != ' -- '
                    ) {
                        idString += '|"' + $('note_view_' + id).innerHTML + '"';
                    } else if (EbayMotorCompatibilityHandlerObj.savedNotes[id]) {
                        idString += '|"' + EbayMotorCompatibilityHandlerObj.savedNotes[id] + '"';
                    }

                    attributeContent[attributeContent.length] = idString;
                });

                $('attribute_content').value = attributeContent.join(',');

                $('attribute_content').value == ''
                    ? $('generate_attribute_content_container').hide() : $('generate_attribute_content_container').show();
            }
        );

        grid.massaction.apply = function () {
            if (this.getCheckedValues() == '') {
                alert(M2ePro.translator.translate('Please select the products you want to perform the action on.'));
                return;
            }

            var item = this.getSelectedItem();
            if (!item) {
                return;
            }

            if (item.confirm && !window.confirm(item.confirm)) {
                return;
            }

            switch (item.id) {
                case 'overwrite_attribute':
                    self.addIdsToProducts(true);
                    break;

                case 'add_to_attribute':
                    self.addIdsToProducts(false);
                    break;
            }
        };
    },

    initCompatibilityViewGrid: function ()
    {
        ebayMotorViewGridJsObject.massaction.apply = function () {
            if (this.getCheckedValues() == '') {
                alert(M2ePro.translator.translate('Please select items you want to perform the action on.'));
                return;
            }

            var item = this.getSelectedItem();
            if (!item) {
                return;
            }

            if (item.confirm && !window.confirm(item.confirm)) {
                return;
            }

            switch (item.id) {
                case 'delete':
                    EbayMotorCompatibilityHandlerObj.deleteIdsFromProduct(true);
                    break;
            }
        };
    },

    loadCompatibilityGrid: function()
    {
        var self = this;

        var url = M2ePro.url.get('adminhtml_ebay_listing/motorSpecificGrid');
        if (self.compatibilityType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE')) {
            url = M2ePro.url.get('adminhtml_ebay_listing/motorKtypeGrid');
        }

        new Ajax.Request( url ,
        {
            method: 'get',
            asynchronous : false,
            parameters : {
                listing_id: self.listingId
            },
            onSuccess: function (transport)
            {
                var responseText = transport.responseText.replace(/>\s+</g, '><');
                $('compatibility_grid_container').update(responseText);
                setTimeout(function() {
                    self.initProductGrid();
                    self.initCompatibilityGrid();
                }, 150);
            }
        });
    },

    //----------------------------------

    initPopUp: function(title, popUpBlockId)
    {
        this.title = title;
        this.popUpBlockId = popUpBlockId;
        this.popUpId = 'save_to_products_pop_up';
    },

    openPopUp: function()
    {
        var self = this;

        MagentoMessageObj.clearAll();

        if (self.compatibilityType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC') && self.isEmptyCompatibilityAttribute) {
            MagentoMessageObj.addError(M2ePro.translator.translate('Please specify eBay motors compatibility attribute in %s > Configuration > <a target="_blank" href="%s">General</a>'));
            return;
        }

        var isCompatibilityGridExists = false;
        if ($(self.compatibilityGridId) != null && $('compatibility_grid_container').innerHTML != '') {
            isCompatibilityGridExists = true;
        }

        self.loadCompatibilityGrid();

        this.popUp = Dialog.info(null, {
            id: this.popUpId,
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: self.title,
            top: 50,
            width: 1000,
            height: 550,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function () { self.closeCallback(); return true; }
        });

        $('modal_dialog_message').appendChild($(self.popUpBlockId).show());

        if (isCompatibilityGridExists) {
            self.initCompatibilityGrid();
        }
    },

    closePopUp: function()
    {
        Windows.close(this.popUpId);
    },

    closeCallback: function()
    {
        var self = this;

        $(document.body).appendChild($(this.popUpBlockId).hide());

        var compatibilityGrid = eval(self.compatibilityGridId + 'JsObject');
        compatibilityGrid.massaction.unselectAll();
        compatibilityGrid.massaction.select.value = '';

        var productsGrid = eval(self.productsGridId + 'JsObject');
        productsGrid.massaction.unselectAll();

        $('attribute_content').value = '';
    },

    //----------------------------------

    addIdsToProducts: function(overwrite)
    {
        var self = this;
        var compatibilityGrid = eval(self.compatibilityGridId + 'JsObject');
        var productsGrid = eval(self.productsGridId + 'JsObject');

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing/updateMotorsCompatibilityAttributes') ,
        {
            method: 'post',
            asynchronous : true,
            parameters : {
                listing_id: this.listingId,
                listing_product_ids: EbayListingSettingsGridHandlerObj.selectedProductsIds.toString(),
                ids: $('attribute_content').value,
                compatibility_type: self.compatibilityType,
                overwrite: overwrite ? 'yes' : 'no'
            },
            onSuccess: function (transport)
            {
                compatibilityGrid.massaction.unselectAll();
                self.closePopUp();

                var response = transport.responseText.evalJSON(true);

                if (response.ok) {
                    MagentoMessageObj.addSuccess(response.message);
                    productsGrid.doFilter();
                } else {
                    MagentoMessageObj.addError(response.message);
                }
            }
        });
    },

    deleteIdsFromProduct: function()
    {
        var self = this;

        new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing/deleteIdsFromCompatibilityList') ,
            {
                method: 'post',
                asynchronous : true,
                parameters : {
                    listing_product_id: $('compatibility_view_listing_product_id').value,
                    ids: ebayMotorViewGridJsObject.massaction.getCheckedValues(),
                    compatibility_type: self.compatibilityType
                },
                onSuccess: function (transport)
                {
                    ebayMotorViewGridJsObject.reload();
                    ebayMotorViewGridJsObject.massaction.unselectAll();
                }
            });
    },

    //----------------------------------

    switchNoteEditMode: function(id, isCancel)
    {
        if ($('note_view_' + id).getStyle('display') == 'none') {
            if (!isCancel) {
                $('note_view_' + id).innerHTML = $('note_edit_' + id).value;
            }

            if ($('note_view_' + id).innerHTML) {
                $('note_edit_link_' + id).show();
                $('note_add_link_' + id).hide();
            } else {
                $('note_edit_link_' + id).hide();
                $('note_add_link_' + id).show();
            }

            $('note_save_link_' + id).hide();
            $('note_cancel_link_' + id).hide();

            $('note_edit_' + id).hide();
            $('note_view_' + id).show();
        } else {
            if ($('note_view_' + id).innerHTML != '') {
                $('note_edit_' + id).value = $('note_view_' + id).innerHTML;
            }

            $('note_view_' + id).hide();
            $('note_edit_' + id).show();

            $('note_save_link_' + id).show();
            $('note_cancel_link_' + id).show();
            $('note_edit_link_' + id).hide();
            $('note_add_link_' + id).hide();
        }
    },

    saveNote: function(id)
    {
        var self = EbayMotorCompatibilityHandlerObj;

        self.switchNoteEditMode(id);

        if (self.mode == 'add') {
            window[self.compatibilityGridId + 'JsObject'].massaction.updateCount();
            self.savedNotes[id] = $('note_view_' + id).innerHTML;
        } else {
            new Ajax.Request( M2ePro.url.get('adminhtml_ebay_listing/changeCompatibilityNote') ,
            {
                method: 'post',
                asynchronous : true,
                parameters : {
                    listing_product_id: $('compatibility_view_listing_product_id').value,
                    id: id,
                    compatibility_type: self.compatibilityType,
                    note: $('note_view_' + id).innerHTML
                }
            });
        }
    }

    //----------------------------------
});