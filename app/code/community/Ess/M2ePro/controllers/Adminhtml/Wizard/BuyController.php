<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_BuyController extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getNick()
    {
        return 'buy';
    }

    //#############################################

    public function welcomeAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        if (!$wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/index');
        }

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('welcome',$this->getNick()))
            ->renderLayout();
    }

    public function installationAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/buy/', 'mode', 1
        );

        Mage::helper('M2ePro/Wizard')->clearMenuCache();

        parent::installationAction();
    }

    public function congratulationAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');
        $wizardHelper->getWizard($this->getNick())->disableChildWizards();

        parent::congratulationAction();
    }

    //#############################################
}
