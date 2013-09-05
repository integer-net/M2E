<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_StopQueue extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 55;
    const PERCENTS_END = 65;
    const PERCENTS_INTERVAL = 10;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Stopping Products Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Stopping Products" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Stopping Products" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare last time
        $this->prepareLastTime();

        // Check locked last time
        if ($this->isLockedLastTime()) {
            return;
        }

        $isProcessedItems = false;
        $components = Mage::helper('M2ePro/Component')->getComponents();
        foreach ($components as $component) {
            $tempFlag = $this->sendComponentRequests($component);
            $tempFlag && $isProcessedItems = true;
        }

        if (!$isProcessedItems) {
            $this->setCheckLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        }
    }

    //####################################

    private function sendComponentRequests($component)
    {
        $items = Mage::getModel('M2ePro/StopQueue')->getCollection()
                    ->addFieldToFilter('is_processed',0)
                    ->addFieldToFilter('component_mode',$component)
                    ->getItems();

        $accountMarketplaceItems = array();

        foreach ($items as $item) {

            /** @var Ess_M2ePro_Model_StopQueue $item */
            $tempKey = (string)$item->getMarketplaceId().'_'.$item->getAccountHash();

            if (!isset($accountMarketplaceItems[$tempKey])) {
                $accountMarketplaceItems[$tempKey] = array();
            }

            if (count($accountMarketplaceItems[$tempKey]) >= 100) {
                continue;
            }

            $accountMarketplaceItems[$tempKey][] = $item;
        }

        foreach ($accountMarketplaceItems as $items) {

            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {

                $parts = array_chunk($items,10);

                foreach ($parts as $part) {
                    if (count($part) <= 0) {
                        continue;
                    }
                    $this->sendAccountMarketplaceRequests($component,$part);
                }

            } else {
                $this->sendAccountMarketplaceRequests($component,$items);
            }

            foreach ($items as $item) {
                /** @var Ess_M2ePro_Model_StopQueue $item */
                $item->setData('is_processed',1)->save();
            }
        }

        return count($accountMarketplaceItems) > 0;
    }

    private function sendAccountMarketplaceRequests($component, $accountMarketplaceItems)
    {
        try {

            $requestData = array(
                'items' => array(),
            );

            /** @var Ess_M2ePro_Model_StopQueue $tempItem */
            $tempItem = $accountMarketplaceItems[0];
            $requestData['account'] = $tempItem->getAccountHash();
            if (!is_null($tempItem->getMarketplaceId())) {
                $requestData['marketplace'] = $tempItem->getMarketplaceId();
            }

            foreach ($accountMarketplaceItems as $item) {
                /** @var Ess_M2ePro_Model_StopQueue $item */
                $tempIndex = count($requestData['items']);
                $component == Ess_M2ePro_Helper_Component_Ebay::NICK && $tempIndex+=100;
                $requestData['items'][$tempIndex] = $item->getDecodedItemData();
            }

            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $entity = 'item';
                $type = 'update';
                $name = 'ends';
            } else {
                $entity = 'product';
                $type = 'update';
                $name = 'entities';
            }

            $dispatcher = Mage::getModel('M2ePro/Connector_Server_'.ucwords($component).'_Dispatcher');
            $dispatcher->processVirtualAbstract($entity, $type, $name, $requestData);

        } catch (Exception $exception) {}
    }

    //####################################

    private function prepareLastTime()
    {
        $lastTime = $this->getCheckLastTime();
        if (empty($lastTime)) {
            $lastTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastTime->modify("-1 year");
            $this->setCheckLastTime($lastTime);
        }
    }

    private function isLockedLastTime()
    {
        $lastTime = strtotime($this->getCheckLastTime());

        $tempGroup = '/defaults/stop_queue/';
        $interval = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue($tempGroup,'interval');

        if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return true;
        }

        return false;
    }

    //------------------------------------

    private function getCheckLastTime()
    {
        $tempGroup = '/defaults/stop_queue/';
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue($tempGroup,'last_time');
    }

    private function setCheckLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }
        $tempGroup = '/defaults/stop_queue/';
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue($tempGroup,'last_time',$time);
    }

    //####################################
}