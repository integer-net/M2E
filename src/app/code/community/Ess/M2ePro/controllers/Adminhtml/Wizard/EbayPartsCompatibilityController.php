<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_EbayPartsCompatibilityController
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getNick()
    {
        return 'ebayPartsCompatibility';
    }

    //#############################################

    public function completeAction()
    {
        parent::completeAction();
        return $this->_redirect('*/adminhtml_marketplace',array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
        ));
    }

    //#############################################

    public function skipAction()
    {
        parent::skipAction();
        return $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################
}