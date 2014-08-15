CommonAmazonTemplateSynchronizationHandler = Class.create();
CommonAmazonTemplateSynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.translator.translate('The specified title is already used for other template. Template title must be unique.'),
                                                'Template_Synchronization', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        Validation.add('M2ePro-input-time', M2ePro.translator.translate('Wrong time format string.'), function(value) {
            return value.match(/^\d{2}:\d{2}$/g);
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

        //-----------------
        Validation.add('M2ePro-validate-conditions-between', M2ePro.translator.translate('Must be greater than "Min".'), function(value, el)
        {
            var minValue = $(el.id.replace('_max','')).value;

            if (!el.up('tr').visible()) {
                return true;
            }

            return parseInt(value) > parseInt(minValue);
        });
        //-----------------

        //-----------------
        Validation.add('M2ePro-validate-stop-relist-conditions-product-status', M2ePro.translator.translate('Inconsistent settings in Revise and Stop rules.'), function(value, el)
        {
            if (AmazonTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_status_disabled').value == 1 && $('relist_status_enabled').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-stock-availability', M2ePro.translator.translate('Inconsistent settings in Revise and Stop rules.'), function(value, el)
        {
            if (AmazonTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_out_off_stock').value == 1 && $('relist_is_in_stock').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-item-qty', M2ePro.translator.translate('Inconsistent settings in Revise and Stop rules.'), function(value, el)
        {
            if (AmazonTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            var stopMaxQty = 0,
                relistMinQty = 0;

            switch (parseInt($('stop_qty').value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_NONE'):
                    return true;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_LESS'):
                    stopMaxQty = parseInt($('stop_qty_value').value);
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_BETWEEN'):
                    stopMaxQty = parseInt($('stop_qty_value_max').value);
                    break;
            }

            switch (parseInt($('relist_qty').value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_NONE'):
                    return false;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_MORE'):
                case M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_BETWEEN'):
                    relistMinQty = parseInt($('relist_qty_value').value);
                    break;
            }

            if (relistMinQty <= stopMaxQty) {
                return false;
            }

            return true;
        });
        //-----------------
    },

    //----------------------------------

    isRelistModeDisabled : function()
    {
        return $('relist_mode').value == 0;
    },

    //----------------------------------

    duplicate_click: function($headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.translator.translate('The specified title is already used for other template. Template title must be unique.'),
                                                'Template_Synchronization', 'title', '','',
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        CommonHandlerObj.duplicate_click($headId, M2ePro.translator.translate('Add Synchronization Template.'));
    },

    //----------------------------------

    stopQty_change : function()
    {
        if ($('stop_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_NONE')) {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_LESS')) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_BETWEEN')) {
            $('stop_qty_item_min').show();
            $('stop_qty_item').hide();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').show();
        } else if ($('stop_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_MORE')) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        }
    },

    listMode_change : function()
    {
        if ($('list_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_MODE_NONE')) {
            $('magento_block_amazon_template_synchronization_list_rules').hide();
        } else if ($('list_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_MODE_YES')) {
            $('magento_block_amazon_template_synchronization_list_rules').show();
        } else {
            $('magento_block_amazon_template_synchronization_list_rules').hide();
        }
    },

    listQty_change : function()
    {
        if ($('list_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_NONE')) {
            $('list_qty_value_container').hide();
            $('list_qty_value_max_container').hide();
        } else if ($('list_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_LESS')) {
            $('list_qty_item_min').hide();
            $('list_qty_item').show();
            $('list_qty_value_container').show();
            $('list_qty_value_max_container').hide();
        } else if ($('list_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_BETWEEN')) {
            $('list_qty_item_min').show();
            $('list_qty_item').hide();
            $('list_qty_value_container').show();
            $('list_qty_value_max_container').show();
        } else if ($('list_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_MORE')) {
            $('list_qty_item_min').hide();
            $('list_qty_item').show();
            $('list_qty_value_container').show();
            $('list_qty_value_max_container').hide();
        } else {
            $('list_qty_value_container').hide();
            $('list_qty_value_max_container').hide();
        }
    },

    relistMode_change : function()
    {
        if ($('relist_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_MODE_NONE')) {
            $('relist_filter_user_lock_tr_container').hide();
            $('magento_block_amazon_template_synchronization_relist_rules').hide();
        } else if ($('relist_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_MODE_YES')) {
            $('relist_filter_user_lock_tr_container').show();
            $('magento_block_amazon_template_synchronization_relist_rules').show();
        } else {
            $('relist_filter_user_lock_tr_container').hide();
            $('magento_block_amazon_template_synchronization_relist_rules').hide();
        }
    },

    relistQty_change : function()
    {
        if ($('relist_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_NONE')) {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_LESS')) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_BETWEEN')) {
            $('relist_qty_item_min').show();
            $('relist_qty_item').hide();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').show();
        } else if ($('relist_qty').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_MORE')) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        }
    },

    reviseQty_change : function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_UPDATE_QTY_YES')) {
            $('revise_update_qty_max_applied_value_mode_tr').show();
            $('revise_update_qty_max_applied_value_line_tr').show();
            $('revise_update_qty_max_applied_value_mode').simulate('change');
        } else {
            $('revise_update_qty_max_applied_value_mode_tr').hide();
            $('revise_update_qty_max_applied_value_line_tr').hide();
            $('revise_update_qty_max_applied_value_tr').hide();
            $('revise_update_qty_max_applied_value_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_OFF');
        }
    },

    reviseQtyMaxAppliedValueMode_change : function()
    {
        $('revise_update_qty_max_applied_value_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_ON')) {
            $('revise_update_qty_max_applied_value_tr').show();
        }
    }

    //----------------------------------
});