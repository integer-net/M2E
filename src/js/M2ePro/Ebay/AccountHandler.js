EbayAccountHandler = Class.create();
EbayAccountHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-account-token-session', M2ePro.translator.translate('You must get token.'), function(value) {
            return value != '';
        });

        Validation.add('M2ePro-account-customer-id', M2ePro.translator.translate('No Customer entry is found for specified ID.'), function(value) {

            var checkResult = false;

            if ($('magento_orders_customer_id_container').getStyle('display') == 'none') {
                return true;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_general/checkCustomerId'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    customer_id : value,
                    id          : M2ePro.formData.id
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['ok'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-account-order-number-prefix', M2ePro.translator.translate('Prefix length should not be greater than 5 characters.'), function(value) {

            if ($('magento_orders_number_prefix_mode').value == 0) {
                return true;
            }

            return value.length <= 5;
        });

        Validation.add('M2ePro-account-feedback-templates', M2ePro.translator.translate('You should create at least one Response Template.'), function(value) {

            if (value == 0) {
                return true;
            }

            var checkResult = false;

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateCheck'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    id: M2ePro.formData.id
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['ok'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-require-select-attribute', M2ePro.translator.translate('If Yes is chosen, you must select at least one Attribute for Product Mapping.'), function(value, el) {

            if ($('other_listings_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_MODE_NO')) {
                return true;
            }

            var isAttributeSelected = false;

            $$('.attribute-mode-select').each(function(obj) {
                if (obj.value != 0) {
                    isAttributeSelected = true;
                }
            });

            return isAttributeSelected;
        });
    },

    //----------------------------------

    updateHiddenValue: function(elementMode, elementHidden)
    {
        var value = elementMode.options[elementMode.selectedIndex].getAttribute('value_hack');
        elementHidden.value = value;
    },

    //----------------------------------

    saveAndClose: function()
    {
        var url = typeof M2ePro.url.urls.formSubmit == 'undefined' ?
                M2ePro.url.formSubmit + 'back/'+base64_encode('list')+'/' :
                M2ePro.url.get('formSubmit', {'back': base64_encode('list')});

        if (!editForm.validate()) {
            return;
        }

        new Ajax.Request(url, {
            method: 'post',
            parameters: Form.serialize($(editForm.formId)),
            onSuccess: function() {
                window.close();
            }
        });
    },

    //----------------------------------

    delete_click: function()
    {
        if (!confirm(M2ePro.translator.translate('Be attentive! By Deleting Account you delete all information on it from M2E Pro Server. This will cause inappropriate work of all Accounts\' copies.'))) {
            return;
        }
        setLocation(M2ePro.url.get('deleteAction'));
    },

    //----------------------------------

    update_account_title: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/update_account_title'), {
            method: 'post',
            parameters: {
                id: M2ePro.formData.id,
                mode: $('mode').value
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response['status'] == 'success') {
                    var title;
                    if (response['url'] != '') {
                        title = '<a target="_blank" href="' + response['url'] + '">' + response['title'] + '</a>';
                    } else {
                        title = response['title'];
                    }
                    $('account_title').update(title);
                }
            }
        });
    },

    //----------------------------------

    get_token: function()
    {
        if ($('token_session').value == '') {
            $('token_session').value = '0';
        }
        if ($('token_expired_date').value == '') {
            $('token_expired_date').value = '0';
        }
        this.submitForm(M2ePro.url.get('adminhtml_ebay_account/beforeGetToken', {'id': M2ePro.formData.id}));
    },

    //----------------------------------

    feedbacksReceiveChange: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('feedbacks_receive').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_RECEIVE_YES')) {
            $('magento_block_ebay_accounts_feedbacks_response').show();
        } else {
            $('magento_block_ebay_accounts_feedbacks_response').hide();

        }
        $('feedbacks_auto_response').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_AUTO_RESPONSE_NONE');
        self.feedbacksAutoResponseChange();
    },

    feedbacksAutoResponseChange: function()
    {
        if ($('feedbacks_auto_response').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_AUTO_RESPONSE_NONE')) {
            $('block_accounts_feedbacks_templates').hide();
            $('feedbacks_auto_response_only_positive_container').hide();
        } else {
            $('block_accounts_feedbacks_templates').show();
            $('feedbacks_auto_response_only_positive_container').show();
        }
    },

    //----------------------------------

    feedbacksOpenAddForm: function()
    {
        $('block_accounts_feedbacks_form_template_title_add').show();
        $('block_accounts_feedbacks_form_template_title_edit').hide();

        $('feedbacks_templates_id').value = '';
        $('feedbacks_templates_body').value = '';

        $('block_accounts_feedbacks_form_template_button_cancel').show();
        $('block_accounts_feedbacks_form_template_button_add').show();
        $('block_accounts_feedbacks_form_template_button_edit').hide();

        $('magento_block_ebay_accounts_feedbacks_form_template').show();
        $('feedbacks_templates_body_validate').hide();
    },

    feedbacksOpenEditForm: function(id,body)
    {
        $('block_accounts_feedbacks_form_template_title_add').hide();
        $('block_accounts_feedbacks_form_template_title_edit').show();

        $('feedbacks_templates_id').value = id;
        $('feedbacks_templates_body').value = body;

        $('block_accounts_feedbacks_form_template_button_cancel').show();
        $('block_accounts_feedbacks_form_template_button_add').hide();
        $('block_accounts_feedbacks_form_template_button_edit').show();

        $('magento_block_ebay_accounts_feedbacks_form_template').show();
        $('feedbacks_templates_body_validate').hide();
    },

    feedbacksCancelForm: function()
    {
        $('block_accounts_feedbacks_form_template_title_add').hide();
        $('block_accounts_feedbacks_form_template_title_edit').hide();

        $('feedbacks_templates_id').value = '';
        $('feedbacks_templates_body').value = '';

        $('block_accounts_feedbacks_form_template_button_cancel').hide();
        $('block_accounts_feedbacks_form_template_button_add').hide();
        $('block_accounts_feedbacks_form_template_button_edit').hide();

        $('magento_block_ebay_accounts_feedbacks_form_template').hide();
        $('feedbacks_templates_body_validate').hide();
    },

    //----------------------------------

    feedbacksAddAction: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('feedbacks_templates_body').value.length < 2 || $('feedbacks_templates_body').value.length > 80) {
            $('feedbacks_templates_body_validate').show();
            return;
        } else {
            $('feedbacks_templates_body_validate').hide();
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateEdit'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                account_id: M2ePro.formData.id,
                body: $('feedbacks_templates_body').value
            },
            onSuccess: function(transport) {
                self.feedbacksCancelForm();
                eval('ebayAccountEditTabsFeedbackGrid'+M2ePro.formData.id+'JsObject.reload();');
            }
        });
    },

    feedbacksEditAction: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('feedbacks_templates_body').value.length < 2 || $('feedbacks_templates_body').value.length > 80) {
            $('feedbacks_templates_body_validate').show();
            return;
        } else {
            $('feedbacks_templates_body_validate').hide();
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateEdit'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                id: $('feedbacks_templates_id').value,
                account_id: M2ePro.formData.id,
                body: $('feedbacks_templates_body').value
            },
            onSuccess: function(transport) {
                self.feedbacksCancelForm();
                eval('ebayAccountEditTabsFeedbackGrid'+M2ePro.formData.id+'JsObject.reload();');
            }
        });
    },

    feedbacksDeleteAction: function(id)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return false;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateDelete'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                id: id
            },
            onSuccess: function(transport) {
                eval('ebayAccountEditTabsFeedbackGrid'+M2ePro.formData.id+'JsObject.reload();');
            }
        });
    },

    //----------------------------------

    ebayStoreUpdate: function()
    {
        var self = EbayAccountHandlerObj;
        self.submitForm(M2ePro.url.get('formSubmit', {'update_ebay_store': 1, 'back': base64_encode('edit')}));
    },

    ebayStoreSelectCategory: function(id)
    {
        $('ebay_store_categories_selected_container').show();
        $('ebay_store_categories_selected').value = id;
    },

    ebayStoreSelectCategoryHide: function()
    {
        $('ebay_store_categories_selected_container').hide();
        $('ebay_store_categories_selected').value = '';
    },

    //----------------------------------

    magentoOrdersListingsModeChange: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_MODE_YES')) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
        }

        $('magento_orders_listings_store_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange: function()
    {
        if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
        }

        $('magento_orders_listings_store_id').value = '';
    },

    magentoOrdersListingsOtherModeChange: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES')) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
        }

        $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
        $('magento_orders_listings_other_store_id').value = '';

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange: function()
    {
        if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
        }
    },

    magentoOrdersNumberSourceChange: function()
    {
        var self = EbayAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixModeChange: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            $('magento_orders_number_prefix_container').show();
        } else {
            $('magento_orders_number_prefix_container').hide();
            $('magento_orders_number_prefix_prefix').value = '';
        }

        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixPrefixChange: function()
    {
        var self = EbayAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    renderOrderNumberExample: function()
    {
        var orderNumber = $('sample_magento_order_id').value;
        if ($('magento_orders_number_source').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
            orderNumber = $('sample_ebay_order_id').value;
        }

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;
        }

        $('order_number_example_container').update(orderNumber);
    },

    magentoOrdersCustomerModeChange: function()
    {
        var customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {  // M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::ORDERS_CUSTOMER_MODE_GUEST') || M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::ORDERS_CUSTOMER_MODE_NEW')
            $('magento_orders_customer_id_container').hide();
            $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
        }

        var action = (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
        $('magento_orders_customer_new_website_id_container')[action]();
        $('magento_orders_customer_new_group_id_container')[action]();
        $('magento_orders_customer_new_notifications_container')[action]();

        $('magento_orders_customer_id').value = '';
        $('magento_orders_customer_new_website_id').value = '';
        $('magento_orders_customer_new_group_id').value = '';
        $('magento_orders_customer_new_notifications').value = '';
//        $('magento_orders_customer_new_newsletter_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO');
    },

    magentoOrdersStatusMappingModeChange: function()
    {
        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_new').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_NEW');
        $('magento_orders_status_mapping_paid').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_PAID');
        $('magento_orders_status_mapping_shipped').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED');

        // Default auto create invoice & shipping
        $('magento_orders_invoice_mode').checked = true;
        $('magento_orders_shipment_mode').checked = true;

        var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
        $('magento_orders_status_mapping_new').disabled = disabled;
        $('magento_orders_status_mapping_paid').disabled = disabled;
        $('magento_orders_status_mapping_shipped').disabled = disabled;
        $('magento_orders_invoice_mode').disabled = disabled;
        $('magento_orders_shipment_mode').disabled = disabled;
    },

    magentoOrdersCreationModeChange: function()
    {
        var creationMode = $('magento_orders_creation_mode').value;

        if (creationMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CREATE_IMMEDIATELY')) {
            $('magento_orders_creation_reservation_days_container').show();
            $('magento_orders_qty_reservation_days').value = 1;
            $('magento_orders_qty_reservation_days_container').hide();
        } else {
            $('magento_orders_creation_reservation_days').value = 0;
            $('magento_orders_creation_reservation_days_container').hide();
            $('magento_orders_qty_reservation_days_container').show();
        }
    },

    changeVisibilityForOrdersModesRelatedBlocks: function()
    {
        var self = EbayAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_MODE_NO') &&
            $('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO')) {

            $('magento_block_ebay_accounts_magento_orders_number').hide();
            $('magento_orders_number_source').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');
            $('magento_orders_number_prefix_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_NO');
            self.magentoOrdersNumberPrefixModeChange();

            $('magento_block_ebay_accounts_magento_orders_customer').hide();
            $('magento_orders_customer_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST');
            self.magentoOrdersCustomerModeChange();

            $('magento_block_ebay_accounts_magento_orders_status_mapping').hide();
            $('magento_orders_status_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            self.magentoOrdersStatusMappingModeChange();

            $('magento_block_ebay_accounts_magento_orders_rules').hide();
            $('magento_orders_creation_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID');
            $('magento_orders_creation_reservation_days').value = 0;
            $('magento_orders_qty_reservation_days').value = 1;

            $('magento_block_ebay_accounts_magento_orders_tax').hide();
            $('magento_orders_tax_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_TAX_MODE_MIXED');
        } else {
            $('magento_block_ebay_accounts_magento_orders_number').show();
            $('magento_block_ebay_accounts_magento_orders_customer').show();
            $('magento_block_ebay_accounts_magento_orders_status_mapping').show();
            $('magento_block_ebay_accounts_magento_orders_rules').show();
            $('magento_block_ebay_accounts_magento_orders_tax').show();
        }
    },

    //---------------------------------------

    other_listings_synchronization_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES')) {
            $('other_listings_mapping_mode_tr').show();
            $('other_listings_mapping_mode').simulate('change');
            $('magento_block_ebay_accounts_other_listings_related_store_views').show();
        } else {
            $('other_listings_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_MODE_NO');
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            $('magento_block_ebay_accounts_other_listings_related_store_views').hide();
        }
    },

    other_listings_mapping_mode_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_MODE_YES')) {
            $('magento_block_ebay_accounts_other_listings_product_mapping').show();
        } else {
            $('magento_block_ebay_accounts_other_listings_product_mapping').hide();

            $('mapping_sku_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
            $('mapping_title_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE');
        }

        $('mapping_sku_mode').simulate('change');
        $('mapping_title_mode').simulate('change');
    },

    synchronization_mapped_change: function()
    {
       if (this.value == 0) {
           $('settings_button').hide();
       } else {
           $('settings_button').show();
       }
    },

    mapping_sku_mode_change: function()
    {
        var self        = EbayAccountHandlerObj,
            attributeEl = $('mapping_sku_attribute');

        $('mapping_sku_priority_td').hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE')) {
            $('mapping_sku_priority_td').show();
        }

        attributeEl.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    },

    mapping_title_mode_change: function()
    {
        var self        = EbayAccountHandlerObj,
            attributeEl = $('mapping_title_attribute');

        $('mapping_title_priority_td').hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE')) {
            $('mapping_title_priority_td').show();
        }

        attributeEl.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    }

    //----------------------------------
});