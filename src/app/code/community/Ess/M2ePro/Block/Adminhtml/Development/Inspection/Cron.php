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
        $this->cronPhp = 'php -q '.
                         Mage::helper('M2ePro/Client')->getBaseDirectory() .
                         DIRECTORY_SEPARATOR .
                         'cron.php -mdefault 1';

        $this->cronGet = 'GET ' . Mage::helper('M2ePro/Magento')->getBaseUrl() .'cron.php';

        $this->cronLastRun = 'N/A';
        $this->cronLastGMT = false;

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()
                                        ->getGroupValue('/cron/', 'last_access');

        if (!is_null($cronLastAccessTime)) {

            if ($this->getIsSupportMode()) {
                $this->cronLastRun = Mage::helper('M2ePro')->gmtDateToTimezone($cronLastAccessTime);
            } else {
                $this->cronLastRun = $cronLastAccessTime;
                $this->cronLastGMT = true;
            }
        }

        $modelCron = Mage::getModel('M2ePro/Cron');

        $this->cronLastRunHighlight = 'none';
        if ($modelCron->isShowError()) {
            $this->cronLastRunHighlight = 'error';
        } else if ($modelCron->isShowNotification()) {
            $this->cronLastRunHighlight = 'warning';
        }

        return parent::_beforeToHtml();
    }

    // ########################################
}