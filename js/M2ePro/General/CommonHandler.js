CommonHandler = Class.create();
CommonHandler.prototype = {

    // --------------------------------

    initialize : function() {},

    //----------------------------------

    initCommonValidators: function()
    {
        Validation.add('M2ePro-required-when-visible', REQUIRED_FIELD_ERROR, function(value, el) {
            var hidden = false;
            hidden = !$(el).visible();

            while (!hidden) {
                el = $(el).up();
                hidden = !el.visible();
                if (el == document || el.hasClassName('entry-edit')) {
                    break;
                }
            }

            return hidden ? true : !!value;
        });

        Validation.add('M2ePro-required-when-visible-and-enabled', REQUIRED_FIELD_ERROR, function(value, el) {
            var hidden = false;
            var disabled = false;
            hidden = !$(el).visible();
            disabled = !$(el).disabled;

            while (!hidden) {
                el = $(el).up();
                hidden = !el.visible();
                if (el == document || el.hasClassName('entry-edit')) {
                    break;
                }
            }

            if (disabled) {
                return true;
            }

            return hidden ? true : !!value;
        });
    },

    //----------------------------------

    scroll_page_to_top: function()
    {
        if (location.href[location.href.length-1] != '#') {
            setLocation(location.href+'#');
        } else {
            setLocation(location.href);
        }
    },

    back_click: function(url)
    {
        setLocation(url.replace(/#$/, ''));
    },

    reset_click: function()
    {
        var url = window.location.href;
        setLocation(url.replace(/#$/, ''));
    },

    //----------------------------------

    save_click: function(url)
    {
        if (typeof url == 'undefined' || url == '') {
            url = M2ePro.url.formSubmit + 'back/'+base64_encode('list')+'/';
        }
        this.submitForm(url);
    },

    save_and_edit_click: function(url, tabsId)
    {
        if (typeof url == 'undefined' || url == '') {

            var tabsUrl = '';
            if (typeof tabsId != 'undefined') {
                tabsUrl = '|tab=' + $$('#' + tabsId + ' a.active')[0].name;
            }

            url = M2ePro.url.formSubmit + 'back/'+base64_encode('edit'+tabsUrl) + '/';
        }
        this.submitForm(url);
    },

    //----------------------------------

    duplicate_click: function($headId)
    {
        $('loading-mask').show();
        M2ePro.url.formSubmit = M2ePro.url.formSubmitNew;
        M2ePro.formData.id = 0;

        $('title').value = '';

        $$('.head-adminhtml-'+$headId).each(function(o) { o.innerHTML = M2ePro.text.chapter_when_duplicate; });
        $$('.M2ePro_duplicate_button').each(function(o) { o.hide(); });
        $$('.M2ePro_delete_button').each(function(o) { o.hide(); });

        window.setTimeout(function() {
            $('loading-mask').hide()
        }, 1200);
    },

    delete_click: function()
    {
        if (!confirm(CONFIRM)) {
            return;
        }
        setLocation(M2ePro.url.deleteAction);
    },

    //----------------------------------

    submitForm: function(url, newWindow)
    {
        if (typeof newWindow == 'undefined') {
            newWindow = false;
        }

        $('edit_form').action = url;
        $('edit_form').target = newWindow ? '_blank' : '_self';

        editForm.submit();
    },

    postForm: function(url, params)
    {
        var form = new Element('form', {'method': 'post', 'action': url});

        $H(params).each(function(i) {
            form.insert(new Element('input', {'name': i.key, 'value': i.value, 'type': 'hidden'}));
        });

        form.insert(new Element('input', {'name': 'form_key', 'value': FORM_KEY, 'type': 'hidden'}));

        $(document.body).insert(form);

        form.submit();
    },

    //----------------------------------

    openWindow: function(url)
    {
        var w = window.open(url);
        w.focus();
        return w;
    },

    //----------------------------------

    hideEmptyOption: function(select)
    {
        $(select).select('.empty') && $(select).select('.empty').length && $(select).select('.empty')[0].hide();
    },

    setRequiried: function(el)
    {
        $(el).addClassName('required-entry');
    },

    setNotRequiried: function(el)
    {
        $(el) && $(el).removeClassName('required-entry');
    },

    //----------------------------------

    setConstants: function(data)
    {
        data = eval(data);
        for (var i=0;i<data.length;i++) {
            eval('this.'+data[i][0]+'=\''+data[i][1]+'\'');
        }
    },

    setValidationCheckRepetitionValue: function(idInput, textError, model, dataField, idField, idValue)
    {
        Validation.add(idInput, textError, function(value) {

            var checkResult = false;

            new Ajax.Request( VALIDATION_CHECK_REPETITION_URL ,
            {
                method: 'post',
                asynchronous : false,
                parameters : {
                    model : model,
                    data_field : dataField,
                    data_value : value,
                    id_field : idField,
                    id_value : idValue
                },
                onSuccess: function (transport)
                {
                    checkResult = transport.responseText.evalJSON()['result'];
                }
            });

            return checkResult;

        });
    }

    //----------------------------------
}