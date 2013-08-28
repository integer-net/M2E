<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Wizard_Amazon_Presentation extends Ess_M2ePro_Block_Adminhtml_Wizard_Presentation
{
    // ########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $startWizardLink = $this->getUrl("*/adminhtml_wizard_amazon");

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => 'Proceed',
            'onclick' => 'setLocation(\''.$startWizardLink.'\')',
        ) );

        $this->setChild('continue_button',$buttonBlock);
    }

    // ########################################
}
