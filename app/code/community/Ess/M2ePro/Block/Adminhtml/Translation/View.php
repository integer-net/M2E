<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Translation_View extends Mage_Adminhtml_Block_Widget_View_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('translationView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_translation';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Translation');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    protected function _toHtml()
    {
        if (is_null($this->getData('active_language'))) {
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'CommonHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $emptyBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_translation_view_emptyLanguage');
            return parent::_toHtml() . $emptyBlock->toHtml();
        }

        $languagesCollection = Mage::getModel('M2ePro/Translation_Language')->getCollection();
        $languagesCollection->addFieldToFilter('code', $this->getData('active_language'));
        $languageId = $languagesCollection->getFirstItem()->getId();

        $textsCollection = Mage::getModel('M2ePro/Translation_Text')->getCollection();
        $textsCollection->addFieldToFilter('language_id', $languageId);
        if ($textsCollection->getSize() <= 0) {
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'CommonHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $emptyBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_translation_view_emptyText');
            return parent::_toHtml() . $emptyBlock->toHtml();
        }

        $addSuggestionUrl = $this->getUrl("*/adminhtml_translation/addSuggestion");
        $removeSuggestionUrl = $this->getUrl("*/adminhtml_translation/removeSuggestion");

        $emptyNewSuggestionMessage = Mage::helper('M2ePro')->__('Please, enter new suggestion text.');

        $confirmMessage = Mage::helper('M2ePro')->__('Are you sure?');

        $javascriptMain =<<<JAVASCRIPT

<script type="text/javascript">

    M2ePro = {};
    M2ePro.url = {};
    M2ePro.formData = {};
    M2ePro.customData = {};
    M2ePro.text = {};

    M2ePro.url.addSuggestion = '{$addSuggestionUrl}';
    M2ePro.url.removeSuggestion = '{$removeSuggestionUrl}';

    M2ePro.text.empty_new_suggestion = '{$emptyNewSuggestionMessage}';
    M2ePro.text.confirm_message = '{$confirmMessage}';

    Event.observe(window, 'load', function() {
            TranslationHandlerObj = new TranslationHandler(M2ePro);
    });

</script>

JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_translation_view_help');
        $filterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_translation_view_filter');
        $gridBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_translation_view_grid',
            '',
            array('active_language' => $this->getData('active_language'))
        );

        return parent::_toHtml() . $javascriptMain . $helpBlock->toHtml() .
               $filterBlock->toHtml() . $gridBlock->toHtml();

    }
}