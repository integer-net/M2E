AmazonAccountHandler = Class.create();
AmazonAccountHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-account-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Account', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-require-select-marketplaces', M2ePro.text.need_select_marketplaces_error, function(value, el) {

            var isMarketplaceSelected = false;

            $$('.marketplaces_mode_element').each(function(obj) {
                if (obj.value == 1) {
                    isMarketplaceSelected = true;
                }
            });

            return isMarketplaceSelected;
        });

        Validation.add('M2ePro-marketplace-merchant-id', M2ePro.text.need_authorized_account_error, function(value, el){
            // reset error message to the default
            this.error = M2ePro.text.need_authorized_account_error;

            var marketplace_id = $('marketplace_id').value;

            if (el.name != 'marketplace_merchant_id_' + marketplace_id) return true;

            var pattern = /^[A-Z0-9]*$/;
            if (!pattern.test(value)) {
                return false;
            }

            var checkResult = false;
            var checkReason = null;

            new Ajax.Request( M2ePro.url.checkAuthAction ,
                {
                    method: 'post',
                    asynchronous : false,
                    parameters : {
                        merchant_id : value,
                        marketplace_id : marketplace_id
                    },
                    onSuccess: function (transport)
                    {
                        var response = transport.responseText.evalJSON();
                        checkResult = response['result'];
                        checkReason = response['reason'];
                    }
                });

            if (checkReason != null) {
                this.error = M2ePro.text.account_access_certain_error.replace('%s', checkReason);
            }

            return checkResult;

        });

        Validation.add('M2ePro-require-select-attribute', M2ePro.text.need_select_attributes_error, function(value, el) {

            if ($('other_listings_mapping_mode').value == AmazonAccountHandlerObj.OTHER_LISTINGS_MAPPING_MODE_NO) {
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

    completeStep: function()
    {
        window.opener.completeStep = 1;
        window.close();
    },

    //----------------------------------

    delete_click: function()
    {
        if (!confirm(M2ePro.text.account_delete_alert)) {
            return;
        }
        setLocation(M2ePro.url.deleteAction);
    },

    //----------------------------------

    changeMarketplace : function(id)
    {
        var self = AmazonAccountHandlerObj;

        $$('.marketplace_empty_option').each(function(obj) {
            obj.hide();
        });

        $$('.marketplaces_view_element').each(function(obj) {
            obj.hide();
        });

        $$('.marketplaces_mode_element').each(function(obj) {
            obj.value = 0;
        });

        $('marketplace_mode_'+id).value = 1;

        if ($('marketplace_current_mode_'+id).value == 0) {
            $('marketplaces_register_url_container_'+id).show();
            $('marketplaces_application_name_container_'+id).show();
            $('marketplaces_developer_key_container_'+id).show();
        }

        $('marketplaces_merchant_id_container_'+id).show();
        $('marketplaces_related_store_id_container_'+id).show();
    },

    //----------------------------------

    other_listings_synchronization_change : function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == self.OTHER_LISTINGS_SYNCHRONIZATION_YES) {
            $('other_listings_mapping_mode_tr').show();
            $('other_listings_store_view_tr').show();
        } else {
            $('other_listings_mapping_mode').value = self.OTHER_LISTINGS_MAPPING_MODE_NO;
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            $('other_listings_store_view_tr').hide();
        }
    },

    other_listings_mapping_mode_change : function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == self.OTHER_LISTINGS_MAPPING_MODE_YES) {
            $('magento_block_amazon_accounts_other_listings_product_mapping').show();
            $('magento_block_amazon_accounts_other_listings_move_mode').show();
        } else {
            $('magento_block_amazon_accounts_other_listings_product_mapping').hide();
            $('magento_block_amazon_accounts_other_listings_move_mode').hide();

            $('other_listings_move_mode').value = self.OTHER_LISTINGS_MOVE_TO_LISTINGS_DISABLED;
            $('mapping_general_id_mode').value = self.OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE;
            $('mapping_sku_mode').value = self.OTHER_LISTINGS_MAPPING_SKU_MODE_NONE;
            $('mapping_title_mode').value = self.OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE;
        }

        $('mapping_general_id_mode').simulate('change');
        $('mapping_sku_mode').simulate('change');
        $('mapping_title_mode').simulate('change');

        $('other_listings_move_mode').simulate('change');
    },

    //----------------------------------

    mapping_general_id_mode_change : function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == self.OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE) {
            $('mapping_general_id_priority_td').hide();
            $('mapping_general_id_attribute_tr').hide();
        } else {
            $('mapping_general_id_priority_td').show();
            $('mapping_general_id_attribute_tr').show();
        }
    },

    mapping_sku_mode_change : function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == self.OTHER_LISTINGS_MAPPING_SKU_MODE_NONE) {
            $('mapping_sku_priority_td').hide();
            $('mapping_sku_attribute_tr').hide();
        } else {
            $('mapping_sku_priority_td').show();

            if (this.value == self.OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $('mapping_sku_attribute_tr').show();
            } else {
                $('mapping_sku_attribute_tr').hide();
            }
        }
    },

    mapping_title_mode_change : function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == self.OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE) {
            $('mapping_title_priority_td').hide();
            $('mapping_title_attribute_tr').hide();
        } else {
            $('mapping_title_priority_td').show();

            if (this.value == self.OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE) {
                $('mapping_title_attribute_tr').show();
            } else {
                $('mapping_title_attribute_tr').hide();
            }
        }
    },

    //----------------------------------

    move_mode_change : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('other_listings_move_mode').value == self.OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED) {
            $('other_listings_move_synch_tr').show();
        } else {
            $('other_listings_move_synch_tr').hide();
        }
    },

    //----------------------------------

    ordersModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('orders_mode').value == self.ORDERS_MODE_YES) {
            $('magento_block_amazon_accounts_magento_orders_listings').show();
            $('magento_block_amazon_accounts_magento_orders_listings_other').show();
        } else {
            $('magento_block_amazon_accounts_magento_orders_listings').hide();
            $('magento_block_amazon_accounts_magento_orders_listings_other').hide();
        }

        $('magento_orders_listings_mode').value = self.MAGENTO_ORDERS_LISTINGS_MODE_NO;
        $('magento_orders_listings_other_mode').value = self.MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO;

        self.magentoOrdersListingsModeChange();
        self.magentoOrdersListingsOtherModeChange();
    },

    magentoOrdersListingsModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == self.MAGENTO_ORDERS_LISTINGS_MODE_YES) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
        }

        $('magento_orders_listings_store_mode').value = self.MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT;
        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_store_mode').value == self.MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
        }

        $('magento_orders_listings_store_id').value = '';
    },

    magentoOrdersListingsOtherModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_other_mode').value == self.MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
        }

        $('magento_orders_listings_other_product_mode').value = self.MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE;
        $('magento_orders_listings_other_store_id').value = '';

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_other_product_mode').value == self.MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
        }
    },

    magentoOrdersFbaModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_fba_mode').value == self.MAGENTO_ORDERS_FBA_MODE_NO) {
            $('magento_orders_fba_stock_mode_container').hide();
            $('magento_orders_fba_stock_mode').value = self.MAGENTO_ORDERS_FBA_STOCK_MODE_YES;
        } else {
            $('magento_orders_fba_stock_mode_container').show();
        }
    },

    magentoOrdersCustomerModeChange : function()
    {
        var self = AmazonAccountHandlerObj,
            customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == self.MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {  // self.ORDERS_CUSTOMER_MODE_GUEST || self.ORDERS_CUSTOMER_MODE_NEW
            $('magento_orders_customer_id_container').hide();
            $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
        }

        var action = (customerMode == self.MAGENTO_ORDERS_CUSTOMER_MODE_NEW) ? 'show' : 'hide';
        $('magento_orders_customer_new_website_id_container')[action]();
        $('magento_orders_customer_new_group_id_container')[action]();
        $('magento_orders_customer_new_notifications_container')[action]();

        $('magento_orders_customer_id').value = '';
        $('magento_orders_customer_new_website_id').value = '';
        $('magento_orders_customer_new_group_id').value = '';
        $('magento_orders_customer_new_notifications').value = '';
//        $('magento_orders_customer_new_newsletter_mode').value = self.MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO;
    },

    magentoOrdersStatusMappingModeChange : function()
    {
        var self = AmazonAccountHandlerObj;

        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_processing').value = self.MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING;
        $('magento_orders_status_mapping_shipped').value = self.MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED;

        // Default auto create invoice & shipment
        $('magento_orders_invoice_mode').checked = true;
        $('magento_orders_shipment_mode').checked = true;

        var disabled = $('magento_orders_status_mapping_mode').value == self.MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
        $('magento_orders_status_mapping_processing').disabled = disabled;
        $('magento_orders_status_mapping_shipped').disabled = disabled;
        $('magento_orders_invoice_mode').disabled = disabled;
        $('magento_orders_shipment_mode').disabled = disabled;
    },

    changeVisibilityForOrdersModesRelatedBlocks : function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == self.MAGENTO_ORDERS_LISTINGS_MODE_NO &&
            $('magento_orders_listings_other_mode').value == self.MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO) {

            $('magento_block_amazon_accounts_magento_orders_fba').hide();
            $('magento_orders_fba_stock_mode').value = self.MAGENTO_ORDERS_FBA_STOCK_MODE_YES;

            $('magento_block_amazon_accounts_magento_orders_customer').hide();
            $('magento_orders_customer_mode').value = self.MAGENTO_ORDERS_CUSTOMER_MODE_GUEST;
            self.magentoOrdersCustomerModeChange();

            $('magento_block_amazon_accounts_magento_orders_status_mapping').hide();
            $('magento_orders_status_mapping_mode').value = self.MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
            self.magentoOrdersStatusMappingModeChange();

            $('magento_block_amazon_accounts_magento_orders_rules').hide();
            $('magento_orders_qty_reservation_days').value = 0;

            $('magento_block_amazon_accounts_magento_orders_tax').hide();
            $('magento_orders_tax_mode').value = self.MAGENTO_ORDERS_TAX_MODE_MAGENTO;
        } else {
            $('magento_block_amazon_accounts_magento_orders_fba').show();
            $('magento_block_amazon_accounts_magento_orders_customer').show();
            $('magento_block_amazon_accounts_magento_orders_status_mapping').show();
            $('magento_block_amazon_accounts_magento_orders_tax').show();
            $('magento_block_amazon_accounts_magento_orders_rules').show();
        }
    }

    //----------------------------------
});