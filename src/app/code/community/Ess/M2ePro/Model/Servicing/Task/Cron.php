<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Cron implements Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'cron';
    }

    // ########################################

    public function getRequestData()
    {
        return array(
            'base_url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)
        );
    }

    public function processResponseData(array $data)
    {
        if (!isset($data['auth_key'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()
                                     ->setGroupValue('/cron/service/', 'auth_key', $data['auth_key']);
    }

    // ########################################
}