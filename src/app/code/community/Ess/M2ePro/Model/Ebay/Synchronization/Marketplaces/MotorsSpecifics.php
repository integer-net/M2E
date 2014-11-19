<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_MotorsSpecifics
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/motors_specifics/';
    }

    protected function getTitle()
    {
        return 'Parts Compatibility';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        $params = $this->getParams();

        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->isMarketplaceSupportsSpecific(
            $params['marketplace_id']
        );
    }

    protected function performActions()
    {
        $partNumber = 1;
        $this->deleteAllSpecifics();

        while (true) {

            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get_motor','Get Motor specifics from eBay');
            $response = $this->receiveFromEbay($partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get_motor');

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing data ('.(int)$response['current'].'/'.(int)$response['total'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save_motor','Save specifics to DB');
            $this->saveSpecificsToDb($response['parts']);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save_motor');

            $this->getActualLockItem()->setPercents($this->getPercentsEnd());
            $this->getActualLockItem()->activate();

            $partNumber = $response['next'];

            if (is_null($partNumber)) {
                break;
            }
        }

        $this->logSuccessfulOperation();
    }

    //####################################

    protected function receiveFromEbay($partNumber)
    {
        $partSize = (int)$this->getConfigValue($this->getFullSettingsPath(), 'part_size');

        $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                            ->processVirtual('marketplace','get','motorsSpecifics',
                                             array('part_number' => $partNumber, 'part_size' => $partSize),
                                             NULL,NULL,NULL,NULL);

        if (is_null($response) || empty($response['parts'])) {
            $response = array();
        } else {
            $this->getActualOperationHistory()->addText('Total received parts from eBay: '.count($response['parts']));
        }

        return $response;
    }

    protected function deleteAllSpecifics()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsSpecifics = Mage::getSingleton('core/resource')
                                        ->getTableName('m2epro_ebay_dictionary_motor_specific');

        $connWrite->delete($tableMotorsSpecifics);
    }

    protected function saveSpecificsToDb(array $parts)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsSpecifics = Mage::getSingleton('core/resource')
                                        ->getTableName('m2epro_ebay_dictionary_motor_specific');

        $iteration = 0;
        $iterationsForOneStep = 1000;

        $totalCountItems = 0;
        foreach ($parts as $part) {
            $totalCountItems += count($part['items']);
        }

        $percentsForOneStep = ($this->getPercentsInterval()/2) / ($totalCountItems/$iterationsForOneStep);

        foreach ($parts as $part) {
            for ($i = 0; $i < count($part['items']); $i++) {

                $item = $part['items'][$i];

                $itemsForInsert[] = array(
                    'epid'         => $item['ePID'],
                    'product_type' => (int)$part['product_type'],
                    'make'         => $item['Make'],
                    'model'        => $item['Model'],
                    'year'         => $item['Year'],
                    'trim'         => (isset($item['Trim']) ? $item['Trim'] : NULL),
                    'engine'       => (isset($item['Engine']) ? $item['Engine'] : NULL),
                    'submodel'     => (isset($item['Submodel']) ? $item['Submodel'] : NULL)
                );

                if (count($itemsForInsert) >= 100 || $i >= (count($part['items']) - 1)) {
                    $connWrite->insertMultiple($tableMotorsSpecifics, $itemsForInsert);
                    $itemsForInsert = array();
                }

                if (++$iteration % $iterationsForOneStep == 0) {
                    $percentsShift = ($iteration/$iterationsForOneStep) * $percentsForOneStep;
                    $this->getActualLockItem()->setPercents(
                        $this->getPercentsStart() + $this->getPercentsInterval()/2 + $percentsShift
                    );
                }
            }
        }
    }

    protected function logSuccessfulOperation()
    {
        $message = Mage::helper('M2ePro')->__(
            'The "Parts Compatibility" action for eBay Motors Site has been successfully completed.'
        );
        $this->getLog()->addMessage($message,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}