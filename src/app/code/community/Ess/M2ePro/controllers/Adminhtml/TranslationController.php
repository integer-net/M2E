<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_TranslationController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('m2epro/help')
            ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
            ->_title(Mage::helper('M2ePro')->__('Help'))
            ->_title(Mage::helper('M2ePro')->__('Translation'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/TranslationHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/help/translation');
    }

    //#############################################

    public function indexAction()
    {
        Mage::getModel('M2ePro/Translation_Language')->runSynchronization();

        $languagesCollection = Mage::getModel('M2ePro/Translation_Language')->getCollection();
        if ($languagesCollection->getSize() <= 0) {
            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_translation_view'))
                ->renderLayout();

            return;
        }

        $selectedLanguage = $this->getActiveLanguage();

        Mage::getModel('M2ePro/Translation_Text')->runSynchronization($selectedLanguage->getId());

        $blockParams = array(
            'active_language' => $selectedLanguage->getCode()
        );
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_translation_view', '', $blockParams))
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_translation_tabs', '', $blockParams))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $blockParams = array(
            'active_language' => $this->getActiveLanguage()->getCode()
        );
        $block = $this->loadLayout()
                      ->getLayout()
                      ->createBlock('M2ePro/adminhtml_translation_view_grid', '', $blockParams);
        $this->getResponse()->setBody($block->toHtml());
    }

    protected function getActiveLanguage()
    {
        $languageCode = $this->getRequest()->getParam('language');
        if (is_null($languageCode)) {
            $languageCode = Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/cache/translation/','language');
            if (is_null($languageCode)) {
                $languageCode = strtoupper(Mage::helper('M2ePro/Magento')->getLocale());
            }
        }

        $selectedLanguageCollection = Mage::getModel('M2ePro/Translation_Language')->getCollection()
                                                                         ->addFieldToFilter('code', $languageCode);
        if ($selectedLanguageCollection->getSize() <= 0) {
            $selectedLanguageCollection = Mage::getModel('M2ePro/Translation_Language')->getCollection();
        }

        $selectedLanguage = $selectedLanguageCollection->getFirstItem();

        Mage::helper('M2ePro/Module')->getConfig()
                                     ->setGroupValue('/cache/translation/','language', $selectedLanguage->getCode());

        return $selectedLanguage;
    }

    //#############################################

    public function addSuggestionAction()
    {
        $textId = (int)$this->getRequest()->getParam('text_id');
        $suggestionText = $this->getRequest()->getParam('suggestion_text');

        $textModel = Mage::getModel('M2ePro/Translation_Text')->loadInstance($textId);

        $commandData = array(
            'language_code' => $textModel->getLanguage()->getCode(),
            'original_text' => $textModel->getOriginalText(),
            'suggestion_text' => $suggestionText
        );

        try {
            Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
                                                    ->processVirtual('translation','add','suggestion', $commandData);
        } catch (Exception $e) {
            exit('fail');
        }

        $textModel->addSuggestion($suggestionText);

        $dataForInsert = array(
            'language_code' => $textModel->getLanguage()->getCode(),
            'original_text' => $textModel->getOriginalText(),
            'custom_text' => $suggestionText
        );
        Mage::getModel('M2ePro/Translation_CustomSuggestion')->setData($dataForInsert)->save();

        exit('success');
    }

    public function removeSuggestionAction()
    {
        $textId = (int)$this->getRequest()->getParam('text_id');
        $textModel = Mage::getModel('M2ePro/Translation_Text')->loadInstance($textId);

        $suggestionsCollection = Mage::getModel('M2ePro/Translation_CustomSuggestion')->getCollection();
        $suggestionsCollection->addFieldToFilter('language_code', $textModel->getLanguage()->getCode());
        $suggestionsCollection->addFieldToFilter('original_text', $textModel->getOriginalText());

        if ($suggestionsCollection->getSize() <= 0) {
            exit();
        }

        $suggestionText = $suggestionsCollection->getFirstItem()->getCustomText();
        $suggestionsCollection->getFirstItem()->deleteInstance();

        $commandData = array(
            'language_code' => $textModel->getLanguage()->getCode(),
            'original_text' => $textModel->getOriginalText(),
            'suggestion_text' => $suggestionText
        );

        try {
            Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
                                                ->processVirtual('translation','delete','trustCount', $commandData);
        } catch (Exception $e) {}

        exit();
    }

    //#############################################
}