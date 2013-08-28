<?php

    /*
    * @copyright  Copyright (c) 2011 by  ESS-UA.
    */

class Ess_M2ePro_Adminhtml_Wizard_MainController extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getNick()
    {
        return 'main';
    }

    //#############################################

    public function welcomeAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        if (!$wizardHelper->isInstallationNotStarted()) {
            return $this->_redirect('*/*/index');
        }

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('welcome',$this->getNick()))
            ->renderLayout();
    }

    //#############################################
}