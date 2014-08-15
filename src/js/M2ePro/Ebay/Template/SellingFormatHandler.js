EbayTemplateSellingFormatHandler = Class.create();
EbayTemplateSellingFormatHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-price-coefficient', M2ePro.translator.translate('Price Change is not valid.'), function(value, el)
        {
            var tempEl = el;

            var hidden = !$(tempEl).visible();

            while (!hidden) {
                tempEl = $(tempEl).up();
                hidden = !tempEl.visible();
                if (tempEl == document || tempEl.hasClassName('entry-edit')) {
                    break;
                }
            }

            if (hidden) {
                return true;
            }

            var coefficient = el.up().next().down('input');

            coefficient.removeClassName('price_unvalidated');

            if (!coefficient.up('div').visible()) {
                return true;
            }
            var floatValidator = Validation.get('M2ePro-validation-float');
            if (floatValidator.test($F(coefficient), coefficient) && parseFloat(coefficient.value) <= 0) {
                coefficient.addClassName('price_unvalidated');
                return false;
            }

            return true;
        });

        Validation.add('validate-qty', M2ePro.translator.translate('Wrong value. Only integer numbers.'), function(value, el)
        {
            if (!el.up('tr').visible()) {
                return true;
            }

            if (value.match(/[^\d]+/g) || value <= 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-vat', M2ePro.translator.translate('Wrong value. Must be no more than 30. Max applicable length is 6 characters, including the decimal (e.g., 12.345).'), function(value) {
            if (!value) {
                return true;
            }

            if (value.length > 6) {
                return false;
            }

            if (value < 0) {
                return false;
            }

            value = Math.ceil(value);

            return value >= 0 && value <= 30;
        });

        Validation.add('M2ePro-validation-charity-percentage', M2ePro.translator.translate('Please select a percentage of donation'), function(value, element)
        {
            if (value == 0) {
                return false;
            }

            return true;
        });
    },

    //----------------------------------

    simple_mode_disallowed_hide : function()
    {
        $$('#template_selling_format_data_container .simple_mode_disallowed').invoke('hide');
    },

    //----------------------------------

    updateHiddenValue : function(elementMode, elementHidden)
    {
        var value = elementMode.options[elementMode.selectedIndex].getAttribute('value_hack');
        elementHidden.value = value;
    },

    //----------------------------------

    isSimpleMode: function()
    {
        return M2ePro.formData.simpleMode;
    },

    isStpAvailable: function()
    {
        return M2ePro.formData.isStpEnabled;
    },

    isMapAvailable: function()
    {
        return M2ePro.formData.isMapEnabled;
    },

    isStpAdvancedAvailable: function()
    {
        return M2ePro.formData.isStpAdvancedEnabled;
    },

    //----------------------------------

    listing_type_change: function(event)
    {
        var self             = EbayTemplateSellingFormatHandlerObj,

            bestOfferBlock   = $('magento_block_ebay_template_selling_format_edit_form_best_offer'),
            bestOfferMode    = $('best_offer_mode'),
            attributeElement = $('listing_type_attribute');

        $('start_price_tr', 'reserve_price_tr').invoke('show');
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {
            $('start_price_tr','reserve_price_tr').invoke('hide');
            $$('#variation_price_tr .value').invoke('show');
        }

        attributeElement.innerHTML = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }

        bestOfferBlock.show();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            bestOfferBlock.hide();
            bestOfferMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_MODE_NO');
            bestOfferMode.simulate('change');
        }

        self.updateQtyMode();
        self.updateQtyPercentage();
        self.updateIgnoreVariations();
        self.updateListingDuration();
        self.updateBuyItNowPrice();
        self.updatePriceDiscountStpVisibility();
        self.updatePriceDiscountMapVisibility();
        self.updateVariationPriceTrVisibility();
    },

    duration_mode_change : function()
    {
        var outOfStockControlTr = $('out_of_stock_control_tr'),
            outOfStockControlMode = $('out_of_stock_control_mode');

        outOfStockControlTr.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC') &&
            !EbayTemplateSellingFormatHandlerObj.isSimpleMode()) {
            outOfStockControlTr.show();
            outOfStockControlMode.value = M2ePro.formData.outOfStockControl;
        } else {
            outOfStockControlMode.value = 0;
        }
    },

    updateQtyMode : function()
    {
        var qtyMode   = $('qty_mode'),
            qtyModeTr = $('qty_mode_tr');

        qtyModeTr.show();
        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            qtyMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_SINGLE');
            qtyMode.simulate('change');
            qtyModeTr.hide();
        }
    },

    updateQtyPercentage : function()
    {
        var qtyPercentageTr = $('qty_percentage_tr');

        qtyPercentageTr.hide();

        if (EbayTemplateSellingFormatHandlerObj.isSimpleMode()) {
            return;
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            return;
        }

        var qtyMode = $('qty_mode').value;

        if (qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_SINGLE') ||
            qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_NUMBER')) {
            return;
        }

        qtyPercentageTr.show();
    },

    updateIgnoreVariations : function()
    {
        var ignoreVariationsValueTr = $('ignore_variations_value_tr'),
            ignoreVariationsValue = $('ignore_variations_value');

        ignoreVariationsValueTr.hide();

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            ignoreVariationsValue.value = 0;
        } else {
            ignoreVariationsValueTr.show();
        }
    },

    updateListingDuration : function()
    {
        var durationMode          = $('duration_mode'),
            durationAttribute     = $('duration_attribute'),
            durationAttributeNote = $('duration_attribute_note');

        var outOfStockControlTr = $('out_of_stock_control_tr'),
            outOfStockControlMode = $('out_of_stock_control_mode');

        $('durationId1', 'durationId30', 'durationId100').invoke('show');

        durationMode.show();
        durationAttribute.hide();
        durationAttributeNote.hide();

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {

            durationMode.value = 3;

            $('durationId1').hide();
            if (M2ePro.formData.duration_mode && M2ePro.formData.duration_mode != 1) {
                durationMode.value = M2ePro.formData.duration_mode;
            }

            durationMode.simulate('change');
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {

            durationMode.value = 3;

            $('durationId30', 'durationId100').invoke('hide');
            if (M2ePro.formData.duration_mode && M2ePro.formData.duration_mode != 30 && M2ePro.formData.duration_mode != 100) {
                durationMode.value = M2ePro.formData.duration_mode;
            }

            outOfStockControlTr.hide();
            outOfStockControlMode.value = 0;

            if (EbayTemplateSellingFormatHandlerObj.isSimpleMode()) {
                durationMode.hide();
            }
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE')) {
            durationMode.hide();
            if (!EbayTemplateSellingFormatHandlerObj.isSimpleMode()) {
                durationAttribute.show();
                durationAttributeNote.show();
            }

            outOfStockControlTr.hide();
            outOfStockControlMode.value = 0;
        }
    },

    updateVariationPriceTrVisibility : function()
    {
        var removeBottomBorderTds = $$('#buyitnow_price_tr td.remove_bottom_border'),
            addRowspanTds         = $$('#buyitnow_price_tr td.add_rowspan'),
            priceModeSelect       = $('buyitnow_price_mode'),
            variationPriceTr      = $('variation_price_tr');

        variationPriceTr.hide();
        removeBottomBorderTds.invoke('removeClassName','bottom_border_disabled');
        addRowspanTds.invoke('removeAttribute','rowspan');

        if (!EbayTemplateSellingFormatHandlerObj.isSimpleMode() &&
            $('listing_type').value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            variationPriceTr.show();
            addRowspanTds.invoke('setAttribute','rowspan','2');
            if(priceModeSelect.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
                removeBottomBorderTds.invoke('addClassName','bottom_border_disabled');
            }
        }
    },

    updateBuyItNowPrice : function()
    {
        var priceNoneOption                 = $('buyitnow_price_mode_none_option'),
            priceModeSelect                 = $('buyitnow_price_mode'),

            priceLabel                      = $('buyitnow_price_label'),
            priceNote                       = $('buyitnow_price_note'),
            bestOfferAcceptPercentageOption = $('best_offer_accept_percentage_option'),
            bestOfferRejectPercentageOption = $('best_offer_reject_percentage_option');

        priceNoneOption.show();

        priceLabel.innerHTML = M2ePro.translator.translate('"Buy It Now" Price') + ': ';
        priceNote.innerHTML = M2ePro.translator.translate('The fixed price for immediate purchase.<br/>Find out more about <a href="http://sellercentre.ebay.co.uk/add-buy-it-now-price-auction" target="_blank">adding a Buy It Now price</a> to your listing.');
        bestOfferAcceptPercentageOption.innerHTML = M2ePro.translator.translate('% of "Buy It Now" Price');
        bestOfferRejectPercentageOption.innerHTML = M2ePro.translator.translate('% of "Buy It Now" Price');

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {

            priceNoneOption.hide();

            if (priceModeSelect.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
                priceModeSelect.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT');
                priceModeSelect.simulate('change');
            }

            priceLabel.innerHTML = M2ePro.translator.translate('Price') + ': ';
            priceNote.innerHTML = M2ePro.translator.translate('The fixed price for immediate purchase.');
            bestOfferAcceptPercentageOption.innerHTML = M2ePro.translator.translate('% of Price');
            bestOfferRejectPercentageOption.innerHTML = M2ePro.translator.translate('% of Price');
        }
    },

    updatePriceDiscountStpVisibility: function()
    {
        var priceDiscTrStp = $('price_discount_stp_tr'),
            priceDiscStpMode = $('price_discount_stp_mode');

        priceDiscTrStp.hide();
        if (EbayTemplateSellingFormatHandlerObj.isStpAvailable()) {
            priceDiscTrStp.show();
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            priceDiscTrStp.hide();
            priceDiscStpMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE');
            priceDiscStpMode.simulate('change');
        }
    },

    updatePriceDiscountMapVisibility: function()
    {
        var priceDiscTrMap = $('price_discount_map_tr'),
            priceDiscMapMode = $('price_discount_map_mode');

        priceDiscTrMap.hide();
        if (EbayTemplateSellingFormatHandlerObj.isMapAvailable()) {
            priceDiscTrMap.show();
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            priceDiscTrMap.hide();
            priceDiscMapMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE');
            priceDiscMapMode.simulate('change');
        }
    },

    //----------------------------------

    qty_mode_change: function()
    {
        var self               = EbayTemplateSellingFormatHandlerObj,

            customValueTr      = $('qty_mode_cv_tr'),
            attributeElement   = $('qty_custom_attribute'),

            maxPostedValueTr   = $('qty_max_posted_value_mode_tr'),
            maxPostedValueMode = $('qty_max_posted_value_mode');

        customValueTr.hide();
        attributeElement.value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_NUMBER')) {
            customValueTr.show();
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }

        maxPostedValueTr.hide();
        maxPostedValueMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MAX_POSTED_MODE_OFF');

        if (self.isMaxPostedQtyAvailable(this.value)) {

            maxPostedValueTr.show();
            maxPostedValueMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MAX_POSTED_MODE_ON');

            if (self.isMaxPostedQtyAvailable(M2ePro.formData.qty_mode)) {
                maxPostedValueMode.value = M2ePro.formData.qty_max_posted_value_mode;
            }
        }

        maxPostedValueMode.simulate('change');

        self.updateQtyPercentage();
    },

    isMaxPostedQtyAvailable: function(qtyMode)
    {
        return qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT') ||
               qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE') ||
               qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED');
    },

    qtyMaxPostedMode_change: function()
    {
        var maxPosterValueTr = $('qty_max_posted_value_tr');

        maxPosterValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MAX_POSTED_MODE_ON')) {
            maxPosterValueTr.show();
        }
    },

    //----------------------------------

    taxCategoryChange: function()
    {
        var self = EbayTemplateSellingFormatHandlerObj,
            valueEl     = $('tax_category_value'),
            attributeEl = $('tax_category_attribute');

        valueEl.value     = '';
        attributeEl.value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::TAX_CATEGORY_MODE_VALUE')) {
            self.updateHiddenValue(this, valueEl);
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::TAX_CATEGORY_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    },

    //----------------------------------

    start_price_mode_change : function()
    {
        var self             = EbayTemplateSellingFormatHandlerObj,
            attributeElement = $('start_price_custom_attribute');

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    reserve_price_mode_change : function()
    {
        var self             = EbayTemplateSellingFormatHandlerObj,
            attributeElement = $('reserve_price_custom_attribute'),
            priceChangeTd    = $('reserve_price_change_td'),
            currencyTd       = $('reserve_price_currency_td');

        priceChangeTd.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
            priceChangeTd.show();
            currencyTd && currencyTd.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    buyitnow_price_mode_change : function()
    {
        var self = EbayTemplateSellingFormatHandlerObj,

            listingType             = $('listing_type'),
            attributeElement        = $('buyitnow_price_custom_attribute'),
            priceChangeTd           = $('buyitnow_price_change_td'),
            priceChangeTds          = $$('#buyitnow_price_tr td.remove_bottom_border'),
            currencyTd              = $('buyitnow_price_currency_td'),
            variationPriceSelect    = $$('#variation_price_tr .value');

        variationPriceSelect.invoke('hide');
        priceChangeTds.invoke('removeClassName','bottom_border_disabled');
        priceChangeTd.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
            priceChangeTd.show();
            currencyTd && currencyTd.show();
            variationPriceSelect.invoke('show');
            if(listingType.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
                priceChangeTds.invoke('addClassName','bottom_border_disabled');
            }
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    price_coefficient_mode_change : function()
    {
        var coefficientInputDiv = $(this.id.replace('mode','') + 'input_div'),
            signSpan            = $(this.id.replace('mode','') + 'sign_span'),
            percentSpan         = $(this.id.replace('mode','') + 'percent_span'),
            examplesContainer   = $(this.id.replace('coefficient_mode','') + 'example_container');

        //-----------------------------
        coefficientInputDiv.show();

        console.log(examplesContainer);
        examplesContainer.show();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_NONE')) {
            coefficientInputDiv.hide();
            examplesContainer.hide();
        }
        //-----------------------------

        //-----------------------------
        signSpan.innerHTML    = '';
        percentSpan.innerHTML = '';
        $$('.' + this.id.replace('coefficient_mode','') + 'example').invoke('hide');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_INCREASE')) {
            signSpan.innerHTML = '+';

            if (typeof M2ePro.formData.currency != 'undefined') {
                percentSpan.innerHTML = M2ePro.formData.currency;
            }

            $(this.id.replace('coefficient_mode','') + 'example_absolute_increase').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE')) {
            signSpan.innerHTML = '-';

            if (typeof M2ePro.formData.currency != 'undefined') {
                percentSpan.innerHTML = M2ePro.formData.currency;
            }

            $(this.id.replace('coefficient_mode','') + 'example_absolute_decrease').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE')) {
            signSpan.innerHTML = '+';
            percentSpan.innerHTML = '%';

            $(this.id.replace('coefficient_mode','') + 'example_percentage_increase').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE')) {
            signSpan.innerHTML = '-';
            percentSpan.innerHTML = '%';

            $(this.id.replace('coefficient_mode','') + 'example_percentage_decrease').show();
        }
        //-----------------------------
    },

    price_discount_stp_mode_change : function()
    {
        var attributeElement         = $('price_discount_stp_attribute'),
            priceDiscountStpTds      = $$('#price_discount_stp_tr td.remove_bottom_border'),
            priceDiscountStpReasonTr = $('price_discount_stp_reason_tr'),
            currencyTd               = $('price_discount_stp_currency_td');

        priceDiscountStpReasonTr.hide();
        currencyTd && currencyTd.hide();
        priceDiscountStpTds.invoke('removeClassName','bottom_border_disabled');

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
            currencyTd && currencyTd.show();

            if (EbayTemplateSellingFormatHandlerObj.isStpAdvancedAvailable()) {
                priceDiscountStpReasonTr.show();
                priceDiscountStpTds.invoke('addClassName','bottom_border_disabled');
            }
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE')) {
            EbayTemplateSellingFormatHandlerObj.selectMagentoAttribute(this, attributeElement);
        }
    },

    price_discount_map_mode_change : function()
    {
        var attributeElement           = $('price_discount_map_attribute'),
            priceDiscountMapExposureTr = $('price_discount_map_exposure_tr'),
            currencyTd                 = $('price_discount_map_currency_td');

        priceDiscountMapExposureTr.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
            currencyTd && currencyTd.show();

            if (EbayTemplateSellingFormatHandlerObj.isMapAvailable()) {
                priceDiscountMapExposureTr.show();
            }
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_ATTRIBUTE')) {
            EbayTemplateSellingFormatHandlerObj.selectMagentoAttribute(this, attributeElement);
        }
    },

    //----------------------------------

    charity_id_change: function()
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        if (this[this.selectedIndex].hasClassName('searchNewCharity')) {
            EbayTemplateSellingFormatHandlerObj.openPopUpCharity(M2ePro.translator.translate('Search For Charities'));

            if (typeof self.charitySelectedHistory != 'undefined') {
                this.selectedIndex = self.charitySelectedHistory;
            }

            return;
        }

        self.charitySelectedHistory = this.selectedIndex;

        var charityPercent = $('charity_percent');
        var charityPercentSelf = $('charity_percent_self');
        var charityName = $('charity_name');

        if (this.selectedIndex != 0) {
            $$('.charity_percent_tr').invoke('show');
            charityPercent.addClassName('M2ePro-validation-charity-percentage');

            if (charityPercentSelf) {
               charityPercent.simulate('change');
            }

            $('charity_percent_none').show();
            charityPercent.selectedIndex = 0;
            self.prepareCharity();
        } else {
            charityPercent.removeClassName('M2ePro-validation-charity-percentage');

            if (charityName) {
                charityName.remove();
            }

            $$('.charity_percent_tr').invoke('hide');
        }
    },

    charity_percent_change: function()
    {
        var charityPercent = $('charity_percent');
        var charityPercentSelf = $('charity_percent_self');

        if (charityPercentSelf) {
            charityPercentSelf.remove();
            charityPercent.select('option').each(function(el){
                if (el.value == charityPercentSelf.value) {
                    el.writeAttribute('selected', 'selected');
                    el.focus();
                    $('charity_percent_none').hide();

                    return;
                }
            });
        }

        $('charity_percent_none').hide();
    },

    //----------------------------------

    best_offer_mode_change : function()
    {
        var bestOfferRespondTable = $('best_offer_respond_table');

        bestOfferRespondTable.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_MODE_YES')) {
            bestOfferRespondTable.show();
            $('best_offer_reject_mode','best_offer_accept_mode').invoke('simulate','change');
        }
    },

    best_offer_accept_mode_change : function()
    {
        var self                   = EbayTemplateSellingFormatHandlerObj,

            bestOfferAcceptValueTr = $('best_offer_accept_value_tr'),
            attributeElement       = $('best_offer_accept_custom_attribute');

        bestOfferAcceptValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_PERCENTAGE')) {
            bestOfferAcceptValueTr.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    best_offer_reject_mode_change : function()
    {
        var self                   = EbayTemplateSellingFormatHandlerObj,
            bestOfferRejectValueTr = $('best_offer_reject_value_tr'),
            attributeElement       = $('best_offer_reject_custom_attribute');

        bestOfferRejectValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_PERCENTAGE')) {
            bestOfferRejectValueTr.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    //----------------------------------

    selectMagentoAttribute : function(elementSelect, elementAttribute)
    {
        var attributeCode = elementSelect.options[elementSelect.selectedIndex].getAttribute('attribute_code');
        elementAttribute.value = attributeCode;
    },

    //----------------------------------

    checkMessages: function()
    {
        if (typeof EbayListingTemplateSwitcherHandlerObj == 'undefined') {
            // not inside template switcher
            return;
        }

        var id = '',
            nick = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT'),
            data = Form.serialize('template_selling_format_data_container'),
            storeId = EbayListingTemplateSwitcherHandlerObj.storeId,
            marketplaceId = EbayListingTemplateSwitcherHandlerObj.marketplaceId,
            checkAttributesAvailability = EbayListingTemplateSwitcherHandlerObj.checkAttributesAvailability,
            container = 'template_selling_format_messages',
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages();
                    }.bind(this))
                }
            }.bind(this);

        TemplateHandlerObj
            .checkMessages(
                id,
                nick,
                data,
                storeId,
                marketplaceId,
                checkAttributesAvailability,
                container,
                callback
            );
    },

    //----------------------------------

    openPopUpCharity: function(title)
    {
        var self = EbayTemplateSellingFormatHandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template_sellingFormat/getSearchCharityPopUpHtml'),
        {
            method: 'post',
            parameters: {},
            onSuccess: function(transport)
            {
                self.popUp = Dialog.info(transport.responseText, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    top: 80,
                    width: 750,
                    height: 525,
                    zIndex: 100,
                    recenterAuto: false,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                $('query').observe('keypress',function(event) {
                    event.keyCode == Event.KEY_RETURN && self.searchCharity();
                });

                $('searchCharity_reset').observe('click', function(event){
                    $('query').value = '';
                    $('selectCharitySearch').selectedIndex = '';
                    $('searchCharity_grid').hide();
                    $('searchCharity_warning_block').hide();
                })
            }
        });

    },

    searchCharity: function()
    {
        var query = $('query').value;
        var destination = $('selectCharitySearch').value;

        if (query == ''){
            $('query').focus();
            alert(M2ePro.translator.translate('Please, enter the organization name or ID.'));
            return;
        }

        $('searchCharity_grid').hide();
        $('searchCharity_error_block').hide();
        $('searchCharity_warning_block').hide();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template_sellingFormat/searchCharity'),
        {
            method: 'post',
            parameters: {
                query: query,
                destination: destination
            },
            onSuccess: function(transport)
            {
                transport = transport.responseText.evalJSON();

                if(transport.result == 'success') {
                    $('searchCharity_grid')
                        .update(transport.data)
                        .show();

                    if (transport.count) {
                        $('searchCharity_warning_block').show();
                        $('searchCharity_warning_message').update(M2ePro.translator.translate('If you do not see the organization you were looking for, try to enter another keywords and run the search again.'));
                    }
                } else {
                    $('searchCharity_error_block').show();
                    $('searchCharity_error_message').update(transport.data);
                }
            }
        })
    },

    selectNewCharity: function(id, name)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        this.popUp.close();

        var charityId = $('charity_id');
        var optgroup = $('customCharity');

        charityId.select('option').each(function(el) {
            el.writeAttribute('selected', false);
        });

        if ($('newCharity')) {
            $('newCharity')
                .update(name)
                .writeAttribute('value', id)
                .writeAttribute('selected', 'selected')
                .focus();
        } else {
            if (optgroup) {
                optgroup.insert({
                    bottom: new Element('option', {
                        value: id,
                        selected: 'selected',
                        id: 'newCharity'
                    }).update(name)
                });
            } else {
                optgroup = new Element('optgroup', {
                    label: 'Custom',
                    id: 'customCharity'
                }).insert({
                        bottom: new Element('option', {
                            value: id,
                            selected: 'selected',
                            id: 'newCharity'
                        }).update(name)
                    });
            }

            var featuresGroup = charityId.select('optgroup')[0];
            if(featuresGroup) {
                featuresGroup.insert({
                    before: optgroup
                });
            } else {
                charityId.insert(optgroup);
            }
        }

        charityId.simulate('change');
    },

    prepareCharity: function()
    {
        var charityId = $('charity_id');

        var charityName = charityId.selectedIndex > 0 ? charityId.options[charityId.selectedIndex].innerHTML : '';

        if (charityName == ''){
            return;
        }

        if ($('charity_name')) {
            $('charity_name').value = charityName;
            return;
        }

        $('charity_id').insert({
            after: new Element('input', {
                type: 'hidden',
                value: charityName,
                id: 'charity_name',
                name: 'selling_format[charity_name]'
            })
        });
    }

    //-----------------------------------------
});