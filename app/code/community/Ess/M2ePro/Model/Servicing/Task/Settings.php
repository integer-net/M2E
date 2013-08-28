<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
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
    }

    // ########################################

    private function updateLockData(array $data)
    {
        $validValues = array(
            Ess_M2ePro_Helper_Module::SERVER_LOCK_NO,
            Ess_M2ePro_Helper_Module::SERVER_LOCK_YES
        );

        if (isset($data['lock']) && in_array((int)$data['lock'],$validValues)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',(int)$data['lock']
            );
        }
    }

    // ########################################
}