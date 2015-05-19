<?php

    /*
    * @copyright  Copyright (c) 2013 by  ESS-UA.
    */

class Ess_M2ePro_Adminhtml_Wizard_InstallationCommonController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //#############################################

    protected function _initAction()
    {
        $result = parent::_initAction();

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Wizard/InstallationCommon.js')
             ->addJs('M2ePro/Configuration/ComponentsHandler.js');

        return $result;
    }

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Common::WIZARD_INSTALLATION_NICK;
    }

    //#############################################

    public function congratulationAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isFinished($this->getNick())) {
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        if ($nextWizard = $wizardHelper->getActiveWizard($this->getCustomViewNick())) {
            return $this->_redirect('*/adminhtml_wizard_'.$wizardHelper->getNick($nextWizard));
        }

        $this->_initAction();
        $this->_addContent($wizardHelper->createBlock('congratulation',$this->getNick()));
        $this->renderLayout();
    }

    //#############################################

    public function createLicenseAction()
    {
        $keys = array(
            'email',
            'firstname',
            'lastname',
            'country',
            'city',
            'postal_code',
        );

        $post = $this->getRequest()->getPost();
        unset($post['form_key']);
        foreach ($keys as $key) {
            (!isset($post[$key]) || !$post[$key]) && $post[$key] = 'undefined';
        }

        $registry = Mage::getModel('M2ePro/Registry')->load('wizard_license_form_data', 'key');
        $registry->setData('key', 'wizard_license_form_data');
        $registry->setData('value', json_encode($post));
        $registry->save();

        if (Mage::helper('M2ePro/Module_License')->getKey()) {
            return $this->getResponse()->setBody(json_encode(array('result' => true)));
        }

        $licenseResult = Mage::helper('M2ePro/Module_License')->obtainRecord(
            $post['email'],
            $post['firstname'], $post['lastname'],
            $post['country'], $post['city'], $post['postal_code']
        );

        return $this->getResponse()->setBody(json_encode(array('result' => $licenseResult)));
    }

    //#############################################
}