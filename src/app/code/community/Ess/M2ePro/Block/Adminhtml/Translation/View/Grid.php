<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Translation_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('translationGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $languageCode = $this->getData('active_language');
        $languagesCollection = Mage::getModel('M2ePro/Translation_Language')->getCollection();
        $languagesCollection->addFieldToFilter('code', $languageCode);
        $languageObject = $languagesCollection->getFirstItem();

        $textsCollection = Mage::getModel('M2ePro/Translation_Text')->getCollection();
        $textsCollection->getSelect()->where("`main_table`.`language_id` = ?", (int)$languageObject->getId());

        $customSuggestionSelect = Mage::getModel('M2ePro/Translation_CustomSuggestion')
            ->getCollection()
            ->getSelect()
            ->where("`language_code` = ?", $languageCode);

        $groupFilter = $this->getRequest()->getParam('group');
        $translationFilter = $this->getRequest()->getParam('status');

        if (!is_null($translationFilter)) {
            switch ($translationFilter) {
                case 'translated':
                    $textsCollection->getSelect()
                                    ->where("`main_table`.`suggestions` IS NOT NULL OR `mtcs`.`id` IS NOT NULL");
                    break;

                case 'untranslated':
                    $textsCollection->getSelect()->where("`main_table`.`suggestions` IS NULL");
                    $textsCollection->getSelect()->where("`mtcs`.`id` IS NULL");
                    break;
            }
        }

        if (!is_null($groupFilter)) {
            $textsCollection->getSelect()->where("`main_table`.`group` = ?", $groupFilter);
        }

        $textsCollection->getSelect()->joinLeft(
            array('mtcs' => $customSuggestionSelect),
            '(`mtcs`.`original_text` = `main_table`.`original_text`)',
            array('custom_suggestion_text'=>'custom_text', 'custom_suggestion_id'=>'id')
        );

        // Set collection to grid
        $this->setCollection($textsCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('original_text', array(
            'header'    => Mage::helper('M2ePro')->__('Original Text'),
            'align'     => 'left',
            'width'     => '350px',
            'padding'   => '10px',
            'type'      => '',
            'index'     => 'original_text',
            'filter_index' => 'main_table.original_text',
            'frame_callback' => array($this, 'callbackColumnOriginalText'),
        ));

        $this->addColumn('suggestions', array(
            'header'    => Mage::helper('M2ePro')->__('Translation'),
            'align'     => 'left',
            //'width'     => '350px',
            'type'      => '',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnSuggestions')
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _toHtml()
    {
        $css ='
<style type="text/css">
.grid tr.even, .grid tr.even tr
{
    background: transparent;
}
</style>';

        return $css . parent::_toHtml();

    }

    public function callbackColumnOriginalText($value, $row, $column, $isExport)
    {
        $html = '<div style="padding: 9px;">'.$value.'</div>';
        return $html;
    }

    public function callbackColumnSuggestions($value, $row, $column, $isExport)
    {
        $textId = $row->getData('id');
        $suggestions = (array)json_decode($row->getData('suggestions'), true);
        $suggestionsCount = 0;
        foreach ($suggestions as $suggestion) {
            if ($suggestion['text'] == $row->getData('custom_suggestion_text')) {
                continue;
            }

            $suggestionsCount++;
        }
        if (is_null($row->getData('custom_suggestion_id')) && $suggestionsCount > 0) {
            $suggestionsCount--;
        }

        $html = '<div id="major_container'.$textId.'" style="padding: 0; margin-top: 5px; margin-bottom: 5px;">';

        $html .= '<table id="suggestions_list_' . $textId . '" style="border: none;';
        if (is_null($row->getData('custom_suggestion_id')) && count($suggestions) <= 0) {
            $html .= ' display: none;';
        }
        $html .= '">' . $this->getSuggestionsRows($row) . '</table>';

        $html .= '&nbsp;&nbsp;';

        $html .= '<a href="javascript:void(0)" id="show_more_'.$textId.'"
                     onclick="TranslationHandlerObj.suggestionsVisibility(this)"';
        if (count($suggestions) <= 1) {
            $html .= ' style="display: none;"';
        }
        $html .= '>' . Mage::helper('M2ePro')->__('Show More Translations') .'[' . $suggestionsCount . ']</a>';

        $html .= '<a href="javascript:void(0)" id="hide_more_'.$textId.'"
                     onclick="TranslationHandlerObj.suggestionsVisibility(this)"
                     style="display: none;">' . Mage::helper('M2ePro')->__('Hide Translations') . '</a>';

        $html .= '<span id="separator_hide_'.$textId.'" class="separator" style="display: none;">|</span>';

        $html .= '<a href="javascript:void(0)" id="new_suggestion_link_'. $textId .'"
                     onclick="TranslationHandlerObj.newSuggestionVisibility(this)"';
        if (!is_null($row->getData('custom_suggestion_id')) || count($suggestions) > 1) {
            $html .= ' style="display: none;"';
        }
        $html .= '>'. Mage::helper('M2ePro')->__('Add Translation') .'</a>';

        $html .= '<div id="new_suggestion_container_'.$textId.'"
                       style="width: 75%; text-align: right; display: none;">
                  <textarea cols="73" rows="5" id="new_custom_suggestion_' . $textId . '"
                            style="width: 99%; display: none;"></textarea><br />';
        $html .= '&nbsp;&nbsp;<a href="javascript:void(0)" id="save_link_'. $textId .'"
                     onclick="TranslationHandlerObj.confirmSuggestion(this)"
                     style="display: none;">'.Mage::helper('M2ePro')->__('Add').'</a>';
        $html .= '<span id="separator_'.$textId.'" class="separator" style="display: none;">|</span>';
        $html .= '<a href="javascript:void(0)" id="discard_link_'. $textId .'"
                     onclick="TranslationHandlerObj.newSuggestionVisibility(this)"
                     style="display: none;">'.Mage::helper('M2ePro')->__('Cancel').'</a>&nbsp;';
        $html .= '</div></div>';

        return $html;
    }

    protected function getSuggestionsRows($row)
    {
        $textId = $row->getData('id');
        $rowsHtml = '';
        $suggestions = (array)json_decode($row->getData('suggestions'), true);
        $styleDisplay = '';

        if (!is_null($row->getData('custom_suggestion_id'))) {
            $rowsHtml .= '<tr class="custom_suggestion_'.$textId.'">
                          <td style="border: none; padding: 5px; background-color: transparent;" width="75%">
                          <span style="font-weight: bold;" class="suggestion_text">'
                          .$row->getData('custom_suggestion_text').'</span><br />
                          <span style="font-size: 10px; color: grey;">Confirmed by me.</span></td>
                          <td style="border: none; padding: 5px; background-color: transparent;" width="25%">
                          <a href="javascript:void(0)" id="reset_link_'.$textId.'"
                             onclick="TranslationHandlerObj.resetSuggestion(this)">'.
                          Mage::helper('M2ePro')->__('Cancel Confirmation') . '</a><br />
                          <a href="javascript:void(0)" id="edit_link_'.$textId.'"
                             onclick="TranslationHandlerObj.editSuggestion(this)">'.
                          Mage::helper('M2ePro')->__('Edit Translation').'</a>
                          </td></tr>';
            $styleDisplay = ' style="display: none;"';
        }

        if (count($suggestions) <= 0) {
            return $rowsHtml;
        }

        foreach ($suggestions as $suggestion) {
            if ($suggestion['text'] == $row->getData('custom_suggestion_text')) {
                continue;
            }

            $rowsHtml .= '<tr class="suggestions_'.$textId.'" '.$styleDisplay.'">
                          <td style="border: none; padding: 5px;" width="75%"><span class="suggestion_text">'
                          .$suggestion['text'].'</span><br />
                          <span style="font-size: 10px; color: grey;">Confirmed: '.$suggestion['trust_count'].'</span>
                          </td><td style="border: none; padding: 5px;" width="25%">
                          <a href="javascript:void(0)" id="confirm_link_'.$textId.'"
                             onclick="TranslationHandlerObj.confirmSuggestion(this)">'.
                          Mage::helper('M2ePro')->__('Confirm') . '</a><br />
                          <a href="javascript:void(0)" id="edit_link_'.$textId.'"
                             onclick="TranslationHandlerObj.editSuggestion(this)">'.
                          Mage::helper('M2ePro')->__('Edit Translation').'</a></td></tr>';
            $styleDisplay = ' style="display: none;"';
        }

        return $rowsHtml;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_translation/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}