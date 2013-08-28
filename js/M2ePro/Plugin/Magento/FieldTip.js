MagentoFieldTip = Class.create();
MagentoFieldTip.prototype = {

    // --------------------------------

    initialize: function() {},

    // --------------------------------

    showTipsForBlock: function(blockClass,init)
    {
        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(object) {
            object.remove();
        });

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_tips_changer" style="float: right; color: white; font-size: 11px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoFieldTipObj.hideTipsForBlock(\''+blockClass+'\',\'0\');">'+HIDE_TIPS+'</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml + tempHtml2;

        deleteCookie(blockClass, '/', '');

        $$('div.'+blockClass)[0].select('div.fieldset div.hor-scroll table.form-list tr td.value p.note').each(function(o) {

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
        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o) {
            o.remove();
        });

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_tips_changer" style="float: right; color: white; font-size: 11px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoFieldTipObj.showTipsForBlock(\''+blockClass+'\',\'0\');">'+SHOW_TIPS+'</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml + tempHtml2;

        setCookie( blockClass , 1 , 3*365 , '/' );

        $$('div.'+blockClass)[0].select('div.fieldset div.hor-scroll table.form-list tr td.value p.note').each(function(o) {

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

        var isClosed = getCookie(blockClass);

        if (isClosed == '' || isClosed == '0') {
            self.showTipsForBlock(blockClass,'1');
        } else {
            self.hideTipsForBlock(blockClass,'1');
        }
    }

    // --------------------------------
}