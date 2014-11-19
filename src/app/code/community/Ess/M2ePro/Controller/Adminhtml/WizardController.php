<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    abstract protected function getNick();

    //#############################################

    protected function _initAction()
    {
        $this->loadLayout();

        // Popup
        //-------------
        $this->_initPopUp();
        //-------------

        Mage::helper('M2ePro/Module_Wizard')->addWizardHandlerJs();

        return $this;
    }

    //#############################################

    public function indexAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/welcome');
        }

        if ($wizardHelper->isActive($this->getNick())) {
            return $this->_redirect('*/*/installation');
        }

        $this->_redirect('*/*/congratulation',array('wizard'=>true));
    }

    //---------------------------------------------

    public function welcomeAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/index');
        }

        $wizardHelper->setStatus(
            $this->getNick(),
            Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE
        );
        $wizardHelper->setStep(
            $this->getNick(),
            $wizardHelper->getWizard($this->getNick())->getFirstStep()
        );

        $this->_redirect('*/*/index');
    }

    public function installationAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isFinished($this->getNick()) ||
            $wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/index');
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
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isFinished($this->getNick())) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_initAction();
        $this->_addContent($wizardHelper->createBlock('congratulation',$this->getNick()));
        $this->_addNextWizardPresentation();
        $this->renderLayout();
    }

    //#############################################

    public function skipAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        Mage::helper('M2ePro/Module_Wizard')->setStatus(
            $this->getNick(),
            Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED
        );

        $this->_redirect('*/*/index');
    }

    public function completeAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        Mage::helper('M2ePro/Module_Wizard')->setStatus(
            $this->getNick(),
            Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED
        );

        $this->_redirect('*/*/index');
    }

    //#############################################

    public function setStepAction()
    {
        $step = $this->getRequest()->getParam('step');

        if (is_null($step)) {
            exit(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Step is invalid')
            )));
        }

        Mage::helper('M2ePro/Module_Wizard')->setStep(
            $this->getNick(),$step
        );

        $this->getResponse()->setBody(json_encode(array(
            'type' => 'success'
        )));
    }

    public function setStatusAction()
    {
        $status = $this->getRequest()->getParam('status');

        if (is_null($status)) {
            exit(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Status is invalid')
            )));
        }

        Mage::helper('M2ePro/Module_Wizard')->setStatus(
            $this->getNick(),$status
        );

        $this->getResponse()->setBody(json_encode(array(
            'type' => 'success'
        )));
    }

    //#############################################

    protected function _addNextWizardPresentation()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $nextWizard = $wizardHelper->getActiveWizard($this->getCustomViewNick());
        if ($nextWizard) {
            $presentationBlock = $wizardHelper->createBlock('presentation',$wizardHelper->getNick($nextWizard));
            $presentationBlock && $this->_addContent($presentationBlock);
        }

        return $this;
    }

    //#############################################
}