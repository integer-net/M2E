<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Server extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getEndpoint()
    {
        return $this->getBaseUrl().'index.php';
    }

    public function switchEndpoint()
    {
        return $this->switchBaseUrl();
    }

    // ----------------------------------------

    public function getAdminKey()
    {
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','admin_key');
    }

    public function getApplicationKey()
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$moduleName.'/server/','application_key'
        );
    }

    // ########################################

    public function sendRequest(array $postData,
                                array $headers,
                                $timeout = 300,
                                $secondAttempt = false)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $this->getEndpoint());

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the headers using the array of headers
        curl_setopt($curlObject, CURLOPT_HTTPHEADER, $headers);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query($postData,'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlObject, CURLOPT_TIMEOUT, $timeout);

        $response = curl_exec($curlObject);
        $errorNumber = curl_errno($curlObject);

        curl_close($curlObject);

        if ($response === false) {

            if ($errorNumber !== CURLE_OPERATION_TIMEOUTED &&
                !$secondAttempt && $this->switchEndpoint()) {
                return $this->sendRequest($postData,$headers,$timeout,true);
            }

            throw new Exception('Server connection is failed. Please try again later.');
        }

        return $response;
    }

    // ########################################

    private function getBaseUrl()
    {
        $index = $this->getBaseUrlIndex();

        $baseUrl = Mage::helper('M2ePro/Primary')->getConfig()
                        ->getGroupValue('/server/','baseurl_'.$index);

        if (empty($baseUrl) || ($index > 1 && $this->isBaseUrlEmergencyTimeExceeded())) {

            $index = 1;
            $this->setBaseUrlIndex($index);

            $baseUrl = Mage::helper('M2ePro/Primary')->getConfig()
                            ->getGroupValue('/server/','baseurl_'.$index);
        }

        return $baseUrl;
    }

    private function switchBaseUrl()
    {
        $currentIndex = $this->getBaseUrlIndex();
        $nextIndex = $currentIndex + 1;

        $baseUrl = Mage::helper('M2ePro/Primary')->getConfig()
                        ->getGroupValue('/server/','baseurl_'.$nextIndex);

        if (!empty($baseUrl)) {
            $this->setBaseUrlIndex($nextIndex);
            return true;
        }

        if ($currentIndex > 1) {
            $this->setBaseUrlIndex(1);
            return true;
        }

        return false;
    }

    // ----------------------------------------

    private function getBaseUrlIndex()
    {
        $index = Mage::helper('M2ePro/Module')->getCacheConfig()
                        ->getGroupValue('/server/baseurl/','current_index');
        is_null($index) && $this->setBaseUrlIndex($index = 1);
        return (int)$index;
    }

    private function setBaseUrlIndex($index)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $currentIndex = $cacheConfig->getGroupValue('/server/baseurl/','current_index');

        if (!is_null($currentIndex) && $currentIndex == $index) {
            return;
        }

        $cacheConfig->setGroupValue('/server/baseurl/','current_index',$index);

        if ((is_null($currentIndex) || $currentIndex == 1) && $index > 1) {
            $cacheConfig->setGroupValue('/server/baseurl/','date_of_emergency_state',
                                        Mage::helper('M2ePro/Data')->getCurrentGmtDate());
        }

        if (!is_null($currentIndex) && $currentIndex > 1 && $index == 1) {
            $cacheConfig->deleteGroupValue('/server/baseurl/','date_of_emergency_state');
        }
    }

    // ----------------------------------------

    private function isBaseUrlEmergencyTimeExceeded()
    {
        $currentTimestamp = Mage::helper('M2ePro/Data')->getCurrentGmtDate(true);

        $emergencyDateTime = Mage::helper('M2ePro/Module')->getCacheConfig()
                                    ->getGroupValue('/server/baseurl/','date_of_emergency_state');

        return is_null($emergencyDateTime) || strtotime($emergencyDateTime) + 86400 < $currentTimestamp;
    }

    // ########################################
}