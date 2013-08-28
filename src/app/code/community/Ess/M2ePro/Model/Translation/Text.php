<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Model_Translation_Text extends Ess_M2ePro_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Translation_Text');
    }

    // ########################################

    public function getOriginalText()
    {
        return $this->getData('original_text');
    }

    public function getLanguage()
    {
        $languageId = (int)$this->getData('language_id');
        return Mage::getModel('M2ePro/Translation_Language')->loadInstance($languageId);
    }

    public function getSuggestions()
    {
        return (array)$this->getSettings('suggestions');
    }

    public function getGroup()
    {
        return $this->getData('group');
    }

    public function getCustomSuggestion()
    {
        $customSuggestionsCollection = Mage::getModel('M2ePro/Translation_CustomSuggestion')->getCollection();
        $customSuggestionsCollection->addFieldToFilter('language_code', $this->getLanguage()->getCode())
                                    ->addFieldToFilter('original_text', $this->getOriginalText());

        if ($customSuggestionsCollection->getSize() <= 0) {
            return NULL;
        }

        return $customSuggestionsCollection->getFirstItem();
    }

    public function addSuggestion($text, $trustCount = 0)
    {
        $suggestions = $this->getSuggestions();
        $issetFlag = false;
        foreach  ($suggestions as $suggestion) {
            if ($suggestion['text'] != $text) {
                continue;
            }

            $issetFlag = true;
            break;
        }

        if (!$issetFlag) {
            $suggestions[] = array(
                'text' => $text,
                'trust_count' => $trustCount
            );

            $this->setSettings('suggestions', $suggestions)->save();
        }
    }

    // ########################################

    public function runSynchronization($languageId)
    {
        $languageInstance = Mage::getModel('M2ePro/Translation_Language')->loadInstance($languageId);

        if (!$languageInstance->isNeedSynchronization()) {
            return;
        }

        $commandInput = array(
            'language_code' => $languageInstance->getCode()
        );

        try {
            $texts = Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
                                            ->processVirtual('translation','get','textsByLanguage', $commandInput);
        } catch (Exception $exception) {
            return;
        }

        if (!is_array($texts)) {
            return;
        }

        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');
        $textTable  = $coreRes->getTableName('M2ePro/Translation_Text');

        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $textsForInsert = array();
        foreach ($texts['texts'] as $text) {
            $textsForInsert[] = array(
                'language_id' => (int)$languageId,
                'group' => $text['group'],
                'original_text' => $text['original_text'],
                'suggestions' => count($text['suggestions']) > 0 ? json_encode($text['suggestions']) : NULL,
                'update_date' => $currentDate,
                'create_date' => $currentDate
            );
        }

        if (count($textsForInsert) > 0) {
            $connWrite->insertMultiple($textTable, $textsForInsert);
        }
        $languageInstance->setData('need_synch', 0)->save();
    }
}