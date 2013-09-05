EbayTemplateShippingHandler = Class.create(CommonHandler, {

    missingAttributes: {},

    discountProfiles: [],
    shippingServices: [],
    shippingLocations: [],

    counter: {
        local: 0,
        international: 0,
        total: 0
    },

    isSimpleViewMode: false,

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-location-or-postal-required', M2ePro.translator.translate('Location or Zip/Postal Code should be specified.'), function(value) {
            return $('address').value != '' || $('postal_code').value != '';
        });

        Validation.add('M2ePro-validate-international-ship-to-location', M2ePro.translator.translate('Select one or more international ship-to locations.'), function(value, el) {
            return $$('input[name="'+el.name+'"]').any(function(o) {
                return o.checked;
            });
        });

        Validation.add('M2ePro-validate-vat', M2ePro.translator.translate('Max applicable length is 6 characters, including the decimal (e.g., 12.345).'), function(value) {
            if (!value) {
                return true;
            }

            if (value.length > 6) {
                return false;
            }

            value = Math.ceil(value);

            return value >= 0 && value <= 30;
        });

        Validation.add('M2ePro-validate-shipping-methods', M2ePro.translator.translate('You should specify at least one shipping method.'), function(value, el) {
            var locationType = /local/.test(el.id) ? 'local' : 'international',
                shippingModeValue = $(locationType + '_shipping_mode').value;

            shippingModeValue = parseInt(shippingModeValue);

            if (shippingModeValue !== M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT') &&
                shippingModeValue !== M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED')) {
                return true;
            }

            return EbayTemplateShippingHandlerObj.counter[locationType] != 0;
        });
    },

    //----------------------------------

    postalCodeChange: function()
    {
        if ($('postal_code').value == '' || $('originating_postal_code').value != '') {
            return;
        }

        if (!EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()
            && !EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()
        ) {
            return;
        }

        $('originating_postal_code').value = $('postal_code').value;
    },

    //----------------------------------

    localShippingModeChange: function()
    {
        //----------------------------------
        $('magento_block_ebay_template_shipping_form_data_international').hide();
        $('block_notice_ebay_template_shipping_local').hide();
        $('block_notice_ebay_template_shipping_freight').hide();
        $('local_shipping_methods_tr').hide();
        //----------------------------------

        // clear selected shipping methods
        //----------------------------------
        $$('#shipping_local_tbody .icon-btn').each(function(el) {
            EbayTemplateShippingHandlerObj.removeRow.call(el, 'local');
        });
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()
            || EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()
        ) {
            if (EbayTemplateShippingHandlerObj.isSimpleViewMode) {
                $$('.local-shipping-always-visible-tr').invoke('show');
            } else {
                $$('.local-shipping-tr').invoke('show');
            }
        } else {
            $$('.local-shipping-tr').invoke('hide');
        }
        //----------------------------------

        //----------------------------------
        EbayTemplateShippingHandlerObj.updateGetItFastVisibility();
        EbayTemplateShippingHandlerObj.updateMeasurementVisibility();
        EbayTemplateShippingHandlerObj.updateCashOnDeliveryVisibility();
        EbayTemplateShippingHandlerObj.updateCrossBorderTradeVisibility();
        EbayTemplateShippingHandlerObj.updateRateTableVisibility('local');
        EbayTemplateShippingHandlerObj.updateLocalHandlingCostVisibility();
        EbayTemplateShippingHandlerObj.renderDiscountProfiles('local');
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()) {
            $('magento_block_ebay_template_shipping_form_data_international').show();
            $('local_shipping_methods_tr').show();
        }
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()) {
            $('magento_block_ebay_template_shipping_form_data_international').show();
            $('local_shipping_methods_tr').show();
            $('postal_code').simulate('change');
        }
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFreight()) {
            $('block_notice_ebay_template_shipping_freight').show();
            $('international_shipping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_NO_INTERNATIONAL');
            $('international_shipping_mode').simulate('change');
        }
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeLocal()) {
            $('block_notice_ebay_template_shipping_local').show();
            $('international_shipping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_NO_INTERNATIONAL');
            $('international_shipping_mode').simulate('change');
        }
        //----------------------------------
    },

    isLocalShippingModeFlat: function()
    {
        return $('local_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT');
    },

    isLocalShippingModeCalculated: function()
    {
        return $('local_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED');
    },

    isLocalShippingModeFreight: function()
    {
        return $('local_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FREIGHT');
    },

    isLocalShippingModeLocal: function()
    {
        return $('local_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_LOCAL');
    },

    //----------------------------------

    internationalShippingModeChange: function()
    {
        // clear selected shipping methods
        //----------------------------------
        $$('#shipping_international_tbody .icon-btn').each(function(el) {
            EbayTemplateShippingHandlerObj.removeRow.call(el, 'international');
        });
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isInternationalShippingModeFlat()
            || EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()
        ) {
            $('add_international_shipping_method_button').show();
            $('shipping_international_table').hide();
            if (EbayTemplateShippingHandlerObj.isSimpleViewMode) {
                $$('.international-shipping-always-visible-tr').invoke('show');
            } else {
                $$('.international-shipping-tr').invoke('show');
            }
        } else {
            $$('.international-shipping-tr').invoke('hide');
        }
        //----------------------------------

        //----------------------------------
        EbayTemplateShippingHandlerObj.updateMeasurementVisibility();
        EbayTemplateShippingHandlerObj.renderDiscountProfiles('international');
        EbayTemplateShippingHandlerObj.updateRateTableVisibility('international');
        EbayTemplateShippingHandlerObj.updateInternationalHandlingCostVisibility();
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()) {
            $('postal_code').simulate('change');
        }
        //----------------------------------
    },

    isInternationalShippingModeFlat: function()
    {
        return $('international_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT');
    },

    isInternationalShippingModeCalculated: function()
    {
        return $('international_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED');
    },

    isInternationalShippingModeNoInternational: function()
    {
        return $('international_shipping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_NO_INTERNATIONAL');
    },

    getCalculatedLocationType: function()
    {
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()) {
            return 'local';
        }

        if (EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()) {
            return 'international';
        }

        return null;
    },

    isShippingModeCalculated: function(locationType)
    {
        if (locationType == 'local') {
            return EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated();
        }

        if (locationType == 'international') {
            return EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated();
        }

        return false;
    },

    //----------------------------------

    internationalTradeChange: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::INTERNATIONAL_TRADE_NONE')) {
            $('international_shipping_none').show();
        } else {
            $('international_shipping_none').hide();
            if (EbayTemplateShippingHandlerObj.isInternationalShippingModeNoInternational()) {
                $('international_shipping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT');
                $('international_shipping_mode').simulate('change');
            }
        }
    },

    //----------------------------------

    updateGetItFastVisibility: function()
    {
        if (!$('get_it_fast_tr')) {
            return;
        }

        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()
            || EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()
        ) {
            $('get_it_fast_tr').show();
        } else {
            $('get_it_fast_tr').hide();
        }
    },

    updateCrossBorderTradeVisibility: function()
    {
        if (!EbayTemplateShippingHandlerObj.isSimpleViewMode
            && (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()
                || EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()
            )
        ) {
            $('magento_block_ebay_template_shipping_form_data_cross_border_trade').show();
        } else {
            $('magento_block_ebay_template_shipping_form_data_cross_border_trade').hide();
            $('international_trade').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::INTERNATIONAL_TRADE_NONE');
        }
    },

    //----------------------------------

    updateRateTableVisibility: function(locationType)
    {
        var shippingMode = $(locationType + '_shipping_mode').value;

        if (!$(locationType+'_shipping_rate_table_mode_tr')) {
            return;
        }

        if (shippingMode != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT')) {
            $(locationType+'_shipping_rate_table_mode_tr').hide();
            $(locationType+'_shipping_rate_table_mode').value = 0;
        } else {
            $(locationType+'_shipping_rate_table_mode_tr').show();
        }
    },

    isLocalRateTableEnabled: function()
    {
        if (!$('local_shipping_rate_table_mode')) {
            return false;
        }

        return $('local_shipping_rate_table_mode').value != 0;
    },

    localRateTableModeChange: function()
    {
        if (!EbayTemplateShippingHandlerObj.isLocalRateTableEnabled()) {
            $('local_shipping_flat_options_td').hide();
        }

        if (!EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()
            && !EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()
        ) {
            EbayTemplateShippingHandlerObj.updateMeasurementVisibility();
        }
    },

    //----------------------------------

    updateLocalHandlingCostVisibility: function()
    {
        if (!$('local_handling_cost_tr')) {
            return;
        }

        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()) {
            $('local_handling_cost_tr').hide();
            $('local_handling_cost').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_NONE');
            $('local_handling_cost').simulate('change');
        }
        //----------------------------------

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()) {
            $('local_handling_cost_tr').show();
            $('local_handling_cost').simulate('change');
        }
        //----------------------------------
    },

    updateInternationalHandlingCostVisibility: function()
    {
        if (!$('international_handling_cost_tr')) {
            return;
        }

        if (EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()) {
            $('international_handling_cost_tr').show();
            $('international_handling_cost').simulate('change');
        } else {
            $('international_handling_cost_tr').hide();
            $('international_handling_cost').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_NONE');
            $('international_handling_cost').simulate('change');
        }
    },

    //----------------------------------

    updateDiscountProfiles: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template_shipping/updateDiscountProfiles'),
        {
            method: 'get',
            parameters: {},
            onSuccess: function(transport) {
                EbayTemplateShippingHandlerObj.discountProfiles = transport.responseText.evalJSON(true);
                EbayTemplateShippingHandlerObj.renderDiscountProfiles('local');
                EbayTemplateShippingHandlerObj.renderDiscountProfiles('international');
            }
        });
    },

    renderDiscountProfiles: function(locationType)
    {
        if (!$(locationType+'_shipping_combined_discount_profile_id')) {
            return;
        }

        var html = EbayTemplateShippingHandlerObj.getDiscountProfilesHtml(locationType);
        var value = M2ePro.formData[locationType + '_shipping_combined_discount_profile_id'];

        $(locationType+'_shipping_combined_discount_profile_id').update(html);
        $(locationType+'_shipping_combined_discount_profile_id').value = value;
    },

    getDiscountProfilesHtml: function(locationType)
    {
        var shippingModeSelect = $(locationType + '_shipping_mode');
        var desiredProfileType = null;
        var html = '<option value="">'+M2ePro.translator.translate('None')+'</option>';

        switch (parseInt(shippingModeSelect.value)) {
            case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT'):
                desiredProfileType = 'flat_shipping';
                break;
            case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED'):
                desiredProfileType = 'calculated_shipping';
                break;
        }

        if (desiredProfileType === null) {
            return html;
        }

        EbayTemplateShippingHandlerObj.discountProfiles.each(function(profile) {
            if (profile.type != desiredProfileType) {
                return;
            }

            html += '<option value="'+profile.profile_id+'">'+profile.profile_name+'</option>';
        });

        return html;
    },

    //----------------------------------

    updateCashOnDeliveryVisibility: function()
    {
        if (!$('local_shipping_cash_on_delivery_cost_mode_tr')) {
            return;
        }

        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()
            || EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()
        ) {
            $('local_shipping_cash_on_delivery_cost_mode_tr').show();
            $('local_shipping_cash_on_delivery_cost_mode').simulate('change');
        } else {
            $('local_shipping_cash_on_delivery_cost_mode_tr').hide();
            $('local_shipping_cash_on_delivery_cost_ca_tr').hide();
            $('local_shipping_cash_on_delivery_cost_cv_tr').hide();
        }
    },

    //----------------------------------

    isCashOnDeliveryCostModeNone: function()
    {
        return $('local_shipping_cash_on_delivery_cost_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::CASH_ON_DELIVERY_COST_MODE_NONE');
    },

    isCashOnDeliveryCostModeCustomValue: function()
    {
        return $('local_shipping_cash_on_delivery_cost_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::CASH_ON_DELIVERY_COST_MODE_CUSTOM_VALUE');
    },

    isCashOnDeliveryCostModeCustomAttribute: function()
    {
        return $('local_shipping_cash_on_delivery_cost_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping::CASH_ON_DELIVERY_COST_MODE_CUSTOM_ATTRIBUTE');
    },

    cashOnDeliveryCostModeChange: function()
    {
        $('local_shipping_cash_on_delivery_cost_cv_tr', 'local_shipping_cash_on_delivery_cost_ca_tr').invoke('hide');

        if (EbayTemplateShippingHandlerObj.isCashOnDeliveryCostModeCustomValue()) {
            $('local_shipping_cash_on_delivery_cost_cv_tr').show();
        }

        if (EbayTemplateShippingHandlerObj.isCashOnDeliveryCostModeCustomAttribute()) {
            $('local_shipping_cash_on_delivery_cost_ca_tr').show();
        }
    },

    //----------------------------------

    packageSizeChange: function()
    {
        var self = EbayTemplateShippingHandlerObj;

        var packageSizeMode = this.options[this.selectedIndex].up().getAttribute('package_size_mode');

        $('package_size_mode').value = packageSizeMode;
        $('package_size_attribute').value = '';

        if (packageSizeMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::PACKAGE_SIZE_CUSTOM_VALUE')) {
            $('package_size_value').value = this.value;

            var showDimension = parseInt(this.options[this.selectedIndex].getAttribute('dimensions_supported'));
            self.updateDimensionVisibility(showDimension);
        } else if (packageSizeMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::PACKAGE_SIZE_CUSTOM_ATTRIBUTE')) {
            $('package_size_attribute').value = this.value;
            self.updateDimensionVisibility(true);
        }
    },

    //----------------------------------

    updateDimensionVisibility: function(showDimension)
    {
        if (showDimension) {
            $('dimensions_tr').show();
            $('dimension_mode').simulate('change');
        } else {
            $('dimensions_tr').hide();
            $('dimension_mode').value = 0;
            $('dimension_mode').simulate('change');
        }
    },

    //----------------------------------

    dimensionModeChange: function()
    {
        $('dimensions_ca_tr', 'dimensions_cv_tr').invoke('hide');

        if (this.value != 0) {
            $(this.value == 1 ? 'dimensions_cv_tr' : 'dimensions_ca_tr').show();
        }
    },

    //----------------------------------

    localHandlingCostChange: function()
    {
        $('local_handling_cost_cv_tr').hide();

        var localHandlingCostMode = this.options[this.selectedIndex].up().getAttribute('local_handling_cost_mode');
        if (localHandlingCostMode === null) {
            localHandlingCostMode = this.value;
        }

        $('local_handling_cost_mode').value = localHandlingCostMode;
        $('local_handling_cost_attribute').value = '';

        if (localHandlingCostMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_CUSTOM_VALUE')) {
            $('local_handling_cost_cv_tr').show();
        } else if (localHandlingCostMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_CUSTOM_ATTRIBUTE')) {
            $('local_handling_cost_attribute').value = this.value;
        }
    },

    internationalHandlingCostChange: function()
    {
        $('international_handling_cost_cv_tr').hide();

        var internationalHandlingCostMode = this.options[this.selectedIndex].up().getAttribute('international_handling_cost_mode');
        if (internationalHandlingCostMode === null) {
            internationalHandlingCostMode = this.value;
        }

        $('international_handling_cost_mode').value = internationalHandlingCostMode;
        $('international_handling_cost_attribute').value = '';

        if (internationalHandlingCostMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_CUSTOM_VALUE')) {
            $('international_handling_cost_cv_tr').show();
        } else if (internationalHandlingCostMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::HANDLING_CUSTOM_ATTRIBUTE')) {
            $('international_handling_cost_attribute').value = this.value;
        }
    },

    //----------------------------------

    weightChange: function()
    {
        var measurementNoteElement = this.up().next('td.note');

        $('weight_cv').hide();
        measurementNoteElement.hide();

        var weightMode = this.options[this.selectedIndex].up().getAttribute('weight_mode');
        if (weightMode === null) {
            weightMode = this.value;
        }

        $('weight_mode').value = weightMode;
        $('weight_attribute').value = '';

        if (weightMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_CUSTOM_VALUE')) {
            $('weight_cv').show();
        } else if (weightMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_CUSTOM_ATTRIBUTE')) {
            $('weight_attribute').value = this.value;
            measurementNoteElement.show();
        }
    },

    //----------------------------------

    isMeasurementSystemEnglish: function()
    {
        return $('measurement_system').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH');
    },

    measurementSystemChange: function()
    {
        $$('.measurement-system-english, .measurement-system-metric').invoke('hide');

        if (EbayTemplateShippingHandlerObj.isMeasurementSystemEnglish()) {
            $$('.measurement-system-english').invoke('show');
        } else {
            $$('.measurement-system-metric').invoke('show');
        }
    },

    //----------------------------------

    updateMeasurementVisibility: function()
    {
        $('local_shipping_flat_options_td').hide();
        $('local_shipping_calculated_options_td').hide();
        $('international_shipping_calculated_options_td').hide();
        $('weight_mode_none').show();

        if (EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()) {
            EbayTemplateShippingHandlerObj.showMeasurementOptions('local', 'calculated');
            return;
        }

        if (EbayTemplateShippingHandlerObj.isInternationalShippingModeCalculated()) {
            EbayTemplateShippingHandlerObj.showMeasurementOptions('international', 'calculated');
            return;
        }

        if (EbayTemplateShippingHandlerObj.isLocalShippingModeFlat()
            && EbayTemplateShippingHandlerObj.isLocalRateTableEnabled()
        ) {
            EbayTemplateShippingHandlerObj.showMeasurementOptions('local', 'flat');
        }
    },

    showMeasurementOptions: function(locationType, shippingMode)
    {
        $$('#block_shipping_template_calculated_options tr').each(function(element) {
            if (element.hasClassName('visible-for-'+shippingMode+'-by-default')) {
                element.show();
            } else {
                element.hide();
            }
        });

        $(locationType+ '_shipping_'+shippingMode+'_options_td').appendChild($('block_shipping_template_calculated_options'));
        $(locationType+ '_shipping_'+shippingMode+'_options_td').show();
        $('block_shipping_template_calculated_options').show();

        if (shippingMode == 'calculated') {
            // doesn't work in IE
            $('weight_mode_none').hide();
        }

        EbayTemplateShippingHandlerObj.prepareMeasurementObservers(shippingMode);
    },

    prepareMeasurementObservers: function(shippingMode)
    {
        $('measurement_system')
            .observe('change', EbayTemplateShippingHandlerObj.measurementSystemChange)
            .simulate('change');

        if (shippingMode == 'calculated') {
            $('package_size')
                .observe('change', EbayTemplateShippingHandlerObj.packageSizeChange)
                .simulate('change');

            $('dimension_mode')
                .observe('change', EbayTemplateShippingHandlerObj.dimensionModeChange)
                .simulate('change');
        }

        $('weight')
            .observe('change', EbayTemplateShippingHandlerObj.weightChange)
            .simulate('change');
    },

    //----------------------------------

    serviceChange: function()
    {
        var row = $(this).up('tr');

        this.down(0).hide();

        if (this.value === '') {
            row.select('.cost-mode')[0].hide();
            row.select('.shipping-cost-cv')[0].hide();
            row.select('.shipping-cost-ca')[0].hide();
            row.select('.shipping-cost-additional')[0].hide();
            row.select('.shipping-cost-additional-ca')[0].hide();
        } else {
            row.select('.cost-mode')[0].show();
            row.select('.cost-mode')[0].simulate('change');
        }
    },

    //----------------------------------

    serviceCostModeChange: function()
    {
        var row = $(this).up('tr');

        //----------------------------------
        var inputCostCV = row.select('.shipping-cost-cv')[0];
        var inputCostCA = row.select('.shipping-cost-ca')[0];
        var inputCostAddCV = row.select('.shipping-cost-additional')[0];
        var inputCostAddCA = row.select('.shipping-cost-additional-ca')[0];
        var inputPriority = row.select('.shipping-priority')[0];
        //----------------------------------

        //----------------------------------
        [inputCostCV, inputCostCA, inputCostAddCV, inputCostAddCA].invoke('hide');
        inputPriority.show();
        //----------------------------------

        //----------------------------------
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_VALUE')) {
            inputCostCV.show();
            inputCostCV.disabled = false;

            inputCostAddCV.show();
            inputCostAddCV.disabled = false;
        }
        //----------------------------------

        //----------------------------------
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE')) {
            inputCostCA.show();
            inputCostAddCA.show();
        }
        //----------------------------------

        //----------------------------------
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_FREE')) {

            var isLocalMethod = /local/.test(row.id);

            if (isLocalMethod && EbayTemplateShippingHandlerObj.isLocalShippingModeCalculated()) {
                inputPriority.value = 0;
                inputCostCV.value = 0;
                inputCostAddCV.value = 0;

                [inputPriority, inputCostCV, inputCostAddCV].invoke('hide');

            } else {
                inputCostCV.show();
                inputCostCV.value = 0;
                inputCostCV.disabled = true;

                inputCostAddCV.show();
                inputCostAddCV.value = 0;
                inputCostAddCV.disabled = true;
            }
        }
        //----------------------------------
    },

    //----------------------------------

    shippingLocationChange: function()
    {
        var i = this.name.match(/\d+/);
        var current = this;

        if (this.value != 'Worldwide') {
            return;
        }

        $$('input[name="shipping[shippingLocation][' + i + '][]"]').each(function(item) {
            if (current.checked && item != current) {
                item.checked = false;
                item.disabled = true;
            } else {
                item.disabled = false;
            }
        });
    },

    //----------------------------------

    addRow: function(type, renderSaved) // local|international
    {
        renderSaved = renderSaved || false;

        $('shipping_'+type+'_table').show();
        $('add_'+type+'_shipping_method_button').hide();

        var id = 'shipping_' + type + '_tbody';
        var i = EbayTemplateShippingHandlerObj.counter.total;

        //----------------------------------
        var tpl = $$('#block_listing_template_shipping_table_row_template_table tbody')[0].innerHTML;
        tpl = tpl.replace(/%i%/g, i);
        tpl = tpl.replace(/%type%/g, type);
        $(id).insert(tpl);
        //----------------------------------

        //----------------------------------
        var row = $('shipping_variant_' + type + '_' + i + '_tr');
        //----------------------------------

        //----------------------------------
        if (!EbayTemplateShippingHandlerObj.isSimpleViewMode || renderSaved) {
            AttributeSetHandlerObj.renderAttributesWithEmptyOption('shipping[shipping_cost_attribute][' + i + ']', row.down('.shipping-cost-ca'));
            AttributeSetHandlerObj.renderAttributesWithEmptyOption('shipping[shipping_cost_additional_attribute][' + i + ']', row.down('.shipping-cost-additional-ca'));
        } else {
            // remove custom attribute option
            row.down('.cost-mode').remove(2);
        }
        //----------------------------------

        //----------------------------------
        EbayTemplateShippingHandlerObj.renderServices(row.select('.shipping-service')[0], type);
        EbayTemplateShippingHandlerObj.initRow(row);
        //----------------------------------

        //----------------------------------
        if (type == 'international') {
            tpl = $$('#block_shipping_table_locations_row_template_table tbody')[0].innerHTML;
            tpl = tpl.replace(/%i%/g, i);
            $(id).insert(tpl);
            EbayTemplateShippingHandlerObj.renderShipToLocationCheckboxes(i);
        }
        //----------------------------------

        //----------------------------------
        EbayTemplateShippingHandlerObj.counter[type]++;
        EbayTemplateShippingHandlerObj.counter.total++;
        //----------------------------------

        //----------------------------------
        if (type == 'local' && EbayTemplateShippingHandlerObj.counter[type] >= 4) {
            $(id).up('table').select('tfoot')[0].hide();
        }
        if (type == 'international' && EbayTemplateShippingHandlerObj.counter[type] >= 5) {
            $(id).up('table').select('tfoot')[0].hide();
        }
        //----------------------------------

        return row;
    },

    //----------------------------------

    initRow: function(row)
    {
        var locationType = /local/.test(row.id) ? 'local' : 'international';

        //----------------------------------
        if (EbayTemplateShippingHandlerObj.isShippingModeCalculated(locationType)) {
            row.select('.cost-mode')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CALCULATED');
            row.select('.shipping-mode-option-notcalc').invoke('remove');

            if (locationType == 'international' || $$('#shipping_local_tbody .cost-mode').length > 1) {
                // only one calculated shipping method can have free mode
                row.select('.shipping-mode-option-free').invoke('remove');
            }
        } else {
            row.select('.cost-mode')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_FREE');
            row.select('.shipping-mode-option-calc')[0].remove();
        }
        //----------------------------------

        //----------------------------------
        EbayTemplateShippingHandlerObj.renderServices(row.select('.shipping-service')[0], locationType);
        //----------------------------------

        //----------------------------------
        row.select('.cost-mode')[0].simulate('change');
        row.select('.shipping-service')[0].simulate('change');
        //----------------------------------
    },

    //----------------------------------

    renderServices: function(el, locationType)
    {
        var html = '';
        var isCalculated = EbayTemplateShippingHandlerObj.isShippingModeCalculated(locationType);
        var selectedPackage = $('package_size_value').value;
        var categoryMethods = '';

        // not selected international shipping service
        if (locationType == 'international') {
            html += '<option value="">--</option>';
        } else {
            html += '<option value="">'+ M2ePro.translator.translate('Select shipping service') +'</option>';
        }

        if (Object.isArray(EbayTemplateShippingHandlerObj.shippingServices) && EbayTemplateShippingHandlerObj.shippingServices.length == 0) {
            $(el).update(html);
            return;
        }

        $H(EbayTemplateShippingHandlerObj.shippingServices).each(function(category) {

            categoryMethods = '';
            category.value.methods.each(function(service) {
                var isServiceOfSelectedDestination = (locationType == 'local' && service.is_international == 0) || (locationType == 'international' && service.is_international == 1);
                var isServiceOfSelectedType = ( isCalculated && service.is_calculated == 1) || (! isCalculated && service.is_flat == 1);

                if (!isServiceOfSelectedDestination || !isServiceOfSelectedType) {
                    return;
                }

                if (isCalculated) {
                    if (service.data.ShippingPackage.indexOf(selectedPackage) != -1) {
                        categoryMethods += '<option value="' + service.ebay_id + '">' + service.title + '</option>';
                    }

                    return;
                }

                categoryMethods += '<option value="' + service.ebay_id + '">' + service.title + '</option>';
            });

            if (categoryMethods != '') {
                noCategoryTitle = category[0] == '';
                if (noCategoryTitle) {
                    html += categoryMethods;
                } else {
                    html += '<optgroup label="' + category.value.title + '">' + categoryMethods + '</optgroup>';
                }
            }
        });

        $(el).update(html);
    },

    //----------------------------------

    renderShipToLocationCheckboxes: function(i)
    {
        var html = '';

        //----------------------------------
        EbayTemplateShippingHandlerObj.shippingLocations.each(function(location) {
            if (location.ebay_id == 'Worldwide') {
                html += '<div>' +
                    '<label>' +
                        '<input' +
                            ' type="checkbox"' +
                            ' name="shipping[shippingLocation][' + i + '][]" value="' + location.ebay_id + '"' +
                            ' onclick="EbayTemplateShippingHandlerObj.shippingLocationChange.call(this);"' +
                            ' class="shipping-location M2ePro-validate-international-ship-to-location"' +
                        '/>' +
                        '&nbsp;<b>' + location.title + '</b>' +
                    '</label>' +
                '</div>';
            } else {
                html += '<label style="float: left; width: 133px;" class="nobr">' +
                    '<input' +
                        ' type="checkbox"' +
                        ' name="shipping[shippingLocation][' + i + '][]" value="' + location.ebay_id + '"' +
                        ' onclick="EbayTemplateShippingHandlerObj.shippingLocationChange.call(this);"' +
                    '/>' +
                    '&nbsp;' + location.title +
                '</label>';
            }
        });
        //----------------------------------

        //----------------------------------
        $$('#shipping_variant_locations_' + i + '_tr td')[0].innerHTML = '<div style="margin: 5px 10px">' + html + '</div>';
        $$('#shipping_variant_locations_' + i + '_tr td')[0].innerHTML += '<div style="clear: both; margin-bottom: 10px;" />';
        //----------------------------------

        if (!M2ePro.formData.shippingMethods[i]) {
            return;
        }

        //----------------------------------
        var locations = [];
        M2ePro.formData.shippingMethods[i].locations.each(function(item) {
            locations.push(item);
        });
        //----------------------------------

        //----------------------------------
        $$('input[name="shipping[shippingLocation][' + i + '][]"]').each(function(el) {
            if (locations.indexOf(el.value) != -1) {
                el.checked = true;
            }
            $(el).simulate('change');
        });
        //----------------------------------
    },

    //----------------------------------

    removeRow: function(locationType)
    {
        var table = $(this).up('table');

        if (locationType == 'international') {
            $(this).up('tr').next().remove();
        }

        $(this).up('tr').remove();

        EbayTemplateShippingHandlerObj.counter[locationType]--;

        if (EbayTemplateShippingHandlerObj.counter[locationType] == 0) {
            $('shipping_'+locationType+'_table').hide();
            $('add_'+locationType+'_shipping_method_button').show();
        }

        if (locationType == 'local' && EbayTemplateShippingHandlerObj.counter[locationType] < 4) {
            table.select('tfoot')[0].show();
        }
        if (locationType == 'international' && EbayTemplateShippingHandlerObj.counter[locationType] < 5) {
            table.select('tfoot')[0].show();
        }

        EbayTemplateShippingHandlerObj.updateMeasurementVisibility();
    },

    //----------------------------------

    hasMissingServiceAttribute: function(code, position)
    {
        if (typeof EbayTemplateShippingHandlerObj.missingAttributes['services'][position] == 'undefined') {
            return false;
        }

        if (typeof EbayTemplateShippingHandlerObj.missingAttributes['services'][position][code] == 'undefined') {
            return false;
        }

        return true;
    },

    addMissingServiceAttributeOption: function(select, code, position, value)
    {
        var option = document.createElement('option');

        option.value = value;
        option.innerHTML = EbayTemplateShippingHandlerObj.missingAttributes['services'][position][code];

        var first = select.down('.empty').next();

        first.insert({ before: option });
    },

    renderShippingMethods: function (shippingMethods)
    {
        if (shippingMethods.length > 0) {
            $('shipping_local_table').show();
            $('add_local_shipping_method_button').hide();
        } else {
            $('shipping_local_table').hide();
            $('add_local_shipping_method_button').show();
        }

        shippingMethods.each(function(service, i) {

            var type = service.shipping_type == 1 ? 'international' : 'local';
            var row = EbayTemplateShippingHandlerObj.addRow(type, true);

            row.down('.shipping-service').value = service.shipping_value;
            row.down('.cost-mode').value = service.cost_mode;

            if (service.cost_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_VALUE')) {
                row.down('.shipping-cost-cv').value = service.cost_value;
                row.down('.shipping-cost-additional').value = service.cost_additional_value;

                if (EbayTemplateShippingHandlerObj.isSimpleViewMode) {
                    // remove custom attribute option
                    row.down('.cost-mode').remove(2);
                }

            } else if (service.cost_mode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE')) {
                if (EbayTemplateShippingHandlerObj.hasMissingServiceAttribute('cost_value', i)) {
                    EbayTemplateShippingHandlerObj.addMissingServiceAttributeOption(
                        row.down('.shipping-cost-ca select'), 'cost_value', i, service.cost_value
                    );
                }

                if (EbayTemplateShippingHandlerObj.hasMissingServiceAttribute('cost_additional_value', i)) {
                    EbayTemplateShippingHandlerObj.addMissingServiceAttributeOption(
                        row.down('.shipping-cost-additional-ca select'), 'cost_additional_value', i, service.cost_additional_value
                    );
                }

                row.down('.shipping-cost-ca select').value = service.cost_value;
                row.down('.shipping-cost-additional-ca select').value = service.cost_additional_value;

                if (EbayTemplateShippingHandlerObj.isSimpleViewMode) {
                    EbayTemplateShippingHandlerObj.replaceSelectWithInputHidden(row.down('.cost-mode'));
                    EbayTemplateShippingHandlerObj.replaceSelectWithInputHidden(row.down('.shipping-cost-ca select'));
                    EbayTemplateShippingHandlerObj.replaceSelectWithInputHidden(row.down('.shipping-cost-additional-ca select'));
                }
            } else {
                if (EbayTemplateShippingHandlerObj.isSimpleViewMode) {
                    // remove custom attribute option
                    row.down('.cost-mode').remove(2);
                }
            }

            row.down('.shipping-priority').value = service.priority;
            row.down('.cost-mode').simulate('change');
            row.down('.shipping-service').simulate('change');
        });
    },

    replaceSelectWithInputHidden: function(select)
    {
        var td = select.up('td');
        var label = select.options[select.selectedIndex].innerHTML;
        var input = '<input type="hidden" ' +
            'name="' + select.name + '" ' +
            'id="' + select.id + '" ' +
            'value="' + select.value + '" ' +
            'class="' + select.className + '" />';

        $(select).replace('');
        $(td).insert('<span>' + label + input + '</span>');

        if (td.down('.cost-mode')) {
            td.down('.cost-mode').observe('change', EbayTemplateShippingHandlerObj.serviceCostModeChange);
        }
    }

    //----------------------------------
});