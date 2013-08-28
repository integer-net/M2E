ListingItemGridHandler = Class.create();
ListingItemGridHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function( M2ePro,
        gridId,
        productIdCellIndex,
        productTitleCellIndex,
        ListingActionHandlerObj,
        ListingMoveToListingHandlerObj,
        ListingProductSearchHandlerObj,
        ListingOtherAutoMapHandlerObj)
    {
        this.M2ePro = M2ePro;
        this.gridId = gridId;
        this.productIdCellIndex = productIdCellIndex;
        this.productTitleCellIndex = productTitleCellIndex;
        this.ListingActionHandlerObj = ListingActionHandlerObj;
        this.ListingMoveToListingHandlerObj = ListingMoveToListingHandlerObj;

        this.ListingProductSearchHandlerObj = ListingProductSearchHandlerObj;
        this.ListingOtherAutoMapHandlerObj = ListingOtherAutoMapHandlerObj;

        this.ListingActionHandlerObj.setListingItemGridHandlerObj(this);
    },

    //----------------------------------

    setMaxProductsInPart: function(maxProductsInPart, separationMode)
    {
        this.maxProductsInPart = maxProductsInPart;
        this.separationMode = separationMode;
    },

    //----------------------------------

    getCellContent : function(rowId,cellIndex)
    {
        eval('var rows = '+this.gridId+'JsObject.rows;');
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                return trim(cels[cellIndex].innerHTML);
            }
        }

        return '';
    },

    getProductIdByRowId : function(rowId)
    {
        return this.getCellContent(rowId,this.productIdCellIndex);
    },

    getProductNameByRowId : function(rowId)
    {
        var cellContent = this.getCellContent(rowId,this.productTitleCellIndex);
        var expr = new RegExp(/<span[^>]*>(.*?)<\/span>/i);
        var matches = expr.exec(cellContent);
        return matches[1];
    },

    //----------------------------------

    getSelectedItemsParts : function()
    {
        eval('var selectedProductsString = '+this.gridId+'_massactionJsObject.checkedString;');
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            return new Array();
        }

        var maxProductsInPart = typeof this.maxProductsInPart == 'undefined' ? 10 : this.maxProductsInPart;
        var separationMode = typeof this.separationMode == 'undefined' ? 1 : this.separationMode;

        if (separationMode == 1) {
            if (selectedProductsArray.length <= 25) {
                maxProductsInPart = 5;
            }
            if (selectedProductsArray.length <= 15) {
                maxProductsInPart = 3;
            }
            if (selectedProductsArray.length <= 8) {
                maxProductsInPart = 2;
            }
            if (selectedProductsArray.length <= 4) {
                maxProductsInPart = 1;
            }
        }

        var result = new Array();
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        return result;
    },

    //----------------------------------

    selectAll : function()
    {
        eval(this.gridId+'_massactionJsObject.selectAll();');
    },

    unselectAll : function()
    {
        eval(this.gridId+'_massactionJsObject.unselectAll();');
    },

    unselectAllAndReload : function()
    {
        this.unselectAll();
        eval(this.gridId+'JsObject.reload();');
    },

    selectByRowId : function(rowId)
    {
        this.unselectAll();

        eval('var rows = '+this.gridId+'JsObject.rows;');
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                checkbox.checked = true;
                eval(this.gridId+'_massactionJsObject.checkedString = \''+rowId+'\';');
                break;
            }
        }
    },

    //----------------------------------

    afterInitPage : function()
    {
        var objectButton = $$('#'+this.gridId+'_massaction-form fieldset span.field-row button');

        var self = this;

        objectButton.each(function(s) {
            s.writeAttribute("onclick",'');
            s.observe('click', function() {
                self.massactionSubmitClick(false);
            });
        });
    },

    massactionSubmitClick : function(force)
    {
        if(typeof(force) != 'boolean') {
            force = false;
        }

        var self = this;

        eval('var selectedProductsString = '+self.gridId+'_massactionJsObject.checkedString;');

        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            alert(self.M2ePro.text.select_items_message);
            return;
        }

        var selectAction = true;
        $$('select#'+self.gridId+'_massaction-select option').each(function(o) {
            if (o.selected && o.value == '') {
                alert(self.M2ePro.text.select_action_message);
                selectAction = false;
                return;
            }
        });

        if (!selectAction) {
            return;
        }

        if (!force && !confirm(CONFIRM)) {
            return;
        }

        CommonHandlerObj.scroll_page_to_top();

        $$('select#'+self.gridId+'_massaction-select option').each(function(o) {
            if (o.selected) {

                switch (o.value) {
                    case '':
                        alert(self.M2ePro.text.select_action_message);
                        return;

                    case 'list':
                        self.ListingActionHandlerObj.runListProducts();
                        break;

                    case 'revise':
                        self.ListingActionHandlerObj.runReviseProducts();
                        break;

                    case 'relist':
                        self.ListingActionHandlerObj.runRelistProducts();
                        break;

                    case 'stop':
                        self.ListingActionHandlerObj.runStopProducts();
                        break;

                    case 'stop_and_remove':
                        self.ListingActionHandlerObj.runStopAndRemoveProducts();
                        break;

                    case 'delete_and_remove':
                        self.ListingActionHandlerObj.runDeleteAndRemoveProducts();
                        break;

                    case 'map_products':
                        self.ListingOtherAutoMapHandlerObj.mapProductsAuto(selectedProductsString);
                        break;

                    case 'move_to_listing':
                        self.ListingMoveToListingHandlerObj.getGridHtml(selectedProductsArray);
                        break;

                    case 'map_to_general_id':
                        self.ListingProductSearchHandlerObj.searchGeneralIdAuto(selectedProductsString);
                        break;

                    case 'new_general_id':
                        self.ListingProductSearchHandlerObj.addNewGeneralId(selectedProductsString);
                        break;

                    case 'unmap_general_id':
                        self.ListingProductSearchHandlerObj.unmapFromGeneralId(selectedProductsString);
                        break;

                    case 'add_specifics_to_products':
                        EbayMotorSpecificHandlerObj.openPopUp();
                        break;

                    case 'duplicate':
                        self.ListingActionHandlerObj.duplicateProducts(selectedProductsString);
                        break;
                }
            }
        });
    },

    //----------------------------------

    viewItemHelp : function(rowId, data)
    {
        $('lpv_grid_help_icon_open_'+rowId).hide();
        $('lpv_grid_help_icon_close_'+rowId).show();

        if ($('lp_grid_help_content_'+rowId) != null) {
            $('lp_grid_help_content_'+rowId).show();
            return;
        }

        var html = this.createHelpTitleHtml(rowId);

        data = eval(base64_decode(data));
        for (var i=0;i<data.length;i++) {
            html += this.createHelpActionHtml(data[i]);
        }

        html += this.createHelpViewAllLogHtml(rowId);

        eval('var rows = '+this.gridId+'JsObject.rows;');
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                row.insert({
                  after: '<tr id="lp_grid_help_content_'+rowId+'"><td class="help_line" colspan="'+($(row).childElements().length)+'">'+html+'</td></tr>'
                });
            }
        }
        var self = this;
        $('hide_item_help_' + rowId).observe('click', function() {
            self.hideItemHelp(rowId);
        });
    },

    hideItemHelp : function(rowId)
    {
        if ($('lp_grid_help_content_'+rowId) != null) {
            $('lp_grid_help_content_'+rowId).hide();
        }

        $('lpv_grid_help_icon_open_'+rowId).show();
        $('lpv_grid_help_icon_close_'+rowId).hide();
    },

    //----------------------------------

    createHelpTitleHtml : function(rowId)
    {
        var self = this;
        var productTitle = this.getProductNameByRowId(rowId);
        var closeHtml = '<a href="javascript:void(0);" id="hide_item_help_' + rowId + '" title="'+self.M2ePro.text.close_word+'"><span class="hl_close">&times;</span></a>';
        return '<div class="hl_header"><span class="hl_title">'+productTitle+'</span>'+closeHtml+'</div>';
    },

    createHelpActionHtml : function(action)
    {
        var self = this;
        var classContainer = 'hl_container';

        if (action.type == 2) {
            classContainer += ' hl_container_success';
        } else if (action.type == 3) {
            classContainer += ' hl_container_warning';
        } else if (action.type == 4) {
            classContainer += ' hl_container_error';
        }

        var html = '<div class="'+classContainer+'">';
            html += '<div class="hl_date">'+action.date+'</div>' +
                    '<div class="hl_action">';

        if (action.initiator != '') {
            html += '<strong style="color: gray;">'+action.initiator+'</strong>&nbsp;&nbsp;';
        }

        html += '<strong>'+action.action+'</strong></div>' +
                    '<div style="clear: both"></div>' +
                        '<div style="padding-top: 3px;">';

        for (var i=0;i<action.items.length;i++) {

            var type = self.M2ePro.text.notice_word;

            if (action.items[i].type == 2) {
                type = '<span style="color: green;">'+self.M2ePro.text.success_word+'</span>';
            } else if (action.items[i].type == 3) {
                type = '<span style="color: orange;">'+self.M2ePro.text.warning_word+'</span>';
            } else if (action.items[i].type == 4) {
                type = '<span style="color: red;">'+self.M2ePro.text.error_word+'</span>';
            }

            html += '<div style="margin-top: 7px;"><div class="hl_messages_type">'+type+'</div><div class="hl_messages_text">'+action.items[i].description+'</div></div>';
        }

        html +=     '</div>' +
                '</div>';

        return html;
    },

    createHelpViewAllLogHtml : function(rowId)
    {
        var self = this;

        var url = '';
        if (this.gridId == 'ebayListingOtherGrid' || this.gridId == 'amazonListingOtherGrid' || this.gridId == 'buyListingOtherGrid') {
            url = self.M2ePro.url.logViewUrl+'id/'+rowId;
        } else {
            var temp = this.getProductIdByRowId(rowId);

            var regExpImg= new RegExp('<img[^><]*>','gi');
            var regExpHr= new RegExp('<hr>','gi');

            temp = temp.replace(regExpImg,'');
            temp = temp.replace(regExpHr,'');

            var productId = strip_tags(temp);

            url = self.M2ePro.url.logViewUrl+'filter/'+base64_encode('product_id[from]='+productId+'&product_id[to]='+productId);
        }

        return '<div class="hl_footer"><a href="'+url+'">'+self.M2ePro.text.view_all_product_log_message+'</a></div>';
    }

    //###############################################
});