<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_CronController extends Mage_Core_Controller_Varien_Action
{
    //#############################################

    public function preDispatch()
    {
        $this->getLayout()->setArea('frontend');
        parent::preDispatch();
    }

    //#############################################

    public function indexAction()
    {
        $this->closeConnection();

        $cron = Mage::getModel('M2ePro/Cron_Type_Service');
        $cron->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        $authKey = $this->getRequest()->getPost('auth_key',false);
        $authKey && $cron->setRequestAuthKey($authKey);

        $connectionId = $this->getRequest()->getPost('connection_id',false);
        $connectionId && $cron->setRequestConnectionId($connectionId);

        $cron->process();

        exit();
    }

    public function reportAction()
    {
        $connectionIds = $this->getRequest()->getPost('connection_ids', '');
        $connectionIds = (array)json_decode($connectionIds, true);

        $result = array();

        if (!empty($connectionIds)) {
            $result = $this->getConnectionsReports($connectionIds);
        }

        exit(json_encode($result));
    }

    public function resetAction()
    {
        Mage::getModel('M2ePro/Cron_Type_Service')->resetTasksStartFrom();
    }

    // --------------------------------------------

    public function testAction()
    {
        exit('ok');
    }

    //#############################################

    private function closeConnection()
    {
        header('Connection: Close');
        header('Content-Length: 13');
        echo 'processing...';

        while(ob_get_level()) {
            if (!$result = @ob_end_flush()) {
                break;
            }
        }

        flush();
    }

    private function getConnectionsReports($connectionIds)
    {
        $preparedOperationHistory = $this->getOperationHistoriesData();
        $lastCronConnectionId = key($preparedOperationHistory);

        $result = array();
        foreach ($connectionIds as $connectionId) {

            if (!array_key_exists($connectionId, $preparedOperationHistory)) {
                $result[$connectionId] = array(
                    'state'      => Ess_M2ePro_Helper_Module_Cron::STATE_NOT_FOUND,
                    'start_date' => null,
                    'end_date'   => null,
                    'data'       => null
                );
                continue;
            }

            $tempItem = $preparedOperationHistory[$connectionId];

            $startDate = $tempItem->getData('start_date');
            $endDate   = $tempItem->getData('end_date');

            $result[$connectionId] = array(
                'state'      => Ess_M2ePro_Helper_Module_Cron::STATE_IN_PROGRESS,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'data'       => null
            );

            if (!is_null($endDate) || !($lastCronConnectionId == $connectionId)) {
                $tempItem->setObject($tempItem);
                $result[$connectionId]['data'] = $tempItem->getFullDataInfo();
                $result[$connectionId]['state'] = Ess_M2ePro_Helper_Module_Cron::STATE_COMPLETED;
            }
        }

        return $result;
    }

    private function getOperationHistoriesData()
    {
        $tempDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $tempDateTime = $tempDateTime->modify("-2 day");
        $mysqlDateTime = $tempDateTime->format('Y-m-d H:i:s');

        $collection = Mage::getModel('M2ePro/OperationHistory')->getCollection()
            ->addFieldToFilter('nick', 'cron')
            ->addFieldToFilter('data', array('notnull' => true))
            ->addFieldToFilter('create_date', array('gt' => $mysqlDateTime));

        $collection->getSelect()->order('start_date DESC');

        $preparedData = array();
        foreach ($collection as $item) {
            $tempData = (array)json_decode($item->getData('data'), true);
            isset($tempData['connection_id']) && $preparedData[$tempData['connection_id']] = $item;
        }

        return $preparedData;
    }

    //#############################################
}