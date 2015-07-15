<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Server extends Mage_Core_Helper_Abstract
{
    const MAX_INTERVAL_OF_RETURNING_TO_DEFAULT_BASEURL = 86400;

    // ########################################

    public function getEndpoint()
    {
        if ($this->getCurrentBaseUrlIndex() != $this->getDefaultBaseUrlIndex()) {

            $currentTimeStamp = Mage::helper('M2ePro/Data')->getCurrentGmtDate(true);

            $interval = self::MAX_INTERVAL_OF_RETURNING_TO_DEFAULT_BASEURL;
            $switchingDateTime = Mage::helper('M2ePro/Module')->getCacheConfig()
                                        ->getGroupValue('/server/baseurl/','datetime_of_last_switching');

            if (is_null($switchingDateTime) || strtotime($switchingDateTime) + $interval <= $currentTimeStamp) {
                $this->setCurrentBaseUrlIndex($this->getDefaultBaseUrlIndex());
            }
        }

        return $this->getCurrentBaseUrl().'index.php';
    }

    public function switchEndpoint()
    {
        $previousIndex = $this->getCurrentBaseUrlIndex();
        $nextIndex = $previousIndex + 1;

        is_null($this->getBaseUrlByIndex($nextIndex)) && $nextIndex = 1;
        $this->setCurrentBaseUrlIndex($nextIndex);

        if ($this->getCurrentBaseUrlIndex() == $previousIndex) {
            return false;
        }

        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfig->setGroupValue('/server/baseurl/','datetime_of_last_switching',
                                        Mage::helper('M2ePro/Data')->getCurrentGmtDate());

        return true;
    }

    // ########################################

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

        $curlInfo    = curl_getinfo($curlObject);
        $errorNumber = curl_errno($curlObject);

        curl_close($curlObject);

        if ($response === false) {

            if ($errorNumber !== CURLE_OPERATION_TIMEOUTED &&
                !$secondAttempt && $this->switchEndpoint()) {
                return $this->sendRequest($postData,$headers,$timeout,true);
            }

            throw new Ess_M2ePro_Model_Exception('Server connection is failed. Please try again later.',
                                                 array('curl_info' => $curlInfo, 'curl_error_number' => $errorNumber));
        }

        return array(
            'response'          => $response,
            'curl_error_number' => $errorNumber,
            'curl_info'         => $curlInfo
        );
    }

    // ########################################

    private function getCurrentBaseUrl()
    {
        return $this->getBaseUrlByIndex($this->getCurrentBaseUrlIndex());
    }

    // ----------------------------------------

    private function getDefaultBaseUrlIndex()
    {
        $index = (int)Mage::helper('M2ePro/Primary')->getConfig()
                        ->getGroupValue('/server/','default_baseurl_index');

        if ($index <= 0 || $index > $this->getMaxBaseUrlIndex()) {
            $this->setDefaultBaseUrlIndex($index = 1);
        }

        return $index;
    }

    private function getCurrentBaseUrlIndex()
    {
        $index = (int)Mage::helper('M2ePro/Module')->getCacheConfig()
                        ->getGroupValue('/server/baseurl/','current_index');

        if ($index <= 0 || $index > $this->getMaxBaseUrlIndex()) {
            $this->setCurrentBaseUrlIndex($index = $this->getDefaultBaseUrlIndex());
        }

        return $index;
    }

    // ----------------------------------------

    private function setDefaultBaseUrlIndex($index)
    {
        Mage::helper('M2ePro/Primary')->getConfig()
                ->getGroupValue('/server/','default_baseurl_index',$index);
    }

    private function setCurrentBaseUrlIndex($index)
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()
                ->setGroupValue('/server/baseurl/','current_index',$index);
    }

    // ########################################

    private function getMaxBaseUrlIndex()
    {
        $index = 1;

        for ($tempIndex=2; $tempIndex<100; $tempIndex++) {

            $tempBaseUrl = $this->getBaseUrlByIndex($tempIndex);

            if (!is_null($tempBaseUrl)) {
                $index = $tempIndex;
            } else {
                break;
            }
        }

        return $index;
    }

    private function getBaseUrlByIndex($index)
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','baseurl_'.$index);
    }

    // ########################################
}