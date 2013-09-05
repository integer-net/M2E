<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Module_ModuleController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Run Cron"
     * @description "Emulate starting cron"
     */
    public function runCronAction()
    {
        Mage::getModel('M2ePro/Cron')->process();
    }

    /**
     * @title "Run Processing Cron"
     * @description "Run Processing Cron"
     * @new_line
     */
    public function cronProcessingTemporaryAction()
    {
        Mage::getModel('M2ePro/Processing_Cron')->process();
    }

    //#############################################

    /**
     * @title "Update License"
     * @description "Send update license request to server"
     */
    public function licenseUpdateAction()
    {
        Mage::getModel('M2ePro/Servicing_Dispatcher')->processTasks(array(
            Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
        ));

        $this->_getSession()->addSuccess('License status was successfully updated.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //#############################################
}