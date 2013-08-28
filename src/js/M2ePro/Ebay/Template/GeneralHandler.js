EbayTemplateGeneralHandler = Class.create();
EbayTemplateGeneralHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(multivariationMarketplaces)
    {
        this.multiVariationEnabledMarketplaces = multivariationMarketplaces;

        this.setValidationCheckRepetitionValue('M2ePro-listing-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_General', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-validate-vat', M2ePro.text.validate_vat_error, function(value) {
            if (!value) {
                return true;
            }

            if (value.length > 6) {
                return false;
            }

            value = Math.ceil(value);

            return value >= 0 && value <= 30;
        });

        Validation.add('M2ePro-validate-shipping-methods', M2ePro.text.no_shipping_method_error, function(value, el) {
            if (value != 0 && value != 1) {
                return true;
            }

            var type = el.name.split('_')[0];

            return EbayTemplateGeneralShippingHandlerObj.counter[type] != 0;
        });

        Validation.add('M2ePro-validate-payment-methods', M2ePro.text.validate_payment_method_error, function(value) {
            var isChecked = false;
            $$('input[name="payments[]"]').each(function(o) {
                if (o.checked) {
                    isChecked = true;
                }
            });

            return isChecked;
        });

        Validation.add('M2ePro-validate-international-trade', M2ePro.text.validate_international_trade_error, function(value) {
            var isChecked = false;
            if ($('international_trade_uk').value != 1 &&
                $('international_trade_au').value != 1 &&
                $('international_trade_na').value != 1) {

                isChecked = true;
            }

            if (value != null) {
                isChecked = true;
            }

            return isChecked;
        });

        Validation.add('M2ePro-validate-cash-on-delivery', M2ePro.text.validate_cash_on_delivery_error, function(value) {
            if (!$('local_shipping_cash_on_delivery_cost_mode_tr').visible() ||
                $('local_shipping_cash_on_delivery_cost_mode').value == EbayTemplateGeneralHandlerObj.CASH_ON_DELIVERY_COST_MODE_NONE) {
                return true;
            }

            return $$('input[value="COD"]')[0].checked;
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

        this.setValidationCheckRepetitionValue('M2ePro-listing-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_General', 'title', '',
                                                '');

        CommonHandlerObj.duplicate_click($headId);
    },

    //----------------------------------

    attribute_sets_confirm: function()
    {
        var self = EbayTemplateGeneralHandlerObj;

        AttributeSetHandlerObj.confirmAttributeSets();

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('categories_main_attribute', 'main_category_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('categories_secondary_attribute', 'secondary_category_attribute_td', null, true);
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('tax_category_attribute', 'tax_category_attribute_td', null, true);

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('condition_attribute', 'item_condition_attribute_container_td');

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('motors_specifics_attribute', 'motors_specifics_attribute_td', null, true);
        $('motors_specifics_attribute').addClassName('M2ePro-validate-motors-specifics-attribute');

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('local_shipping_cash_on_delivery_cost_attribute', 'local_shipping_cash_on_delivery_cost_attribute_td');

        M2ePro.customData.attributesReceiving.each(function(item) {
            AttributeSetHandlerObj.renderAttributesWithEmptyOption(item.id, item.container);
        });

        // show filds wich depends on attributes set
        $$('.requirie-attribute-set').invoke('show');
    },

    //----------------------------------

    marketplace_id_change: function()
    {
        var self = EbayTemplateGeneralHandlerObj;

        self.hideEmptyOption($('marketplace_id'));

        if ($F('categories_mode') != '' && $F('categories_mode') == 0) {

            if (M2ePro.customData.categoriesAlreadyRendered) {
                EbayTemplateGeneralCategoryHandlerObj.resetCategories();
            } else {
                M2ePro.customData.categoriesAlreadyRendered = true;
            }

            $$('.ebay-cat').invoke('hide');
            $('main_ebay_category_confirm_div', 'secondary_ebay_category_confirm_div').invoke('hide');
            $('categories_mode').simulate('change');
        }

        // payments, shippings, refund
        self.loadAllAboutMarketplace();

        self.setVisibilityForMultiVariation(false);

        //$('refund_notice_tr').hide();
        $$('.refund').invoke('show');
    },

    loadAllAboutMarketplace: function()
    {
        var self = this;

        self.loadMarketplaceInformation(function(data) {
            EbayTemplateGeneralShippingHandlerObj.prepareData(
            {shipping: data.shipping, shipping_locations: data.shipping_locations, packages: data.packages},  $('marketplace_id').value);
            self.loadPayments(data.payments);
            self.loadRefund(data.return_policy);
            self.loadTaxCategories(data.tax_categories);
        });
    },

    loadMarketplaceInformation: function(handler)
    {
        var url = M2ePro.url.getMarketplaceInfo + 'id/' + $('marketplace_id').value;
        new Ajax.Request(url, {onSuccess: function(transport) {
            handler(transport.responseText.evalJSON());
        }});
    },

    //----------------------------------

    account_id_change: function()
    {
        var self = EbayTemplateGeneralHandlerObj;

        self.hideEmptyOption($('account_id'));

        if ($('account_id').value) {
            $('magento_block_ebay_template_general_general_account_store').show();
            self.getEbayStoreByAccount();
        } else {
            $('local_shipping_combined_discount_mode').value = 0;
            $('international_shipping_combined_discount_mode').value = 0;
            $('magento_block_ebay_template_general_general_account_store').hide();
        }

        EbayTemplateGeneralShippingHandlerObj.renderShippingDiscountProfiles('local');
        EbayTemplateGeneralShippingHandlerObj.renderShippingDiscountProfiles('international');
    },

    setVisibilityForMultiVariation: function(variationEnabled)
    {
        var self = EbayTemplateGeneralHandlerObj;
        var marketplaceId = parseInt($('marketplace_id').value);

        var marketplaceVariationEnabled = self.multiVariationEnabledMarketplaces.indexOf(marketplaceId) != -1;

        $('magento_block_ebay_template_general_general_multivariation').show();

        var sku_mode = $('sku_mode');
        var fake_sku_mode = $('fake_sku_mode');

        if (marketplaceVariationEnabled) {

            $('block_notice_ebay_template_general_multivariation_marketplace_disabled').hide();

            if (variationEnabled) {

                if (parseInt($('variation_ignore').value) == 0) {
                    sku_mode.disabled = 1;
                    sku_mode.setValue(1);
                    sku_mode.setAttribute('name','fake_sku_mode');
                    fake_sku_mode.setAttribute('name','sku_mode');
                } else {
                    sku_mode.disabled = 0;
                    sku_mode.setAttribute('name','sku_mode');
                    fake_sku_mode.setAttribute('name','fake_sku_mode');
                }

                $('block_notice_ebay_template_general_multivariation_category_enabled').show();
                $('multivariation_settings').show();
                $('block_notice_ebay_template_general_multivariation_category_disabled').hide();
                $('variation_enabled').value = 1;
            } else {

                sku_mode.disabled = 0;
                sku_mode.setAttribute('name','sku_mode');
                fake_sku_mode.setAttribute('name','fake_sku_mode');

                $('block_notice_ebay_template_general_multivariation_category_enabled').hide();
                $('multivariation_settings').hide();
                $('block_notice_ebay_template_general_multivariation_category_disabled').show();
                $('variation_enabled').value = 0;
            }

        } else {

            sku_mode.disabled = 0;
            sku_mode.setAttribute('name','sku_mode');
            fake_sku_mode.setAttribute('name','fake_sku_mode');

            $('block_notice_ebay_template_general_multivariation_category_enabled').hide();
            $('block_notice_ebay_template_general_multivariation_category_disabled').hide();
            $('multivariation_settings').hide();
            $('block_notice_ebay_template_general_multivariation_marketplace_disabled').show();
        }
    },

    getEbayStoreByAccount: function()
    {
        var self = EbayTemplateGeneralHandlerObj;

        var renderer = self._storeSelectRenderer;
        var reloader = self._reloadSomething;

        var successHandler = function(data)
        {
            if (data.information.title == '') {
                $('magento_block_ebay_template_general_general_account_store').hide();
                return;
            }

            $('store_name').innerHTML = data.information.title;
            renderer(data.categories, 'store_categories_main_id');
            renderer(data.categories, 'store_categories_secondary_id');

            var selectedMainCategoryId = M2ePro.formData.store_categories_main_id;
            var selectedSecondaryCategoryId = M2ePro.formData.store_categories_secondary_id;

            if (selectedMainCategoryId != 0) {
                $('store_categories_main_id').value = selectedMainCategoryId;
            }

            if (selectedSecondaryCategoryId != 0) {
                $('store_categories_secondary_id').value = selectedSecondaryCategoryId;
            }
        }

        reloader(M2ePro.url.getEbayStoreByAccount + 'account_id/' + $('account_id').value, '', successHandler);
    },

    updateEbayStoreByAccount_click: function()
    {
        var self = EbayTemplateGeneralHandlerObj;

        new Ajax.Request(
            M2ePro.url.updateEbayStoreByAccount + 'account_id/' + $('account_id').value,
            {
                onSuccess: self.getEbayStoreByAccount
            }
        );
    },

    //----------------------------------

    loadPayments: function(data)
    {
        var self = EbayTemplateGeneralHandlerObj;

        var txt = '';
        data.each(function(item) {
            if (item.ebay_id != 'PayPal') {
                txt += '<div id="payment_' + item.ebay_id + '_div" class="payment-method-container"><label>' +
                       '<input type="checkbox" name="payments[]" value="' + item.ebay_id + '" class="M2ePro-validate-payment-methods" /> ' + item.title +
                       '</label></div>';
            }
        });

        $('payment_methods_td').innerHTML = txt;
        $('payment_methods_tr').show();

        $$('input[name="payments[]"]').each(function(item) {
            if (M2ePro.formData.payments.indexOf(item.value) != -1) {
                item.checked = true;
            }
        });

        if (EbayTemplateGeneralShippingHandlerObj.cashOnDeliverySites.indexOf(EbayTemplateGeneralShippingHandlerObj.marketplaceId) != -1) {
            $$('input[value="COD"]')[0].observe('click', EbayTemplateGeneralShippingHandlerObj.setCashOnDeliveryVisibility);
        }

        EbayTemplateGeneralShippingHandlerObj.setCashOnDeliveryVisibility();
    },

    payments_change: function(checked)
    {
        $('paypal_address_tr')[checked ? 'show' : 'hide']();
        $('immediate_payment_tr')[checked ? 'show' : 'hide']();

        if (checked == false) {
            $('pay_pal_immediate_payment').checked = false;
            EbayTemplateGeneralHandlerObj.immediate_payment_change(false);
        }
    },

    immediate_payment_change: function(checked)
    {
        $('magento_block_ebay_template_general_payment_additional')[checked ? 'hide' : 'show']();

        $$('input[name="payments[]"]').each(function(payment) {
            if (payment.value != 'PayPal' && checked) {
                payment.checked = false;
            }
        });
    },

    filterPayments: function(payments)
    {
        var self = EbayTemplateGeneralHandlerObj;

        $('payment_methods_tr').show();
        $$('.payment-method-container input').each(function(item) {
            if (payments.indexOf(item.value) == -1) {
                item.checked = false;
                $('payment_' + item.value + '_div').hide();
            }
        });

        self.payments_change($('pay_pal_method').checked)
        self.immediate_payment_change($('pay_pal_immediate_payment').checked);
    },

    unFilterPayments: function()
    {
        $('payment_methods_tr').show();
        $$('.payment-method-container').invoke('show');
    },

    //----------------------------------

    refund_accepted_change: function()
    {
        if (this.value == 'ReturnsAccepted') {
            $('refund_option_tr')[$$('#refund_option option').length ? 'show' : 'hide']();
            $('refund_within_tr')[$$('#refund_within option').length ? 'show' : 'hide']();
            $('refund_shippingcost_tr')[$$('#refund_shippingcost option').length ? 'show' : 'hide']();
            $('refund_restockingfee_tr')[$$('#refund_restockingfee option').length ? 'show' : 'hide']();

            $('refund_description_tr').show();
        } else {
            $$('.refund-accepted').invoke('hide');
        }
    },

    _simpleSelectRenderer: function(data, id)
    {
        var options = '';
        data.each(function(paris) {
            var key;
            if (typeof paris.key != 'undefined') {
                key = paris.key;
            } else if (typeof paris.ebay_id != 'undefined') {
                key = paris.ebay_id;
            } else if (typeof paris.category_id != 'undefined') {
                key = paris.category_id;
            } else {
                key = paris.id;
            }
            var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
            options += '<option value="' + key + '">' + val + '</option>\n';
        });

        $(id).update();
        $(id).insert(options);

        if (M2ePro.formData[id]) {
            $(id).value = M2ePro.formData[id];
        } else {
            $(id).value = '';
        }
    },

    _storeSelectRenderer: function(data, id)
    {
        var options = '';
        var leafCount = 0;
        data.each(function(paris) {

            var key = paris.id;
            var val = paris.title;
            var isLeaf = paris.is_leaf;

            if (isLeaf == 0) {
                if (leafCount != 0) {
                    options += '</optgroup>';
                    leafCount--;
                }
                options += '<optgroup label="' + val + '">';
                leafCount++;
            }
            else
            {
                options += '<option value="' + key + '">' + val + '</option>\n';
            }
        });

        $(id).update();
        $(id).insert(options);

        if (M2ePro.formData[id]) {
            $(id).value = M2ePro.formData[id];
        } else {
            $(id).value = '';
        }
    },

    _reloadSomething: function(url, id, successHandler)
    {
        var defaultSuccessHandler = this._simpleSelectRenderer;

        new Ajax.Request(url, {
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);
                successHandler ? successHandler(data, id) : defaultSuccessHandler(data, id);
            }
        });
    },

    loadRefund: function(data)
    {
        var self = EbayTemplateGeneralHandlerObj;

        var assoc = {
            returns_accepted      : 'refund_accepted',
            refund                : 'refund_option',
            returns_within        : 'refund_within',
            shipping_cost_paid_by : 'refund_shippingcost',
            restocking_fee_value  : 'refund_restockingfee'
        };

        $H(assoc).each(function(item) {
            if (typeof data[item.key] == 'undefined') {
                data[item.key] = [];
            }
            var _data = data[item.key].length ? data[item.key] : [];
            self._simpleSelectRenderer(_data, item.value);
        });
        $('refund_accepted').simulate('change');

        $('ebayTemplateGeneralEditTabs_refund').removeClassName('changed');
    },

    loadTaxCategories: function(data)
    {
        var taxCategoryTr = $('tax_category_tr');
        var taxCategorySelect = $('tax_category');

        if ($F('marketplace_id') != '1') {
            taxCategoryTr.hide();
            taxCategorySelect.value = '';
            return;
        }

        taxCategorySelect.select('option').invoke('remove');

        var optionsHtml = '<option value=""></option>';
        data.each(function(tax_category) {
            optionsHtml += '<option value="'+tax_category.ebay_id+'">'+tax_category.title+'</option>';
        });

        taxCategorySelect.insert(optionsHtml);
        taxCategorySelect.value = M2ePro.formData.tax_category;
        taxCategoryTr.show();
    }

    //----------------------------------
});