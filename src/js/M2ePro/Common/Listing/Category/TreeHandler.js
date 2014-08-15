ListingCategoryTreeHandler = Class.create();
ListingCategoryTreeHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    tree_buildCategory: function(parent, config)
    {
        if (!config) {
            return null;
        }

        if (parent && config && config.length) {
            for (var i = 0; i < config.length; i++) {

                config[i].uiProvider = Ext.tree.CheckboxNodeUI;

                var node = new Ext.tree.TreeNode(config[i]);

                for (var j=0;j<initTreeSelectedNodes.length;j++) {
                    if (config[i].id == initTreeSelectedNodes[j][0]) {
                        initTreeSelectedNodes[j][1] = node;
                        initTreeSelectedNodes[j][1].attributes.checked = true;
                        break;
                    }
                }

                parent.appendChild(node);

                if (config[i].children) {
                    ListingCategoryTreeHandlerObj.tree_buildCategory(node, config[i].children);
                }

            }
        }
    },

    tree_processChildren: function(node, state)
    {
        if (!node.hasChildNodes()) {
            return false;
        }

        for (var i = 0; i < node.childNodes.length; i++) {
            node.childNodes[i].ui.check(state);
            if (node.childNodes[i].hasChildNodes()) {
                ListingCategoryTreeHandlerObj.tree_processChildren(node.childNodes[i], state);
            }
        }

        return true;
    },

    tree_categoryAdd: function(id, node)
    {
        categories_selected_items.push(id);
        array_unique(categories_selected_items);

        if (!node.isLeaf() && node.hasChildNodes()) {
            ListingCategoryTreeHandlerObj.tree_processChildren(node, node.attributes.checked);
        }
    },

    tree_categoryRemove: function(id, node)
    {
        while (categories_selected_items.indexOf(id) != -1) {
            categories_selected_items.splice(categories_selected_items.indexOf(id), 1);
        }

        array_unique(categories_selected_items);

        if (!node.isLeaf() && node.hasChildNodes()) {
            ListingCategoryTreeHandlerObj.tree_processChildren(node, node.attributes.checked);
        }
    }

    //----------------------------------
});