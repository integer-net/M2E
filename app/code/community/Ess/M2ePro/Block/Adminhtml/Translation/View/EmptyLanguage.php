<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Translation_View_EmptyLanguage extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('translationViewEmptyLanguage');
        //------------------------------

        $this->setTemplate('M2ePro/translation/view/empty_language.phtml');
    }
}