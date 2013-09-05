<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_InstallationEbay extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'wizardTutorial',
        'license',
        'modeConfirmation',
        'account',

        'listingTutorial',
        'listingAccount',
        'listingGeneral',
        'listingSelling',
        'listingSynchronization',

        'productTutorial',
        'sourceMode',
        'productSelection',
        'productSettings',

        'categoryStepOne',
        'categoryStepTwo',
        'categoryStepThree',
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    // ########################################

    public function getPrevStep()
    {
        $currentStep = Mage::helper('M2ePro/Module_Wizard')->getStep(
            Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK
        );

        $prevStepIndex = array_search($currentStep,$this->steps) - 1;

        return isset($this->steps[$prevStepIndex]) ? $this->steps[$prevStepIndex] : false;
    }

    public function getNextStep()
    {
        $currentStep = Mage::helper('M2ePro/Module_Wizard')->getStep(
            Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK
        );

        $nextStepIndex = array_search($currentStep,$this->steps) + 1;

        return isset($this->steps[$nextStepIndex]) ? $this->steps[$nextStepIndex] : false;
    }

    // ########################################
}