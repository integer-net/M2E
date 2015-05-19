<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_MigrationNewAmazonController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //#############################################

    protected function _initAction()
    {
        parent::_initAction();
        $this->getLayout()->getBlock('head')
                          ->addJs('M2ePro/Wizard/MigrationNewAmazonHandler.js');

        return $this;
    }

    //#############################################

    protected function getNick()
    {
        return 'migrationNewAmazon';
    }

    //#############################################

    public function welcomeAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');
        $wizardHelper->setStatus(
            $this->getNick(),
            Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE
        );

        return $this->_redirect('*/*/index');
    }

    public function installationAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isFinished($this->getNick())) {
            return $this->_redirect('*/*/congratulation');
        }

        if (!$wizardHelper->getStep($this->getNick())) {
            $wizardHelper->setStep(
                $this->getNick(),
                $wizardHelper->getWizard($this->getNick())->getFirstStep()
            );
        }

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('installation',$this->getNick()))
            ->renderLayout();
    }

    public function congratulationAction()
    {
        return $this->_redirect('*/adminhtml_common_listing/index/');
    }

    //#############################################

    public function marketplacesSynchronizationAction()
    {
        $marketplaceId = (int)$this->getRequest()->getParam('id');

        if (!$marketplaceId) {
            return $this->getResponse()->setBody('error');
        }

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(array(Ess_M2ePro_Helper_Component_Amazon::NICK));
        $dispatcher->setAllowedTasksTypes(array(Ess_M2ePro_Model_Synchronization_Task::MARKETPLACES));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array('marketplace_id' => $marketplaceId));

        $dispatcher->process();

        return $this->getResponse()->setBody('success');
    }

    //#############################################
}