SupportHandler = Class.create();
SupportHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        var cmdKeys = [67, 77, 68];
        var developmentKeys = [68, 69, 86];

        var cmdPressedKeys = [];
        var developmentPressedKeys = [];

        document.observe('keyup', function (event) {

            if (cmdPressedKeys.length < cmdKeys.length) {
                if (cmdKeys[cmdPressedKeys.length] == event.keyCode) {
                    cmdPressedKeys.push(event.keyCode);
                } else {
                    cmdPressedKeys = [];
                }
            }
            if (developmentPressedKeys.length < developmentKeys.length) {
                if (developmentKeys[developmentPressedKeys.length] == event.keyCode) {
                    developmentPressedKeys.push(event.keyCode);
                } else {
                    developmentPressedKeys = [];
                }
            }

            if (cmdPressedKeys.length == cmdKeys.length ||
                developmentPressedKeys.length == developmentKeys.length) {

                var queryInput = $('query');
                if (queryInput !== null) {
                    queryInput.value = '';
                    queryInput.focus();
                } else {
                    $('development_button_container').show();
                }

                $$('.development')[0].show();

                if (cmdPressedKeys.length == cmdKeys.length) {
                    $$('.development')[0].simulate('click');
                }

                cmdPressedKeys = [];
                developmentPressedKeys = [];
            }

        });
    },

    //----------------------------------

    searchUserVoiceData: function()
    {
        var self = SupportHandlerObj;
        var query = $('query').value;

        if (query === '') {
            return;
        }

        new Ajax.Request( M2ePro.url.get('adminhtml_support/getResultsHtml') ,
        {
            method: 'post',
            parameters: {
                query: query
            },
            asynchronous: true,
            onSuccess: function(transport)
            {
                $('support_results').style.cssText = '';
                $('support_results_content').innerHTML = transport.responseText;
                $('support_results').simulate('click');
                $('support_other_container').show();
            }
        });
    },

    keyPressQuery: function(event)
    {
        var self = SupportHandlerObj;

        if (event.keyCode == 13) {
            self.searchUserVoiceData();
        }
    },

    //----------------------------------

    toggleArticle: function(answerId)
    {
        var answerBlock = $('article_answer_' + answerId);

        if (!answerBlock.visible()) {
            $('article_meta_' + answerId).hide();
            Effect.Appear(answerBlock,{duration:0.5});
        } else {
            Effect.Fade(answerBlock,{duration:0.3});
            $('article_meta_' + answerId).show();
        }
    },

    toggleSuggestion: function(suggestionId)
    {
        var suggestionBlock = $('suggestion_text_' + suggestionId);

        if (!suggestionBlock.visible()) {
            $('suggestion_meta_' + suggestionId).hide();
            Effect.Appear(suggestionBlock,{duration:0.5});
        } else {
            Effect.Fade(suggestionBlock,{duration:0.3});
            $('suggestion_meta_' + suggestionId).show();
        }
    },

    toggleMoreButton: function()
    {
        if ($('more_button_container').visible()) {
            $('more_button_container').hide();
        } else {
            $('more_button_container').show();
        }
    },

    //----------------------------------

    moreAttachments: function()
    {
        var self = SupportHandlerObj;
        var emptyField = false;

        $$('#more input').each(function(obj) {
            if (obj.value == '') {
                emptyField = true;
            }
        });

        if (emptyField) {
            return;
        }
        $('more').insert('<input type="file" name="files[]" onchange="SupportHandlerObj.toggleMoreButton()" /><br />');
        self.toggleMoreButton();
    },

    //----------------------------------

    setTabActive: function(tabId)
    {
        $(tabId).simulate('click');
    },

    //----------------------------------

    goToArticle: function(url)
    {
        var self = SupportHandlerObj;
        var urlParam = base64_encode(url);

        $('support_articles').href += 'url/' + urlParam + '/';
        $('support_articles_content').innerHTML = '';

        self.setTabActive('support_articles');
        self.scroll_page_to_top();
    }

    //----------------------------------
});