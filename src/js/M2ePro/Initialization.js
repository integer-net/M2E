// Create main objects
// ----------------------------------
CommonHandlerObj = new CommonHandler();

MagentoMessageObj = new MagentoMessage();
MagentoBlockObj = new MagentoBlock();

ModuleNoticeObj = new BlockNotice('Module');
ServerNoticeObj = new BlockNotice('Server');

MagentoFieldTipObj = new MagentoFieldTip();
// ----------------------------------

function initializationMagentoBlocks()
{
    CommonHandlerObj.initCommonValidators();

    $$('.block_notices_module').each(function(blockObj) {
        ModuleNoticeObj.observeModulePrepareStart(blockObj);
    });

    $$('div.entry-edit').each(function(blockObj) {

        if (blockObj.select('div.entry-edit-head').length == 0) {
            return;
        }

        if (blockObj.readAttribute('magento_block') == 'no') {
            return;
        }

        if (blockObj.select('div.entry-edit-head .entry-edit-head-left').length > 0) {
            return;
        }

        blockObj.select('div.entry-edit-head')[0].innerHTML = '<div class="entry-edit-head-left" style="float: left; width: 78%;">' + blockObj.select('div.entry-edit-head')[0].innerHTML + '</div>' +
            '<div class="entry-edit-head-right" style="float: right; width: 20%;"></div>';
        MagentoBlockObj.observePrepareStart(blockObj);

        var noteClassName = 'note';
        if (IS_VIEW_EBAY || IS_VIEW_CONFIGURATION) {
            noteClassName = 'note-no-tool-tip';
        }

        if (blockObj.select('div.fieldset div.hor-scroll table.form-list tr td.value p.' + noteClassName).length > 0) {
            MagentoFieldTipObj.observePrepareStart(blockObj);
        }

        if (!IS_VIEW_EBAY && !IS_VIEW_CONFIGURATION) {
            return;
        }

        blockObj.select('p.note').each(function(noteElement) {

            if (noteElement.hasClassName('note-no-tool-tip') || noteElement.innerHTML.length <= 0) {
                return;
            }

            if (typeof noteElement.up().next() != "undefined" && noteElement.up().next() != null
                && noteElement.up().next().select('.tool-tip-image').length > 0) {

                return;
            }

            noteElement.hide();

            var toolTipContainer = new Element('td', {
                'class': 'value'
            });

            var imageUrl = M2ePro.url.get('m2epro_skin_url') + '/images/tool-tip-icon.png';
            var toolTipImg = new Element('img', {
                'class': 'tool-tip-image',
                'src': imageUrl
            });

            toolTipContainer.insert({top: toolTipImg});

            noteElement.up().insert({after: toolTipContainer});
        });

    });

    $$('.tool-tip-image').each(function(element) {
        element.observe('mouseover', MagentoFieldTipObj.showToolTip);
        element.observe('mouseout', MagentoFieldTipObj.onToolTipIconMouseLeave);
    });

    $$('.tool-tip-message').each(function(element) {
        element.observe('mouseout', MagentoFieldTipObj.onToolTipMouseLeave);
        element.observe('mouseover', MagentoFieldTipObj.onToolTipMouseEnter);
    });
}

// Set main observers
// ----------------------------------
Event.observe(window, 'load', function() {

    initializationMagentoBlocks();

    var ajaxHandler = {
        onComplete: function(transport) {
            if (Ajax.activeRequestCount == 0) {
                initializationMagentoBlocks();
            }
        }

    };

    Ajax.Responders.register(ajaxHandler);
});
// ----------------------------------

// ----------------------------------
(function(window) {

    var setLoc = setLocation;

    setLocation = function() {
        var args = arguments;
        setTimeout(function() {
            setLoc.apply(window,args);
        },200);
    };

})(window);
// ----------------------------------