<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Cron
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionCron');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/cron.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->cronLastRunTime = 'N/A';
        $this->cronIsNotWorking = false;
        $this->cronCurrentType = ucfirst(Mage::helper('M2ePro/Module_Cron')->getType());

        $cronLastRunTime = Mage::helper('M2ePro/Module_Cron')->getLastRun();

        if (!is_null($cronLastRunTime)) {
            $this->cronLastRunTime = $cronLastRunTime;
            $this->cronIsNotWorking = Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(12,true);
        }

        return parent::_beforeToHtml();
    }

    // ########################################
}