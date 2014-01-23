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

            if (!coefficient.value.match(/^\d+[.,]?\d*?$/g) || parseFloat(coefficient.value) <= 0) {
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
        $$('.simple_mode_disallowed').invoke('hide');
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

        self.updateQtyMode(this.value);
        self.updateIgnoreVariations(this.value);
        self.updateListingDuration(this.value);
        self.updateBuyItNowPrice(this.value);
        self.updatePriceDiscountVisibility(this.value);
        self.updateVariationPriceTrVisibility(this.value);
    },

    duration_mode_change : function()
    {
        var outOfStockControlTr = $('out_of_stock_control_tr'),
            outOfStockControlMode = $('out_of_stock_control_mode');

        outOfStockControlTr.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC')) {
            outOfStockControlTr.show();
            outOfStockControlMode.value = M2ePro.formData.outOfStockControl;
        } else {
            outOfStockControlMode.value = 0;
        }
    },

    updateQtyMode : function(listingType)
    {
        var qtyMode   = $('qty_mode'),
            qtyModeTr = $('qty_mode_tr');

        qtyModeTr.show();
        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            qtyMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_SINGLE');
            qtyMode.simulate('change');
            qtyModeTr.hide();
        }
    },

    updateIgnoreVariations : function(listingType)
    {
        var ignoreVariationsValueTr = $('ignore_variations_value_tr'),
            ignoreVariationsValue = $('ignore_variations_value');

        ignoreVariationsValueTr.hide();

        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            ignoreVariationsValue.value = 0;
        } else {
            ignoreVariationsValueTr.show();
        }
    },

    updateListingDuration : function(listingType)
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

        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {

            durationMode.value = 3;

            $('durationId1').hide();
            if (M2ePro.formData.duration_mode && M2ePro.formData.duration_mode != 1) {
                durationMode.value = M2ePro.formData.duration_mode;
            }

            durationMode.simulate('change');
        }

        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {

            durationMode.value = 3;

            $('durationId30', 'durationId100').invoke('hide');
            if (M2ePro.formData.duration_mode && M2ePro.formData.duration_mode != 30 && M2ePro.formData.duration_mode != 100) {
                durationMode.value = M2ePro.formData.duration_mode;
            }

            outOfStockControlTr.hide();
            outOfStockControlMode.value = 0;
        }

        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE')) {
            durationMode.hide();
            durationAttribute.show();
            durationAttributeNote.show();

            outOfStockControlTr.hide();
            outOfStockControlMode.value = 0;
        }
    },

    updateVariationPriceTrVisibility : function(listingType)
    {
        var removeBottomBorderTds = $$('#buyitnow_price_tr td.remove_bottom_border'),
            addRowspanTds         = $$('#buyitnow_price_tr td.add_rowspan'),
            variationPriceTr      = $('variation_price_tr');

        variationPriceTr.hide();
        removeBottomBorderTds.invoke('removeClassName','bottom_border_disabled');
        addRowspanTds.invoke('removeAttribute','rowspan');

        if (!EbayTemplateSellingFormatHandlerObj.isSimpleMode() &&
            listingType != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            variationPriceTr.show();
            removeBottomBorderTds.invoke('addClassName','bottom_border_disabled');
            addRowspanTds.invoke('setAttribute','rowspan','2');
        }
    },

    updateBuyItNowPrice : function(listingType)
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

        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {

            priceNoneOption.hide();

            if (priceModeSelect.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
                priceModeSelect.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_PRODUCT');
            }

            priceLabel.innerHTML = M2ePro.translator.translate('Price') + ': ';
            priceNote.innerHTML = M2ePro.translator.translate('The fixed price for immediate purchase.');
            bestOfferAcceptPercentageOption.innerHTML = M2ePro.translator.translate('% of Price');
            bestOfferRejectPercentageOption.innerHTML = M2ePro.translator.translate('% of Price');
        }
    },

    updatePriceDiscountVisibility: function(listingType)
    {
        var priceDiscTr = $('price_discount_stp_tr'),
            priceDiscStpMode = $('price_discount_stp_mode');

        priceDiscTr.show();

        if (EbayTemplateSellingFormatHandlerObj.isSimpleMode()) {
            priceDiscTr.hide();
        }

        if (listingType == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            priceDiscTr.hide();
            priceDiscStpMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE');
            priceDiscStpMode.simulate('change');
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

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {

            maxPostedValueTr.show();

            maxPostedValueMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MAX_POSTED_MODE_ON');

            if (M2ePro.formData.qty_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_PRODUCT') ||
                M2ePro.formData.qty_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {
                maxPostedValueMode.value = M2ePro.formData.qty_max_posted_value_mode;
            }
        }

        maxPostedValueMode.simulate('change');
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
        var self             = EbayTemplateSellingFormatHandlerObj,

            attributeElement = $('buyitnow_price_custom_attribute'),
            priceChangeTd    = $('buyitnow_price_change_td'),
            currencyTd       = $('buyitnow_price_currency_td');

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

    price_coefficient_mode_change : function()
    {
        var coefficientInputDiv = $(this.id.replace('mode','') + 'input_div'),
            signSpan            = $(this.id.replace('mode','') + 'sign_span'),
            percentSpan         = $(this.id.replace('mode','') + 'percent_span');

        //-----------------------------
        coefficientInputDiv.show();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_NONE')) {
            coefficientInputDiv.hide();
        }
        //-----------------------------

        //-----------------------------
        signSpan.innerHTML = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_INCREASE') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE')) {
            signSpan.innerHTML = '+';
        }
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE')) {
            signSpan.innerHTML = '-';
        }
        //-----------------------------

        //-----------------------------
        percentSpan.innerHTML = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE')) {
            percentSpan.innerHTML = '%';
        }
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_INCREASE')) {

            if (typeof M2ePro.formData.currency != 'undefined') {
                percentSpan.innerHTML = M2ePro.formData.currency;
            }
        }
        //-----------------------------
    },

    price_discount_stp_mode_change : function()
    {
        var attributeElement = $('price_discount_stp_attribute'),
            priceDiscountStpTypeContainer = $('price_discount_stp_type_container'),
            currencyTd       = $('price_discount_stp_currency_td');

        priceDiscountStpTypeContainer.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_NONE')) {
            priceDiscountStpTypeContainer.show();
            currencyTd && currencyTd.show();
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

            charityId.select('optgroup')[0].insert({
                before: optgroup
            });
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