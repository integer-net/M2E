TranslationHandler = Class.create();
TranslationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro)
    {
        this.M2ePro = M2ePro;
    },

    //----------------------------------

    resetSuggestion: function(element)
    {
        var self = TranslationHandlerObj;

        if (!confirm(self.M2ePro.text.confirm_message)) {
            return;
        }

        var id = element.id;
        var textId = id.replace('reset_link_', '');
        var suggestions = 0;
        var suggestionText = element.up().previous().childElements()[0].innerHTML;

        $('loading-mask').show();

        new Ajax.Request(self.M2ePro.url.removeSuggestion,
            {
                method: 'post',
                parameters : {
                    text_id: textId
                },
                onSuccess: function (transport)
                {
                    translationGridJsObject.reload(translationGridJsObject.url);
                    $('loading-mask').hide();
                }
            });
    },

    confirmSuggestion: function(element)
    {
        var self = TranslationHandlerObj;

        var id = element.id;
        var textId = '';
        var suggestionText = '';
        if (id.indexOf('save_link_') + 1) {
            textId = id.replace('save_link_', '');
            suggestionText = $('new_custom_suggestion_' + textId).value;
            suggestionText = htmlentities(suggestionText);
        } else {
            textId = id.replace('confirm_link_', '');
            suggestionText = element.up().previous().childElements()[0].innerHTML;
        }

        var suggestions = 0;
        $$('.suggestions_' + textId).each(function(el) {
            suggestions++;
        });

        $('loading-mask').show();

        new Ajax.Request(self.M2ePro.url.removeSuggestion,
            {
                method: 'post',
                parameters : {
                    text_id: textId
                },
                onSuccess: function (transport)
                {
                    new Ajax.Request(self.M2ePro.url.addSuggestion,
                        {
                            method: 'post',
                            parameters : {
                                text_id: textId,
                                suggestion_text: suggestionText
                            },
                            onSuccess: function (transport)
                            {
                                translationGridJsObject.reload(translationGridJsObject.url);
                                $('loading-mask').hide();
                            }
                        });
                }
            });
    },

    addSuggestion: function(element)
    {
        var self = TranslationHandlerObj;

        var id = element.id;
        var textId = id.replace('confirm_link_', '');
        var suggestionText = $('new_suggestion_' + textId).value;
        suggestionText = htmlentities(suggestionText);

        var suggestions = 0;
        $$('.suggestions_' + textId).each(function(el) {
            suggestions++;
        });

        $('loading-mask').show();

        new Ajax.Request(self.M2ePro.url.addSuggestion,
            {
                method: 'post',
                parameters : {
                    text_id: textId,
                    suggestion_text: suggestionText
                },
                onSuccess: function (transport)
                {
                    translationGridJsObject.reload(translationGridJsObject.url);
                    $('loading-mask').hide();
                }
            });
    },

    editSuggestion: function(element)
    {
        var self = TranslationHandlerObj;

        var id = element.id;
        var textId = id.replace('edit_link_', '');
        var defaultText = element.up().previous().childElements()[0].innerHTML;
        defaultText = decodeHtmlentities(defaultText);

        self.newSuggestionVisibility($('new_suggestion_link_' + textId), defaultText);
    },

    filter_change: function(filter)
    {
        var filterType = filter.id.replace('_filter', '');
        var filterValue = filter.value;
        var hrefString = window.location.href;
        var hrefParts = explode('/', hrefString);
        var isSetFilter = false;

        for (var i = 0; i < hrefParts.length; i++) {
            if (hrefParts[i] != filterType) {
                continue;
            }

            if (filterValue == '-1') {
                hrefParts.splice(i, 2);
            } else {
                hrefParts[i+1] = filterValue;
            }
            hrefString = implode('/', hrefParts);
            isSetFilter = true;
            break;
        }

        if (!isSetFilter && filterValue != '-1') {
            hrefString += filterType + '/' + filterValue + '/';
        }

        window.location.assign(hrefString);
    },

    newSuggestionVisibility: function(element, defaultText)
    {
        var self = TranslationHandlerObj;

        var id = element.id;
        var textId = '';

        if (id.indexOf('new_suggestion') + 1) {
            textId = id.replace('new_suggestion_link_', '');

            if (defaultText != undefined) {
                $('new_custom_suggestion_' + textId).value = defaultText;
            } else {
                $('new_custom_suggestion_' + textId).value = '';
            }

            $('major_container' + textId).style.marginTop = '-5px';

            $('new_suggestion_link_' + textId).hide();
            $('new_custom_suggestion_' + textId).show();
            $('save_link_' + textId).show();
            $('discard_link_' + textId).show();
            $('separator_' + textId).show();
            $('suggestions_list_' + textId).hide();
            $('hide_more_' + textId).hide();
            self.suggestionsVisibility($('hide_more_' + textId));
            $('show_more_' + textId).hide();
            $('new_suggestion_container_' + textId).show();
        } else {
            textId = id.replace('discard_link_', '');

            var countSuggestions = 0;
            $$('.suggestions_' + textId).each(function(el) {
                countSuggestions++;
            });

            $('major_container' + textId).style.marginTop = '5px';
            $('suggestions_list_' + textId).show();

            if (countSuggestions <= 0 && $$('.custom_suggestion_' + textId).length <= 0) {
                $('suggestions_list_' + textId).hide();
            }

            if (countSuggestions <= 1 && $$('.custom_suggestion_' + textId).length <= 0) {
                $('new_suggestion_link_' + textId).show();
            } else {
                $('new_suggestion_link_' + textId).hide();
            }

            if ((countSuggestions + $$('.custom_suggestion_' + textId).length) > 1) {
                $('show_more_' + textId).show();
            }

            $('save_link_' + textId).hide();
            $('discard_link_' + textId).hide();
            $('separator_' + textId).hide();
            $('new_custom_suggestion_' + textId).hide();
            $('new_suggestion_container_' + textId).hide();
        }
    },

    suggestionsVisibility: function(element)
    {
        var id = element.id;
        var textId = '';

        if (id.indexOf('show_more_') + 1) {
            textId = id.replace('show_more_', '');

            $$('.suggestions_' + textId).each(function(el) {
                el.childElements()[0].style.background = '#EDEEF3';
                el.childElements()[1].style.background = '#EDEEF3';

                el.childElements()[0].style.paddingLeft = '10px';
            });

            if ($$('.custom_suggestion_' + textId).length <= 0) {
                $$('.suggestions_' + textId)[0].childElements()[0].style.background = '';
                $$('.suggestions_' + textId)[0].childElements()[1].style.background = '';
                $$('.suggestions_' + textId)[0].childElements()[0].style.paddingLeft = '';
            }

            $$('.suggestions_' + textId).invoke('show');
            $('show_more_' + textId).hide();
            $('hide_more_' + textId).show();
            $('separator_hide_' + textId).show();
            $('new_suggestion_link_' + textId).show();
        } else {
            textId = id.replace('hide_more_', '');

            $$('.suggestions_' + textId).invoke('hide');
            $('new_suggestion_link_' + textId).hide();

            if ($$('.custom_suggestion_' + textId).length <= 0 && $$('.suggestions_' + textId).length > 0) {
                $$('.suggestions_' + textId)[0].show();
            }

            $('hide_more_' + textId).hide();
            $('show_more_' + textId).show();
            $('separator_hide_' + textId).hide();
        }
    }
});