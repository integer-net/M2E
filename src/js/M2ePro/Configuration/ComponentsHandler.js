ConfigurationComponentsHandler = Class.create();
ConfigurationComponentsHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-require-select-component', M2ePro.translator.translate('You should enable at least one component.'), function(value) {

            var isComponentSelected = false;

            $$('.M2ePro-component-other').each(function(el) {
                if ($(el).value == 1) {
                    isComponentSelected = true;
                }
            });

            return isComponentSelected;
        });

        Validation.add('M2ePro-check-default-component', M2ePro.translator.translate('Default component should be enabled.'), function(value) {

            var componentDefault        = $('view_common_component_default').value.toLowerCase(),
                componentsEnabledNumber = 0;

            $$('.M2ePro-component-other').each(function(el) {
                if ($(el).value == 1) {
                    componentsEnabledNumber++;
                }
            });

            if (componentsEnabledNumber <= 1) {
                return true;
            }

            return $('component_' + componentDefault + '_mode') &&
                   $('component_' + componentDefault + '_mode').value == 1;
        });
    },

    //----------------------------------

    component_mode_change : function()
    {
        var enabledComponents = 0;

        $$('.M2ePro-component-other').each(function(el) {
            if ($(el).value == 1) {
                enabledComponents++;
            }
        });

        ComponentsHandlerObj.updateDefaultComponentSelect();

        if (enabledComponents >= 2) {
            $('view_common_component_default_tr').show();
        } else {

            var defaultComponent = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK');
            if ($('component_amazon_mode').value == 1) {
                defaultComponent = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK');
            } else if ($('component_buy_mode').value == 1) {
                defaultComponent = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Buy::NICK');
            } else if ($('component_play_mode').value == 1) {
                defaultComponent = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Play::NICK');
            }

            $('view_common_component_default').value = defaultComponent;
            $('view_common_component_default_tr').hide();
        }
    },

    updateDefaultComponentSelect : function()
    {
        var html       = '',
            selected   = '',

            components = [
                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'),
                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Buy::NICK'),
                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Play::NICK')
            ];

        components.each(function(nick){

            if ($('component_' + nick + '_mode').value == 1) {

                $('view_common_component_default').value == nick
                    ? selected = ' selected="selected"'
                    : selected = '';

                html += '<option value="' + nick + '"' + selected + '>' +
                            M2ePro.php.constant('Ess_M2ePro_Helper_Component_' + nick[0].toUpperCase() + nick.slice(1) + '::TITLE') +
                        '</option>';
            }
        });

        $('view_common_component_default').innerHTML = html;
    },

    //----------------------------------

    completeStep : function()
    {
        var self = this;

        if(configEditForm.validate()) {
            new Ajax.Request( M2ePro.url.get('formSubmit', $('config_edit_form').serialize(true)) ,
                {
                    method: 'get',
                    asynchronous: true,
                    onSuccess: function(transport)
                    {
                        window.opener.completeStep = 1;
                        window.close();
                    }
                });
        };
    }

    //----------------------------------
});