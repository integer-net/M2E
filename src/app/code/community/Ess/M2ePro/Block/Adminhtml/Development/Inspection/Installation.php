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
        $this->latestVersion = $cacheConfig->getGroupValue('/installation/version/', 'last_version');

        /** @var $cacheConfigCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $cacheConfigCollection = $cacheConfig->getCollection()
                                             ->addFieldToFilter('`group`', '/installation/version/history/')
                                             ->setOrder('create_date','DESC');

        $history = $cacheConfigCollection->toArray();
        $this->installationVersionHistory = $history['items'];

        $this->latestUpgradeDate        = false;
        $this->latestUpgradeFromVersion = '--';
        $this->latestUpgradeToVersion   = '--';

        if (isset($this->installationVersionHistory[0])) {

            $this->latestUpgradeDate        = $this->installationVersionHistory[0]['create_date'];
            $this->latestUpgradeFromVersion = $this->installationVersionHistory[0]['value'];
            $this->latestUpgradeToVersion   = $this->installationVersionHistory[0]['key'];
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