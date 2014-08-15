<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Settings implements Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'settings';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        $this->updateLockData($data);
        $this->updateServersBaseUrls($data);
        $this->updateLastVersion($data);
    }

    // ########################################

    private function updateLockData(array $data)
    {
        if (!isset($data['lock'])) {
            return;
        }

        $validValues = array(
            Ess_M2ePro_Helper_Module::SERVER_LOCK_NO,
            Ess_M2ePro_Helper_Module::SERVER_LOCK_YES
        );

        if (!in_array((int)$data['lock'],$validValues)) {
            return;
        }

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',(int)$data['lock']
        );
    }

    private function updateServersBaseUrls(array $data)
    {
        if (!isset($data['servers_baseurls']) || !is_array($data['servers_baseurls'])) {
            return;
        }

        $config = Mage::helper('M2ePro/Primary')->getConfig();

        $index = 1;
        foreach ($data['servers_baseurls'] as $newServerBaseUrl) {

            $oldServerBaseUrl = $config->getGroupValue('/server/','baseurl_'.$index);

            if ($oldServerBaseUrl != $newServerBaseUrl) {
                $config->setGroupValue('/server/', 'baseurl_'.$index, $newServerBaseUrl);
            }

            $index++;
        }
    }

    private function updateLastVersion(array $data)
    {
        if (empty($data['last_version'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/installation/version/', 'last_version', $data['last_version']
        );
    }

    // ########################################
}