<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_IndexController extends Mage_Core_Controller_Front_Action
{
    //#############################################

    public function indexAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        if (!$wizardHelper->isInstallationFinished()) {
            $wizardEdition  = $wizardHelper->getEdition();
            $installatorNick = $wizardHelper->getNick($wizardHelper->getInstallatorWizard());

            $this->_redirect('*/adminhtml_'.$wizardEdition.'_'.$installatorNick.'/index');
        } else {
            $this->_redirect('*/adminhtml_about/index');
        }
    }

    //#############################################
}