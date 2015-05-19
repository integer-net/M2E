<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_DevelopmentController
    extends Ess_M2ePro_Controller_Adminhtml_Development_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
                ->getLayout()
                ->getBlock('head')
                ->addJs('M2ePro/Plugin/DropDown.js')
                ->addCss('M2ePro/css/Plugin/DropDown.css');

        return $this;
    }

    /**
     * @title "First Test"
     * @description "Command for quick development"
     */
    public function firstTestAction()
    {

    }

    /**
     * @title "Second Test"
     * @description "Command for quick development"
     */
    public function secondTestAction()
    {

    }

    /**
     * @title "Force run migration 6.3.0 [temporary]"
     * @description "Force run migration 6.3.0"
     */
    public function runMigrationForce630Action()
    {
        var_dump('start ...');

        try {

            /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630 $migrationInstance */
            $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630');
            $migrationInstance->setInstaller(new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup'));
            $migrationInstance->setForceAllSteps(true);
            $migrationInstance->migrate();

        } catch (Exception $e) {

            var_dump($e);
            die;
        }

        var_dump('success.');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_development'))
                ->renderLayout();
    }

    //#############################################

    public function summaryTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_summary')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    public function aboutTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_about')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    public function databaseTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    //#############################################

    public function enableMaintenanceModeAction()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            Mage::helper('M2ePro/Module_Maintenance')->enable();
        }

        $this->_getSession()->addSuccess('Maintenance was activated.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    public function disableMaintenanceModeAction()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            Mage::helper('M2ePro/Module_Maintenance')->disable();
            Mage::helper('M2ePro/Data_Session')->getValue('warning_message', true);
        }

        $this->_getSession()->addSuccess('Maintenance was deactivated.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    // --------------------------------------------

    public function enableDevelopmentModeAction()
    {
        Mage::helper('M2ePro/Module')->setDevelopmentModeMode(true);

        $this->_getSession()->addSuccess('Development mode has been Enabled.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    public function disableDevelopmentModeAction()
    {
        Mage::helper('M2ePro/Module')->setDevelopmentModeMode(false);

        $this->_getSession()->addSuccess('Development mode has been Disabled.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    //#############################################
}