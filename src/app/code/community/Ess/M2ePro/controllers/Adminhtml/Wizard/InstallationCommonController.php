<?php

    /*
    * @copyright  Copyright (c) 2013 by  ESS-UA.
    */

class Ess_M2ePro_Adminhtml_Wizard_InstallationCommonController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //#############################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Common::WIZARD_INSTALLATION_NICK;
    }

    //#############################################

    public function welcomeAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/index');
        }

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('welcome',$this->getNick()))
            ->renderLayout();
    }

    //#############################################

    public function licenseAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive($this->getNick()) ||
            $wizardHelper->getStep($this->getNick()) != 'license') {
            return $this->_redirect('*/*/index');
        }

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('installation_license_container',$this->getNick()))
            ->renderLayout();
    }

    public function settingsAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive($this->getNick()) ||
            $wizardHelper->getStep($this->getNick()) != 'settings') {
            return $this->_redirect('*/*/index');
        }

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('installation_settings_container',$this->getNick()))
            ->renderLayout();
    }

    //#############################################

    public function createLicenseAction()
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        if ($licenseHelper->getKey()) {
            return $this->getResponse()->setBody(json_encode(array('result' => true)));
        }

        $keys = array(
            'email',
            'firstname',
            'lastname',
            'country',
            'city',
            'postal_code',
        );

        $post = $this->getRequest()->getPost();
        foreach ($keys as $key) {
            (!isset($post[$key]) || !$post[$key]) && $post[$key] = 'undefined';
        }

        $licenseResult = $licenseHelper->obtainRecord($post['email'],
                                                      $post['firstname'],$post['lastname'],
                                                      $post['country'],$post['city'],$post['postal_code']
        );

        return $this->getResponse()->setBody(json_encode(array('result' => $licenseResult)));
    }

    //#############################################
}