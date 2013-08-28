<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Model_Translation_Language extends Ess_M2ePro_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Translation_Language');
    }

    // ########################################

    public function getId()
    {
        return (int)$this->getData('id');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function isNeedSynchronization()
    {
        return (bool)(int)$this->getData('need_synch');
    }

    // ########################################

    public function runSynchronization()
    {
        if (!$this->isNowTimeToSynchronizationRun()) {
            return;
        }

        try {
            $languages = Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
                                                            ->processVirtual('translation','get','languages');
        } catch (Exception $e) {
            return;
        }

        if (!is_array($languages)) {
            return;
        }

        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');
        $languageTable  = $coreRes->getTableName('M2ePro/Translation_Language');
        $textTable  = $coreRes->getTableName('M2ePro/Translation_Text');

        $connWrite->query('TRUNCATE TABLE '.$languageTable);
        $connWrite->query('TRUNCATE TABLE '.$textTable);

        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $dataForInsert = array();
        foreach ($languages['languages'] as $language) {
            $dataForInsert[] = array(
                'code' => $language['code'],
                'title' => $language['title'],
                'update_date' => $currentDate,
                'create_date' => $currentDate
            );
        }

        if (count($dataForInsert) > 0) {
            $connWrite->insertMultiple($languageTable, $dataForInsert);
        }

        $this->setLastAccessTime();
    }

    protected function isNowTimeToSynchronizationRun()
    {
        $configGroup = '/translation/synchronization/';
        $lastAccessTime = Mage::helper('M2ePro/Module')->getConfig()
                                                       ->getGroupValue($configGroup,'last_access');
        $interval = Mage::helper('M2ePro/Module')->getConfig()
                                                 ->getGroupValue($configGroup,'interval');
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if (is_null($lastAccessTime) || $currentTimeStamp > strtotime($lastAccessTime) + $interval) {
            return true;
        }

        return false;
    }

    protected function setLastAccessTime()
    {
        $configGroup = '/translation/synchronization/';
        Mage::helper('M2ePro/Module')->getConfig()
                            ->setGroupValue($configGroup, 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate());
    }
}