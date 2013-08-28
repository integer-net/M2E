<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Model_Translation_CustomSuggestion extends Ess_M2ePro_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Translation_CustomSuggestion');
    }

    // ########################################

    public function getOriginalText()
    {
        return $this->getData('original_text');
    }

    public function getLanguageCode()
    {
        return $this->getData('language_code');
    }

    public function getCustomText()
    {
        return $this->getData('custom_text');
    }

    // ########################################
}