EbayListingCategoryChooserBrowseHandler = Class.create();
EbayListingCategoryChooserBrowseHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.marketplaceId = null;
        this.observers = {
            "leaf_selected": [],
            "not_leaf_selected": [],
            "any_selected": []
        };
    },

    //----------------------------------

    setMarketplaceId: function(marketplaceId)
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;
        self.marketplaceId = marketplaceId;
    },

    getMarketplaceId: function()
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        if (self.marketplaceId === null) {
            alert('You must set marketplace');
        }

        return self.marketplaceId;
    },

    getCategoriesSelectElementId: function(categoryId)
    {
        return 'category_chooser_select_' + categoryId;
    },

    getCategoryChildrenElementId: function(categoryId)
    {
        return 'category_chooser_children_' + categoryId;
    },

    getSelectedCategories: function()
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        var categoryId = 0;
        var selectedCategories = [];
        var isLastCategory = false;

        while (!isLastCategory) {
            var categorySelect = $(self.getCategoriesSelectElementId(categoryId));
            if (!categorySelect || categorySelect.selectedIndex == -1) {
                break;
            }

            categoryId = selectedCategories[selectedCategories.length]
                = categorySelect.options[categorySelect.selectedIndex].value;

            if (categorySelect.options[categorySelect.selectedIndex].getAttribute('is_leaf') == 1) {
                isLastCategory = true;
            }
        }

        return selectedCategories;
    },

    //----------------------------------

    renderTopLevelCategories: function(containerId)
    {
        this.prepareDomStructure(0, $(containerId));
        this.renderChildCategories(0);
    },

    renderChildCategories: function (parentCategoryId)
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getChildCategories'),
        {
            method: 'post',
            asynchronous: true,
            parameters: {
                "parent_category_id": parentCategoryId,
                "marketplace_id": self.getMarketplaceId(),
                "account_id": EbayListingCategoryChooserHandlerObj.getAccountId(),
                "category_type": $('category_type').value
            },
            onSuccess: function(transport)
            {
                if (transport.responseText.length <= 2) {
                    self.simulate('leaf_selected', self.getSelectedCategories());
                    return;
                }

                var categories = JSON.parse(transport.responseText);
                var optionsHtml = '';
                var arrowString = '';
                categories.each(function(category) {
                    if (parseInt(category.is_leaf) == 0) {
                        arrowString = ' > ';
                    } else {
                        arrowString = '';
                    }

                    optionsHtml += '<option is_leaf="'+category.is_leaf+'" value="'+category.category_id+'">'+
                        category.title + arrowString +
                        '</option>';
                });

                $(self.getCategoriesSelectElementId(parentCategoryId)).innerHTML = optionsHtml;
                $(self.getCategoriesSelectElementId(parentCategoryId)).style.display = 'inline-block';
                $('chooser_browser').scrollLeft = $('chooser_browser').scrollWidth;
            }
        });
    },

    onSelectCategory: function (select)
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        var parentCategoryId = select.id.replace(self.getCategoriesSelectElementId(""), "");
        var categoryId = select.options[select.selectedIndex].value;
        var is_leaf = select.options[select.selectedIndex].getAttribute('is_leaf');

        var selectedCategories = self.getSelectedCategories();

        var parentDiv = $(self.getCategoryChildrenElementId(parentCategoryId));
        parentDiv.innerHTML = '';

        self.simulate('any_selected', selectedCategories);

        if (is_leaf == 1) {
            self.simulate('leaf_selected', selectedCategories);
            return;
        }

        self.simulate('not_leaf_selected', selectedCategories);

        self.prepareDomStructure(categoryId, parentDiv);
        self.renderChildCategories(categoryId);
    },

    prepareDomStructure: function(categoryId, parentDiv)
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        var childrenSelect = document.createElement('select');
        childrenSelect.id = self.getCategoriesSelectElementId(categoryId);
        childrenSelect.style.minWidth = '200px';
        childrenSelect.size = 10;
        childrenSelect.onchange = function() {EbayListingCategoryChooserBrowseHandlerObj.onSelectCategory(this);};
        childrenSelect.style.display = 'none';
        parentDiv.appendChild(childrenSelect);

        var childrenDiv = document.createElement('div');
        childrenDiv.id = self.getCategoryChildrenElementId(categoryId);
        childrenDiv.className = 'category-children-block';
        parentDiv.appendChild(childrenDiv);
    },

    //----------------------------------

    observe: function(event, observer)
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        if (typeof observer != 'function') {
            alert('Observer must be a function!');
            return;
        }

        if (typeof self.observers[event] == 'undefined') {
            alert('Event does not supported!');
            return;
        }

        self.observers[event][self.observers[event].length] = observer;
    },

    simulate: function(event, parameters)
    {
        var self = EbayListingCategoryChooserBrowseHandlerObj;

        parameters = parameters || null;

        if (typeof self.observers[event] == 'undefined') {
            alert('Event does not supported!');
            return;
        }

        if (self.observers[event].length == 0) {
            return;
        }

        self.observers[event].each(function(observer) {
            if (parameters == null) {
                (observer)();
            } else {
                (observer)(parameters);
            }
        });
    }

    //----------------------------------
});