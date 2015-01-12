CommonAmazonTemplateNewProductDescriptionHandler = Class.create();
CommonAmazonTemplateNewProductDescriptionHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    attribute_sets_confirm: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[image_main_attribute]',
            'image_main_attribute_td',
            M2ePro.formData.description.image_main_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[gallery_images_attribute]',
            'gallery_images_attribute_td',
            M2ePro.formData.description.gallery_images_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[manufacturer_part_number_custom_attribute]',
            'manufacturer_part_number_attribute_td',
            M2ePro.formData.description.manufacturer_part_number_custom_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[package_weight_custom_attribute]',
            'package_weight_attribute_td',
            M2ePro.formData.description.package_weight_custom_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[shipping_weight_custom_attribute]',
            'shipping_weight_attribute_td',
            M2ePro.formData.description.shipping_weight_custom_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[package_weight_unit_of_measure_custom_attribute]',
            'package_weight_unit_of_measure_attribute_td',
            M2ePro.formData.description.package_weight_unit_of_measure_custom_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[shipping_weight_unit_of_measure_custom_attribute]',
            'shipping_weight_unit_of_measure_attribute_td',
            M2ePro.formData.description.shipping_weight_unit_of_measure_custom_attribute
        );
        AttributeSetHandlerObj.renderAttributesWithEmptyOption(
            'description[target_audience_custom_attribute]',
            'target_audience_attribute_td',
            M2ePro.formData.description.target_audience_custom_attribute
        );

        AttributeSetHandlerObj.renderAttributes('select_attributes_for_title', 'select_attributes_for_title_span', 0, '150');
        AttributeSetHandlerObj.renderAttributes('select_attributes_for_brand', 'select_attributes_for_brand_span', 0, '150');
        AttributeSetHandlerObj.renderAttributes('select_attributes_for_manufacturer', 'select_attributes_for_manufacturer_span', 0, '150');

        for (var i = 0; i < 5; i++) {
            AttributeSetHandlerObj.renderAttributes('select_attributes_for_bullet_points_' + i, 'select_attributes_for_bullet_points_' + i + '_span', 0, '150');
        }

        for (var i = 0; i < 5; i++) {
            AttributeSetHandlerObj.renderAttributes('select_attributes_for_search_terms_' + i, 'select_attributes_for_search_terms_' + i + '_span', 0, '150');
        }

        AttributeSetHandlerObj.renderAttributes('select_attributes', 'select_attributes_span');
    },

    //----------------------------------

    title_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;
        self.setTextVisibilityMode(this, 'custom_title_tr');
    },

    brand_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;
        self.setTextVisibilityMode(this, 'custom_brand_tr');
    },

    manufacturer_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;
        self.setTextVisibilityMode(this, 'custom_manufacturer_tr');
    },

    manufacturer_part_number_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::MANUFACTURER_PART_NUMBER_MODE_NONE')] = function() {
            $('manufacturer_part_number_custom_value_tr').hide();
            $('manufacturer_part_number_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE')] = function() {
            $('manufacturer_part_number_custom_value_tr').show();
            $('manufacturer_part_number_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE')] = function() {
            $('manufacturer_part_number_custom_value_tr').hide();
            $('manufacturer_part_number_custom_attribute_tr').show();
        };

        handlers[this.value].call(self);
    },

    package_weight_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::PACKAGE_WEIGHT_MODE_NONE')] = function() {
            $('package_weight_custom_value_tr').hide();
            $('package_weight_custom_attribute_tr').hide();
            $('package_weight_unit_of_measure_mode_tr').hide();
            $('package_weight_unit_of_measure_custom_value_tr').hide();
            $('package_weight_unit_of_measure_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::PACKAGE_WEIGHT_MODE_CUSTOM_VALUE')] = function() {
            $('package_weight_custom_value_tr').show();
            $('package_weight_custom_attribute_tr').hide();
            $('package_weight_unit_of_measure_mode').simulate('change');
            $('package_weight_unit_of_measure_mode_tr').show();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::PACKAGE_WEIGHT_MODE_CUSTOM_ATTRIBUTE')] = function() {
            $('package_weight_custom_value_tr').hide();
            $('package_weight_custom_attribute_tr').show();
            $('package_weight_unit_of_measure_mode').simulate('change');
            $('package_weight_unit_of_measure_mode_tr').show();
        };

        handlers[this.value].call(self);
    },

    shipping_weight_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::SHIPPING_WEIGHT_MODE_NONE')] = function() {
            $('shipping_weight_custom_value_tr').hide();
            $('shipping_weight_custom_attribute_tr').hide();
            $('shipping_weight_unit_of_measure_mode_tr').hide();
            $('shipping_weight_unit_of_measure_custom_value_tr').hide();
            $('shipping_weight_unit_of_measure_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::SHIPPING_WEIGHT_MODE_CUSTOM_VALUE')] = function() {
            $('shipping_weight_custom_value_tr').show();
            $('shipping_weight_custom_attribute_tr').hide();
            $('shipping_weight_unit_of_measure_mode').simulate('change');
            $('shipping_weight_unit_of_measure_mode_tr').show();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::SHIPPING_WEIGHT_MODE_CUSTOM_ATTRIBUTE')] = function() {
            $('shipping_weight_custom_value_tr').hide();
            $('shipping_weight_unit_of_measure_mode').simulate('change');
            $('shipping_weight_custom_attribute_tr').show();
            $('shipping_weight_unit_of_measure_mode_tr').show();
        };

        handlers[this.value].call(self);
    },

    package_weight_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::PACKAGE_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE')] = function() {
            $('package_weight_unit_of_measure_custom_value_tr').show();
            $('package_weight_unit_of_measure_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::PACKAGE_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE')] = function() {
            $('package_weight_unit_of_measure_custom_value_tr').hide();
            $('package_weight_unit_of_measure_custom_attribute_tr').show();
        };

        handlers[this.value].call(self);
    },

    shipping_weight_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::SHIPPING_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE')] = function() {
            $('shipping_weight_unit_of_measure_custom_value_tr').show();
            $('shipping_weight_unit_of_measure_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::SHIPPING_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE')] = function() {
            $('shipping_weight_unit_of_measure_custom_value_tr').hide();
            $('shipping_weight_unit_of_measure_custom_attribute_tr').show();
        };

        handlers[this.value].call(self);
    },

    target_audience_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::TARGET_AUDIENCE_MODE_NONE')] = function() {
            $('target_audience_custom_value_tr').hide();
            $('target_audience_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::TARGET_AUDIENCE_MODE_CUSTOM_VALUE')] = function() {
            $('target_audience_custom_value_tr').show();
            $('target_audience_custom_attribute_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::TARGET_AUDIENCE_MODE_CUSTOM_ATTRIBUTE')] = function() {
            $('target_audience_custom_value_tr').hide();
            $('target_audience_custom_attribute_tr').show();
        };

        handlers[this.value].call(self);
    },

    multi_element_mode_change: function(type,max)
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::' + type.toUpperCase() + '_MODE_NONE')) {
            $$('.' + type + '_tr').invoke('hide');
            $$('input[name="description[' + type + '][]"]').each(function(obj) {
                obj.value = '';
            });
            $(type + '_actions_tr').hide();
        } else {

            if (AttributeSetHandlerObj.checkAttributeSetSelection()) {
                var visibleElementsCounter = 0;

                $$('.' + type + '_tr').each(function(obj) {
                    if (visibleElementsCounter == 0 || $(obj).select('input[name="description[' + type + '][]"]')[0].value != '') {
                        $(obj).show();
                        visibleElementsCounter++;
                    }
                });

                $(type + '_actions_tr').show();

                if (visibleElementsCounter > 1) {
                    $('hide_' + type + '_action').removeClassName('action-disabled');
                }

                if (visibleElementsCounter < max) {
                    $('show_' + type + '_action').removeClassName('action-disabled');
                } else {
                    $('show_' + type + '_action').addClassName('action-disabled');
                }

                if (visibleElementsCounter == 1) {
                    $('show_' + type + '_action').addClassName('action-disabled');
                }
            } else {
                this.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::' + type.toUpperCase() + '_MODE_NONE');
            }
        }
    },

    multi_element_keyup: function(type,element)
    {
        if (!element.value) {
            return $('show_' + type + '_action').addClassName('action-disabled');
        }

        var hiddenElements = $$('.' + type + '_tr').findAll(function(obj) {
            return !$(obj).visible();
        });

        if (hiddenElements.size() != 0) {
            $('show_' + type + '_action').removeClassName('action-disabled');
        }
    },

    description_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        $$('.c-custom_description_tr').invoke('hide');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::DESCRIPTION_MODE_CUSTOM')) {
            if (AttributeSetHandlerObj.checkAttributeSetSelection()) {
                $$('.c-custom_description_tr').invoke('show');
            } else {
                this.value = 0;
            }
        }
    },

    image_main_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::IMAGE_MAIN_MODE_NONE')) {
            $('gallery_images_mode_tr').hide();
            $('gallery_images_mode').value = 0;
            $('gallery_images_mode').simulate('change');
        } else {
            $('gallery_images_mode_tr').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::IMAGE_MAIN_MODE_ATTRIBUTE') &&
            !AttributeSetHandlerObj.checkAttributeSetSelection()) {

            this.value = M2ePro.formData.image_main_mode;
            return;
        }

        $('image_main_attribute_tr')[this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::IMAGE_MAIN_MODE_ATTRIBUTE') ? 'show' : 'hide']();
    },

    gallery_images_mode_change: function()
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        $('gallery_images_limit_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::GALLERY_IMAGES_MODE_PRODUCT')) {
            $('gallery_images_limit_tr').show();
        }

        $('gallery_images_attribute_tr')[this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::GALLERY_IMAGES_MODE_ATTRIBUTE') ? 'show' : 'hide']();
    },

    //----------------------------------

    setTextVisibilityMode: function(obj, elementName)
    {
        var self = AmazonTemplateNewProductDescriptionHandlerObj;

        if (obj.value == 1) {
            $(elementName).show();

        } else {
            $(elementName).hide();
        }
    },

    //----------------------------------

    showElement: function(type)
    {
        var emptyVisibleElementsExist = $$('.' + type + '_tr').any(function(obj) {
            return $(obj).visible() && $(obj).select('input[name="description[' + type + '][]"]')[0].value == '';
        });

        if (emptyVisibleElementsExist) {
            return;
        }

        var hiddenElements = $$('.' + type + '_tr').findAll(function(obj) {
            return !$(obj).visible();
        });

        if (hiddenElements.size() == 0) {
            return;
        }

        hiddenElements.shift().show();
        $('hide_' + type + '_action').removeClassName('action-disabled');
        $('show_' + type + '_action').addClassName('action-disabled');
    },

    hideElement: function(type)
    {
        var visibleElements = $$('.' + type + '_tr').findAll(Element.visible);

        if (visibleElements.size() == 1) {
            var elementMode = $(type + '_mode');
            elementMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::' + type.toUpperCase() + '_MODE_NONE');
            elementMode.simulate('change');
        }

        if (visibleElements.size() > 1) {
            var lastVisibleElement = visibleElements.pop();
            lastVisibleElement.select('input[name="description[' + type + '][]"]')[0].value = '';
            lastVisibleElement.hide();
        }

        $('show_' + type + '_action').removeClassName('action-disabled');
    }

    //----------------------------------
});