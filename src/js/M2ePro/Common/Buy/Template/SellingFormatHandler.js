CommonBuyTemplateSellingFormatHandler = Class.create();
CommonBuyTemplateSellingFormatHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.translator.translate('The specified title is already used for other template. Template title must be unique.'),
                                                'Template_SellingFormat', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Buy::NICK'));

        Validation.add('M2ePro-validate-price-coefficient', M2ePro.translator.translate('Coefficient is not valid.'), function(value)
        {
            if (value == '') {
                return true;
            }

            if (value == '0' || value == '0%') {
                return false;
            }

            return value.match(/^[+-]?\d+[.]?\d*[%]?$/g);
        });

        Validation.add('validate-qty', M2ePro.translator.translate('Wrong value. Only integer numbers.'), function(value, el)
        {
            if (!el.up('tr').visible()) {
                return true;
            }

            if (value.match(/[^\d]+/g)) {
                return false;
            }

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

        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.translator.translate('The specified title is already used for other template. Template title must be unique.'),
                                                'Template_SellingFormat', 'title', '','',
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Buy::NICK'));

        CommonHandlerObj.duplicate_click($headId, M2ePro.translator.translate('Add Selling Format Template.'));
    },

    //----------------------------------

    attribute_sets_confirm: function()
    {
        var self = BuyTemplateSellingFormatHandlerObj;

        AttributeSetHandlerObj.confirmAttributeSets();

        self.renderAttributesWithEmptyOption('qty_custom_attribute', 'qty_custom_attribute_td');
        self.renderAttributesWithEmptyOption('price_custom_attribute', 'price_custom_attribute_td');
    },

    renderAttributesWithEmptyOption: function(name, insertTo)
    {
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(name, insertTo);

        if (name != 'qty_custom_attribute') {
            return;
        }

        var option = '<option value="' + M2ePro.php.constant('Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_SellingFormat_Edit_Form::QTY_MODE_PRODUCT_FIXED_VIRTUAL_ATTRIBUTE_VALUE') + '">' +
                          M2ePro.translator.translate('QTY') +
                     '</option>';

        $$('#' + name + ' option').first().insert({after: option});
        $$('#' + name + ' option').first().selected = 1;

        AttributeSetHandlerObj.checkAttributesSelect(name, '');
    },

    //----------------------------------

    qty_mode_change: function()
    {
        $('qty_custom_attribute_tr', 'qty_custom_value_tr', 'qty_percentage_tr', 'qty_modification_mode_tr').invoke('hide');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_NUMBER')) {
            $('qty_custom_value_tr').show();
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {

            !AttributeSetHandlerObj.checkAttributeSetSelection()
                ? this.value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT')
                : $('qty_custom_attribute_tr').show();
        }

        $('qty_modification_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODIFICATION_MODE_OFF');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {

            $('qty_modification_mode_tr').show();

            $('qty_modification_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODIFICATION_MODE_ON');

            if (M2ePro.formData.qty_mode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT') ||
                M2ePro.formData.qty_mode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {
                $('qty_modification_mode').value = M2ePro.formData.qty_modification_mode;
            }
        }

        $('qty_modification_mode').simulate('change');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {

            $('qty_percentage_tr').show();
        }
    },

    qtyPostedMode_change: function()
    {
        $('qty_min_posted_value_tr').hide();
        $('qty_max_posted_value_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODIFICATION_MODE_ON')) {
            $('qty_min_posted_value_tr').show();
            $('qty_max_posted_value_tr').show();
        }
    },

    //----------------------------------

    price_mode_change: function()
    {
        var self = BuyTemplateSellingFormatHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_ATTRIBUTE')) {
            if (AttributeSetHandlerObj.checkAttributeSetSelection()) {
                $('price_custom_attribute_tr').show();
            } else {
                this.value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_PRODUCT');
            }
        } else {
            $('price_custom_attribute_tr').hide();
        }

        $('price_note').innerHTML = M2ePro.translator.translate('The price of products in Rakuten.com listing(s).');
    }

    //----------------------------------
});
