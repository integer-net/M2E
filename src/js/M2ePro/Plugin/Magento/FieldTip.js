MagentoFieldTip = Class.create();
MagentoFieldTip.prototype = {

    // --------------------------------

    initialize: function()
    {
        this.isHideToolTip = false;
    },

    // --------------------------------

    getHashedCookie: function(id)
    {
        var hashedCookieKey = 'm2e_bt_' + md5(id).substr(0, 10);
        var notHashedCookie = getCookie(id);
        var resultCookie = null;

        if (notHashedCookie !== "") {
            deleteCookie(id, '/', '');
            this.setHashedCookie(id);
            resultCookie = notHashedCookie;
        } else {
            resultCookie = getCookie(hashedCookieKey);
        }

        return resultCookie;
    },

    setHashedCookie: function(id)
    {
        var hashedCookieKey = 'm2e_bt_' + md5(id).substr(0, 10);
        setCookie(hashedCookieKey, 1, 3*365, '/');
    },

    deleteHashedCookie: function(id)
    {
        var hashedCookieKey = 'm2e_bt_' + md5(id).substr(0, 10);

        deleteCookie(hashedCookieKey, '/', '');
        deleteCookie(id, '/', '');
    },

    // --------------------------------

    showTipsForBlock: function(blockClass,init)
    {
        var self = MagentoFieldTipObj;

        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(object) {
            object.remove();
        });

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_tips_changer" style="float: right; color: white; font-size: 11px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoFieldTipObj.hideTipsForBlock(\''+blockClass+'\',\'0\');">'+M2ePro.translator.translate('Hide Tips')+'</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml + tempHtml2;

        this.deleteHashedCookie(blockClass);

        var noteClassName = 'note';
        if (IS_VIEW_EBAY || IS_VIEW_CONFIGURATION) {
            noteClassName = 'note-no-tool-tip';
        }
        $$('div.'+blockClass)[0].select('div.fieldset div.hor-scroll table.form-list tr td.value p.' + noteClassName).each(function(o) {

            if (init == '0') {
                o.show();
                //Effect.SlideDown(o,{duration:0.5});
            } else {
                o.show();
            }

            if ($$('div.'+blockClass+' div.fieldset')[0].getStyle('display') == 'none') {
                $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o2) {
                    o2.hide();
                });
            }

        });

        return true;
    },

    hideTipsForBlock: function(blockClass,init)
    {
        var self = MagentoFieldTipObj;

        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o) {
            o.remove();
        });

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_tips_changer" style="float: right; color: white; font-size: 11px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoFieldTipObj.showTipsForBlock(\''+blockClass+'\',\'0\');">'+M2ePro.translator.translate('Show Tips')+'</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml + tempHtml2;

        this.setHashedCookie(blockClass);

        var noteClassName = 'note';
        if (IS_VIEW_EBAY || IS_VIEW_CONFIGURATION) {
            noteClassName = 'note-no-tool-tip';
        }
        $$('div.'+blockClass)[0].select('div.fieldset div.hor-scroll table.form-list tr td.value p.' + noteClassName).each(function(o) {

            if (init == '0') {
                o.hide();
                //Effect.SlideUp(o,{duration:0.5});
            } else {
                o.hide();
            }

            if ($$('div.'+blockClass+' div.fieldset')[0].getStyle('display') == 'none') {
                $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o2) {
                    o2.hide();
                });
            }

        });

        return true;
    },

    // --------------------------------

    observePrepareStart: function(blockObj)
    {
        var self = this;

        var tempHideTips = blockObj.readAttribute('hidetips');
        if (typeof tempHideTips == 'string' && tempHideTips == 'no') {
            return;
        }

        var tempId = blockObj.readAttribute('id');
        if (typeof tempId != 'string') {
            tempId = 'magento_block_md5_' + md5(blockObj.innerHTML.replace(/[^A-Za-z]/g,''));
            blockObj.writeAttribute("id",tempId);
        }

        var blockClass = tempId + '_hide_tips';
        blockObj.addClassName(blockClass);

        var isClosed = this.getHashedCookie(blockClass);

        if (isClosed == '' || isClosed == '0') {
            self.showTipsForBlock(blockClass,'1');
        } else {
            self.hideTipsForBlock(blockClass,'1');
        }
    },

    // --------------------------------

    onToolTipMouseLeave: function()
    {
        var self = MagentoFieldTipObj;
        var element = this;

        self.isHideToolTip = true;

        setTimeout(function() {
            self.isHideToolTip && element.hide();
        }, 1000);
    },

    onToolTipMouseEnter: function()
    {
        var self = MagentoFieldTipObj;
        self.isHideToolTip = false;
    },

    onToolTipIconMouseLeave: function()
    {
        var self = MagentoFieldTipObj;
        var element = this.up().select('.tool-tip-message')[0];

        self.isHideToolTip = true;

        setTimeout(function() {
            self.isHideToolTip && element.hide();
        }, 1000);
    },

    // --------------------------------

    showToolTip: function()
    {
        var self = MagentoFieldTipObj;

        self.isHideToolTip = false;

        $$('.tool-tip-message').each(function(element) {
            element.hide();
        });

        if (this.up().select('.tool-tip-message').length > 0) {
            self.changeToolTipPosition(this);
            this.up().select('.tool-tip-message')[0].show();

            return;
        }

        var isShowLeft = false;
        if (this.up().previous('td').select('p.note')[0].hasClassName('show-left')) {
            isShowLeft = true;
        }

        var tipText = this.up().previous('td').select('p.note')[0].innerHTML;
        var tipWidth = this.up().previous('td').select('p.note')[0].getWidth();
        if (tipWidth > 500) {
            tipWidth = 500;
        }

        var additionalClassName = 'tip-right';
        if (isShowLeft) {
            additionalClassName = 'tip-left';
        }

        var toolTipSpan = new Element('span', {
            'class': 'tool-tip-message ' + additionalClassName
        }).update(tipText).hide();

        if (isShowLeft) {
            toolTipSpan.style.width = tipWidth + 'px';
        }

        var imgUrl = M2ePro.url.get('m2epro_skin_url') + '/images/help.png';
        var toolTipImg = new Element('img', {
            'src': imgUrl
        });

        toolTipSpan.insert({top: toolTipImg});
        this.insert({after: toolTipSpan});

        self.changeToolTipPosition(this);

        toolTipSpan.show();

        toolTipSpan.observe('mouseout', self.onToolTipMouseLeave);
        toolTipSpan.observe('mouseover', self.onToolTipMouseEnter);
    },

    // --------------------------------

    changeToolTipPosition: function(element)
    {
        var toolTip = element.up().select('.tool-tip-message')[0];

        var settings = {
            setHeight: false,
            setWidth: false,
            setLeft: true,
            offsetTop: 25,
            offsetLeft: 0
        };

        if (element.up().getStyle('float') == 'right') {
            settings.offsetLeft += 18;
        }
        if (element.up().match('span')) {
            settings.offsetLeft += 15;
        }

        toolTip.clonePosition(element, settings);

        if (toolTip.hasClassName('tip-left')) {
            toolTip.style.left = (parseInt(toolTip.style.left) - toolTip.getWidth() - 10) + 'px';
        }
    }

    // --------------------------------
}