<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsRequester
{
    const TIMEOUT_ERRORS_COUNT_TO_RISE = 3;
    const TIMEOUT_RISE_ON_ERROR        = 30;
    const TIMEOUT_RISE_MAX_VALUE       = 1500;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX
            .'_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->create();

        $this->account->addObjectLock(NULL, $processingRequest->getHash());
        $this->account->addObjectLock('synchronization', $processingRequest->getHash());
        $this->account->addObjectLock('synchronization_amazon', $processingRequest->getHash());
        $this->account->addObjectLock(
            Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX, $processingRequest->getHash()
        );
    }

    //########################################

    public function process()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/amazon/synchronization/orders/receive/timeout';

        try {

            parent::process();

        } catch (Ess_M2ePro_Model_Exception_Connection $exception) {

            $data = $exception->getAdditionalData();
            if (!empty($data['curl_error_number']) && $data['curl_error_number'] == CURLE_OPERATION_TIMEOUTED) {

                $fails = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'fails');
                $fails++;

                $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
                $rise += self::TIMEOUT_RISE_ON_ERROR;

                if ($fails >= self::TIMEOUT_ERRORS_COUNT_TO_RISE && $rise <= self::TIMEOUT_RISE_MAX_VALUE) {

                    $fails = 0;
                    $cacheConfig->setGroupValue($cacheConfigGroup, 'rise', $rise);
                }
                $cacheConfig->setGroupValue($cacheConfigGroup, 'fails', $fails);
            }

            throw $exception;
        }

        $cacheConfig->setGroupValue($cacheConfigGroup, 'fails', 0);
    }

    //########################################

    protected function getRequestTimeout()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/amazon/synchronization/orders/receive/timeout';

        $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
        $rise > self::TIMEOUT_RISE_MAX_VALUE && $rise = self::TIMEOUT_RISE_MAX_VALUE;

        return 300 + $rise;
    }

    //########################################
}