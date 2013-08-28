<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Translation_View_EmptyText extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('translationViewEmptyText');
        //------------------------------

        $this->setTemplate('M2ePro/translation/view/empty_text.phtml');
    }
}