<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Installation
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    public $lastVersion;
    public $installationVersionHistory;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionInstallation');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/installation.phtml');

        $this->prepareInfo();
    }

    // ########################################

    protected function prepareInfo()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $this->latestVersion = $cacheConfig->getGroupValue('/installation/', 'last_version');
        $this->installationVersionHistory = Mage::getModel('M2ePro/Registry')
                                                ->load('/installation/versions_history/', 'key')
                                                ->getValueFromJson();

        $this->latestUpgradeDate        = false;
        $this->latestUpgradeFromVersion = '--';
        $this->latestUpgradeToVersion   = '--';

        $lastVersion = array_pop($this->installationVersionHistory);
        if (!empty($lastVersion)) {

            $this->latestUpgradeDate        = $lastVersion['date'];
            $this->latestUpgradeFromVersion = $lastVersion['from'];
            $this->latestUpgradeToVersion   = $lastVersion['to'];
        }
    }

    protected function isShown()
    {
        if (is_null($this->latestVersion)) {
            return false;
        }

        $compareResult = version_compare(Mage::helper('M2ePro/Module')->getVersion(), $this->latestVersion);
        if ($compareResult >= 0 && !$this->latestUpgradeDate) {
            return false;
        }

        $daysLeftFromLastUpgrade = (Mage::helper('M2ePro')->getCurrentGmtDate(true) -
                                    Mage::helper('M2ePro')->getDate($this->latestUpgradeDate, true)) / 60 / 60 / 24;

        if ($compareResult >= 0 && $daysLeftFromLastUpgrade >= 7) {
            return false;
        }

        return true;
    }

    // ########################################
}