EbayListingVariationProductManageVariationsGridHandler = Class.create(GridHandler, {

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    //----------------------------------

    getMaxProductsInPart: function()
    {
        return 1000;
    },

    //----------------------------------

    prepareActions: function()
    {
        return false;
    },

    //----------------------------------

    afterInitPage: function($super)
    {
        $super();

        $$('.attributes-options-filter').each(this.initAttributesOptionsFilter, this);
    },

    //----------------------------------

    parseResponse: function(response)
    {
        if (!response.responseText.isJSON()) {
            return;
        }

        return response.responseText.evalJSON();
    },

    //----------------------------------

    initAttributesOptionsFilter: function(filterEl)
    {
        var srcElement = Element.down(filterEl, 'select');

        srcElement.observe('change', this.onAttributesOptionsFilterChange.bind(this));

        var valuesDiv = Element.down(filterEl, '.attributes-options-filter-values');
        valuesDiv.optionsCount = valuesDiv.childElementCount;

        if(valuesDiv.optionsCount == srcElement.childElementCount - 1) {
            srcElement.hide();
        }

        valuesDiv.optionsIterator = 0;
        valuesDiv.childElements().each(function(attrValue) {

            var removeImg = Element.down(attrValue, '.filter-param-remove'),
                attrName = Element.down(attrValue, 'input[type="hidden"]'),
                selectedOption = Element.down(filterEl, 'select option[value="' + attrName.value + '"]');

            selectedOption.hide();

            valuesDiv.optionsIterator++;

            removeImg.show();
            removeImg.observe('click', function() {
                valuesDiv.optionsCount--;
                selectedOption.show();
                srcElement.show();
                Element.remove(attrValue);
            });
        }, this);
    },

    onAttributesOptionsFilterChange: function(e)
    {
        var srcElement = e.target || e.srcElement,
            parentDiv = Element.up(srcElement, '.attributes-options-filter'),
            valuesDiv = Element.down(parentDiv, '.attributes-options-filter-values'),
            selectedOption = Element.down(srcElement, '[value="' + srcElement.value + '"]');

        selectedOption.hide();

        valuesDiv.optionsCount++;
        valuesDiv.optionsIterator++;

        srcElement.enable();
        if(valuesDiv.optionsCount == srcElement.childElementCount - 1) {
            srcElement.hide();
        }

        var filterName = parentDiv.id.replace('attributes-options-filter_', '');

        var newOptionContainer = new Element('div'),
            newOptionLabel = new Element('div'),
            newOptionValue = new Element('input', {
                type: 'text',
                name: filterName + '[' + valuesDiv.optionsIterator + '][value]'
            }),
            newOptionAttr = new Element('input', {
                type: 'hidden',
                name: filterName + '[' + valuesDiv.optionsIterator + '][attr]',
                value: srcElement.value
            }),
            removeImg = Element.clone(Element.down(parentDiv, '.attributes-options-filter-selector .filter-param-remove'));

        newOptionLabel.innerHTML = srcElement.value + ': ';
        removeImg.show();

        Event.observe(newOptionValue, 'keypress', this.getGridObj().filterKeyPress.bind(this.getGridObj()));

        newOptionContainer.insert({ bottom: newOptionLabel });
        newOptionContainer.insert({ bottom: newOptionValue });
        newOptionContainer.insert({ bottom: newOptionAttr });
        newOptionContainer.insert({ bottom: removeImg });

        valuesDiv.insert({ bottom: newOptionContainer });

        removeImg.observe('click', function() {
            valuesDiv.optionsCount--;
            selectedOption.show();
            srcElement.show();
            newOptionContainer.remove();
        }, this);

        srcElement.value = '';
    },

    //----------------------------------

    editVariationIdentifiers: function(editBtn, variationId)
    {
        $('variation_identifiers_edit_'+variationId).show();
        $('variation_identifiers_'+variationId).hide();
        editBtn.hide();
    },

    confirmVariationIdentifiers: function(editBtn, variationId)
    {
        var self = this,
            form = $('variation_identifiers_edit_'+variationId);

        var data = form.serialize(true);

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_variation_product_manage/setIdentifiers'), {
            method: 'post',
            parameters: data,
            onSuccess: function(transport) {

                var response = self.parseResponse(transport);
                if(response.success) {
                    VariationsGridHandlerObj.getGridObj().reload();
                }
            }
        });
    },

    cancelVariationIdentifiers: function(variationId)
    {
        $('variation_identifiers_edit_'+variationId).reset();
        $('variation_identifiers_edit_'+variationId).hide();
        $('variation_identifiers_'+variationId).show();
        $('edit_variations_'+variationId).show();
    }

});
