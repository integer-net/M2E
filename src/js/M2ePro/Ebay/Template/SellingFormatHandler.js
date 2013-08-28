EbayTemplateSellingFormatHandler = Class.create();
EbayTemplateSellingFormatHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_SellingFormat', 'title', 'id',
                                                M2ePro.formData.id);

        this.defaultOptions = {
            'duration_mode' : $('duration_mode').innerHTML,
            'qty_mode'      : $('qty_mode').innerHTML,
            'buyitnow_price_mode' : $('buyitnow_price_mode').innerHTML
        };

        Validation.add('M2ePro-validate-price-coefficient', M2ePro.text.price_coef_error, function(value)
        {
            if (value == '') {
                return true;
            }

            if (value == '0' || value == '0%') {
                return false;
            }

            return value.match(/^[+-]?\d+[.,]?\d*[%]?$/g);
        });

        Validation.add('validate-qty', 'Wrong value. Only integer numbers.', function(value, el)
        {
            if (!el.up('tr').visible()) {
                return true;
            }

            if (value.match(/[^\d]+/g)) {
                return false;
            };

            if (value <= 0) {
                return false;
            }

            return true;
        });
    },

    //----------------------------------

    duplicate_click: function($headId)
    {
        var attrSetEl = $('attribute_sets_fake');

        if (attrSetEl) {
            $('attribute_sets').remove();
            attrSetEl.observe('change', AttributeSetHandlerObj.changeAttributeSets);
            attrSetEl.id = 'attribute_sets';
            attrSetEl.name = 'attribute_sets[]';
            attrSetEl.addClassName('M2ePro-validate-attribute-sets');

            AttributeSetHandlerObj.confirmAttributeSets();
        }

        if ($('attribute_sets_breadcrumb')) {
            $('attribute_sets_breadcrumb').remove();
        }
        $('attribute_sets_container').show();
        $('attribute_sets_buttons_container').show();

        var listingTypeEl = $('listing_type_fake');

        if (listingTypeEl) {
            $('listing_type').remove();
            listingTypeEl.observe('change', EbayTemplateSellingFormatHandlerObj.listing_type_change);
            listingTypeEl.id = 'listing_type';
            listingTypeEl.name = 'listing_type';
            listingTypeEl.addClassName('required-entry');
        }

        if ($('listing_type_breadcrumb')) {
            $('listing_type_breadcrumb').remove();
        }
        $('listing_type_container').show();

        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_SellingFormat', 'title', '',
                                                '');

        CommonHandlerObj.duplicate_click($headId);
    },

    //----------------------------------

    attribute_sets_confirm: function()
    {
        AttributeSetHandlerObj.confirmAttributeSets();

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('listing_type_attribute', 'listing_type_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('duration_attribute', 'duration_attribute_span');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('qty_custom_attribute', 'qty_custom_attribute_td');

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('start_price_custom_attribute', 'start_price_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('reserve_price_custom_attribute', 'reserve_price_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('buyitnow_price_custom_attribute', 'buyitnow_price_custom_attribute_td');

        // best offer
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('best_offer_accept_attribute', 'best_offer_accept_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('best_offer_reject_attribute', 'best_offer_reject_attribute_td');
    },

    //----------------------------------

    start_price_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (this.value == self.PRICE_FINAL) {
            $('start_price_note').innerHTML = M2ePro.text.start_final_price_note;
        } else {
            $('start_price_note').innerHTML = M2ePro.text.start_price_note;
        }

        self.setPriceAttributeVisibility(this.value, 'start_price_custom_attribute_tr', null);
    },

    reserve_price_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (this.value == self.PRICE_FINAL) {
            $('reserve_price_note').innerHTML = M2ePro.text.reserve_final_price_note;
        } else {
            $('reserve_price_note').innerHTML = M2ePro.text.reserve_price_note;
        }

        self.setPriceAttributeVisibility(this.value, 'reserve_price_custom_attribute_tr', 'note_reserve_price_custom_attribute');
    },

    buyitnow_price_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (this.value == self.PRICE_FINAL) {
            $('buyitnow_price_note').innerHTML = M2ePro.text.buyitnow_final_price_note;
        } else {
            $('buyitnow_price_note').innerHTML = M2ePro.text.buyitnow_price_note;
        }

        self.setPriceAttributeVisibility(this.value, 'buyitnow_price_custom_attribute_tr', 'note_use_buy_from');
    },

    //----------------------------------

    best_offer_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (this.value == self.BEST_OFFER_MODE_YES) {

            if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
                this.value = 0;
                return;
            }
            $('best_offer_respond_table').show();
            $('best_offer_accept_mode').simulate('change');
            $('best_offer_reject_mode').simulate('change');

        } else {
            $('best_offer_respond_table').hide();
        }
    },

    best_offer_accept_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;
        self.setVisibility(this.value, 'best_acc_input_select_tr', 'best_acc_input_text_tr')
    },

    best_offer_reject_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;
        self.setVisibility(this.value, 'best_offer_reject_value_select_tr', 'best_offer_reject_value_text_tr')
    },

    //----------------------------------

    listing_type_change: function(event)
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        var tempQtyModeValue = $('qty_mode').value;
        var tempBuyItNowPriceModeValue = $('buyitnow_price_mode').value;

        $('duration_mode').value = 3; // 3 days allowed for auction and for fixed price
        $('duration_mode').show();
        $('qty_mode').simulate('change');

        $('duration_attribute_span').hide();
        $('duration_mode_note', 'duration_attribute_note').invoke('hide');
        $('listing_type_custom_attribute_tr').hide();

        if (this.value == self.LISTING_TYPE_FIXED) { //fixed price

            $('qty_mode').update(self.defaultOptions['qty_mode'])
            $('qty_mode').value = tempQtyModeValue;

            $('buyitnow_price_mode').update(self.defaultOptions['buyitnow_price_mode']);
            $('buyitnow_price_mode').select('option[value="0"]')[0].remove();
            $('buyitnow_price_mode').value = tempBuyItNowPriceModeValue;

            $('duration_mode').update(self.defaultOptions['duration_mode']);
            $('durationId1').remove();

            if (M2ePro.formData.duration_mode && M2ePro.formData.duration_mode != 1) {
                $('duration_mode').value = M2ePro.formData.duration_mode;
            }

            $('price_variation_table').show();

            $('start_price_table').hide();
            $('reserve_price_table').hide();
            $('duration_mode_note').show();

            $('magento_block_ebay_template_selling_format_best_offer').show();

        } else if (this.value == self.LISTING_TYPE_ATTRIBUTE) { // custom attribute

            if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
                this.value = M2ePro.formData.listing_type_selected;
                return;
            }

            $('listing_type_custom_attribute_tr').show();

            $('duration_mode').hide();
            $('duration_attribute_span').show();

            $('qty_mode').update(self.defaultOptions['qty_mode'])
            $('qty_mode').value = tempQtyModeValue;

            $('buyitnow_price_mode').update(self.defaultOptions['buyitnow_price_mode']);
            $('buyitnow_price_mode').value = tempBuyItNowPriceModeValue;

            $('price_variation_table').show();

            $('start_price_table').show();
            $('reserve_price_table').show();
            $('duration_attribute_note').show();

            $('magento_block_ebay_template_selling_format_best_offer').show();

        } else { //auction

            $('qty_mode').update(self.defaultOptions['qty_mode']);
            $('qty_mode').value = self.QTY_MODE_SINGLE;
            $('qty_mode').simulate('change');

            $('qty_mode_product', 'qty_mode_cv', 'qty_mode_ca').invoke('remove');
            $('qty_mode_cv_tr', 'qty_mode_ca_tr').invoke('hide');

            $('duration_mode').update(self.defaultOptions['duration_mode']);
            $('durationId30', 'durationId100').invoke('remove');

            $('buyitnow_price_mode').update(self.defaultOptions['buyitnow_price_mode']);
            $('buyitnow_price_mode').value = tempBuyItNowPriceModeValue;

            if (M2ePro.formData.duration_mode &&
                M2ePro.formData.duration_mode != 30 &&
                M2ePro.formData.duration_mode != 100) {
                $('duration_mode').value = M2ePro.formData.duration_mode;
            }

            $('price_variation_table').hide();

            $('start_price_table').show();
            $('reserve_price_table').show();
            $('duration_mode_note').show();

            $('magento_block_ebay_template_selling_format_best_offer').hide();

            $('best_offer_mode').value = self.BEST_OFFER_MODE_NO;
            $('best_offer_mode').simulate('change');
        }
    },

    qty_mode_change: function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        $('qty_mode_cv_tr', 'qty_mode_ca_tr').invoke('hide');

        if (this.value == self.QTY_MODE_NUMBER) {
            $('qty_mode_cv_tr').show();
        } else if (this.value == self.QTY_MODE_ATTRIBUTE) {

            if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
                this.value = 0;
                return;
            }

            $('qty_mode_ca_tr').show();
        }

        var qtyMaxMode = $('qty_max_posted_value_mode_tr');
        var qtyMaxPosted = $('qty_max_posted_value_tr');
        var qtyMaxModeSelect = $('qty_max_posted_value_mode');

        if (this.value == self.QTY_MODE_PRODUCT || this.value == self.QTY_MODE_ATTRIBUTE) {
            qtyMaxMode.show();

            if (M2ePro.formData.qty_mode == self.QTY_MODE_SINGLE || M2ePro.formData.qty_mode == self.QTY_MODE_NUMBER) {
                $('qty_max_posted_value').value = self.QTY_MAX_POSTED_DEFAULT_VALUE;
                qtyMaxModeSelect.value = self.QTY_MAX_POSTED_MODE_ON;
            }

            qtyMaxModeSelect.simulate('change');
        } else {
            qtyMaxMode.hide();
            qtyMaxPosted.hide();
        }
    },

    qtyMaxPostedMode_change: function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;
        var qtyMaxPosted = $('qty_max_posted_value_tr');

        if (this.value == self.QTY_MAX_POSTED_MODE_ON) {

            if (M2ePro.formData.qty_max_posted_value <= 0) {
                $('qty_max_posted_value').value = self.QTY_MAX_POSTED_DEFAULT_VALUE;
            }

            qtyMaxPosted.show();
        } else {
            qtyMaxPosted.hide();
        }
    },

    //----------------------------------

    setVisibility : function(value, firstName, secondName)
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (value == self.BEST_OFFER_ACCEPT_MODE_NO) {

            $(firstName).hide();
            $(secondName).hide();

        } else if (value == self.BEST_OFFER_ACCEPT_MODE_PERCENTAGE) {

            $(firstName).hide();
            $(secondName).show();

        } else { // ca

            if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
                this.value = 0;
                return;
            }

            $(secondName).hide();
            $(firstName).show();

        }
    },

    setPriceAttributeVisibility : function(value, elementName, elementNameNote)
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (elementNameNote != null) {
             $(elementNameNote, elementNameNote + '2').invoke(value == 0 ? 'hide' : 'show');
        }

        if (value == self.PRICE_ATTRIBUTE) {
            if (AttributeSetHandlerObj.checkAttributeSetSelection()) {
                $(elementName).show();
            } else {
                this.value = 1;
            }
        } else {
            $(elementName).hide();
        }

        self.updateCustomerGroupIdVisibility();
    },

    updateCustomerGroupIdVisibility: function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;
        var displayCustomerGroup = $('start_price_mode', 'reserve_price_mode', 'buyitnow_price_mode').any(
            function (select) { return select.value == self.PRICE_FINAL; }
        );

        if (displayCustomerGroup) {
            $('magento_block_ebay_template_selling_format_customer_group_id').show();
        } else {
            $('magento_block_ebay_template_selling_format_customer_group_id').hide();
            $('customer_group_id').value = '';
        }
    }

    //----------------------------------
});