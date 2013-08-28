<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_License extends Mage_Core_Helper_Abstract
{
    const MODE_NONE = 0;
    const MODE_TRIAL = 1;
    const MODE_LIVE = 2;

    const STATUS_NONE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_CLOSED = 3;

    const IS_FREE_NO = 0;
    const IS_FREE_YES = 1;

    // ########################################

    public function getKey()
    {
        $key = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key'
        );
        return !is_null($key) ? (string)$key : '';
    }

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain'
        );
        return !is_null($domain) ? (string)$domain : '';
    }

    public function getIp()
    {
        $ip = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip'
        );
        return !is_null($ip) ? (string)$ip : '';
    }

    public function getDirectory()
    {
        $directory = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory'
        );
        return !is_null($directory) ? (string)$directory : '';
    }

    // ########################################

    public function getMode($component)
    {
        $mode = (int)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','mode'
        );

        $validValues = array(self::MODE_NONE, self::MODE_TRIAL, self::MODE_LIVE);

        if (in_array($mode,$validValues)) {
            return $mode;
        }

        return self::MODE_NONE;
    }

    public function isNoneMode($component)
    {
        return $this->getMode($component) == self::MODE_NONE;
    }

    public function isTrialMode($component)
    {
        return $this->getMode($component) == self::MODE_TRIAL;
    }

    public function isLiveMode($component)
    {
        return $this->getMode($component) == self::MODE_LIVE;
    }

    //--------------------------

    public function getStatus($component)
    {
        $status = (int)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','status'
        );

        $validValues = array(self::STATUS_NONE, self::STATUS_ACTIVE,
                             self::STATUS_SUSPENDED, self::STATUS_CLOSED);

        if (in_array($status,$validValues)) {
            return $status;
        }

        return self::STATUS_NONE;
    }

    public function isNoneStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_NONE;
    }

    public function isActiveStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_ACTIVE;
    }

    public function isSuspendedStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_SUSPENDED;
    }

    public function isClosedStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_CLOSED;
    }

    //--------------------------

    public function getIsFree($component)
    {
        $isFree = (int)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','is_free'
        );

        $validValues = array(self::IS_FREE_NO, self::IS_FREE_YES);

        if (in_array($isFree,$validValues)) {
            return $isFree;
        }

        return self::IS_FREE_YES;
    }

    public function isFreeEnabled($component)
    {
        return $this->getIsFree($component) == self::IS_FREE_YES;
    }

    public function isFreeDisabled($component)
    {
        return $this->getIsFree($component) == self::IS_FREE_NO;
    }

    // ########################################

    public function isExpirationDate($component)
    {
        return $this->getIntervalBeforeExpirationDate($component) == 0;
    }

    public function getTimeStampExpirationDate($component)
    {
        $date = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','expiration_date'
        );
        return (is_null($date) || $date == '') ?
                Mage::helper('M2ePro')->getCurrentGmtDate(true)-60*60*24 :
                (int)strtotime($date);
    }

    public function getTextExpirationDate($component,$withTime = false)
    {
        if ($withTime) {
            return Mage::helper('M2ePro')->gmtDateToTimezone(
                $this->getTimeStampExpirationDate($component)
            );
        } else {
            return Mage::helper('M2ePro')->gmtDateToTimezone(
                $this->getTimeStampExpirationDate($component),false,'Y-m-d'
            );
        }
    }

    public function getIntervalBeforeExpirationDate($component)
    {
        $timeStampCurrentDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $timeStampExpDate = $this->getTimeStampExpirationDate($component);

        if ($timeStampExpDate <= $timeStampCurrentDate) {
            return 0;
        }

        return $timeStampExpDate - $timeStampCurrentDate;
    }

    // ########################################

    public function checkPresencePaidComponents()
    {
        $requestParams = array(
            'components' => Mage::helper('M2ePro/Component')->getComponents()
        );

        $response = Mage::getModel('M2ePro/Connector_Server_Api_Dispatcher')
                            ->processVirtual('license','get','feeStatus',
                                              $requestParams);

        foreach ($response['components'] as $isFree) {
            if ($isFree === self::IS_FREE_NO) {
                return true;
            }
        }

        return false;
    }

    public function obtainFreeRecord($email = NULL, $firstName = NULL, $lastName = NULL,
                                     $country = NULL, $city = NULL, $postalCode = NULL)
    {
        $requestParams = array(
            'valid_domain' => Mage::helper('M2ePro/Server')->getDomain(),
            'valid_ip' => Mage::helper('M2ePro/Server')->getIp(),
            'valid_directory' => Mage::helper('M2ePro/Server')->getBaseDirectory()
        );

        !is_null($email) && $requestParams['email'] = $email;
        !is_null($firstName) && $requestParams['first_name'] = $firstName;
        !is_null($lastName) && $requestParams['last_name'] = $lastName;
        !is_null($country) && $requestParams['country'] = $country;
        !is_null($city) && $requestParams['city'] = $city;
        !is_null($postalCode) && $requestParams['postal_code'] = $postalCode;

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $requestParams[strtolower($component).'_access'] = 1;
        }

        $response = Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
                            ->processVirtual('license','add','freeRecord',
                                              $requestParams);

        if (!isset($response['key'])) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key',(string)$response['key']
        );

        Mage::getModel('M2ePro/Servicing_Dispatcher')->processTasks(array(
            Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
        ));

        return true;
    }

    // ########################################
}