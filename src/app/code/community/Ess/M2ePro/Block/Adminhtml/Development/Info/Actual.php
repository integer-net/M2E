<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Actual extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentSummaryInfo');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/actual.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->magentoInfo = Mage::helper('M2ePro')->__(ucwords(Mage::helper('M2ePro/Magento')->getEditionName())) .
            ' (' . Mage::helper('M2ePro/Magento')->getVersion() . ')';

        //-------------------------------------
        $this->moduleVersion = Mage::helper('M2ePro/Module')->getVersion();
        //-------------------------------------

        //-------------------------------------
        $this->phpVersion = Mage::helper('M2ePro/Client')->getPhpVersion();
        $this->phpApi = Mage::helper('M2ePro/Client')->getPhpApiName();
        //-------------------------------------

        //-------------------------------------
        $this->memoryLimit = Mage::helper('M2ePro/Client')->getMemoryLimit(true);
        $this->maxExecutionTime = @ini_get('max_execution_time');
        //-------------------------------------

        //-------------------------------------
        $this->mySqlVersion = Mage::helper('M2ePro/Client')->getMysqlVersion();
        $this->mySqlDatabaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();
        //-------------------------------------

        //-------------------------------------
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
        //-------------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}