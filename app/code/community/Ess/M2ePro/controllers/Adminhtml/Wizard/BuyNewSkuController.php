<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_BuyNewSkuController extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getNick()
    {
        return 'buyNewSku';
    }

    //#############################################

    public function completeAction()
    {
        parent::completeAction();
        return $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################
}