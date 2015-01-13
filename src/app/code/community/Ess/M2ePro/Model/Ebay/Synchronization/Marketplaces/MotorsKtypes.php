<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_MotorsKtypes
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/motors_ktypes/';
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

        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->isMarketplaceSupportsKtype(
            $params['marketplace_id']
        );
    }

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $partNumber = 1;
        $this->deleteAllKtypesForMarketplace($marketplace);

        while (true) {

            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                                                             'Get KTypes from eBay');
            $response = $this->receiveFromEbay($marketplace,$partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing KTypes data ('.(int)$response['current'].'/'.(int)$response['total'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),
                                                             'Save KTypes to DB');
            $this->saveKtypesToDb($marketplace,$response['parts']);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->getActualLockItem()->setPercents($this->getPercentsEnd());
            $this->getActualLockItem()->activate();

            $partNumber = $response['next'];

            if (is_null($partNumber)) {
                break;
            }
        }

        $this->logSuccessfulOperation($marketplace);
    }

    //####################################

    protected function receiveFromEbay(Ess_M2ePro_Model_Marketplace $marketplace, $partNumber)
    {
        $partSize = (int)$this->getConfigValue($this->getFullSettingsPath(), 'part_size');

        $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
            ->processVirtual('marketplace','get','motorsKtypes',
                array('part_number' => $partNumber, 'part_size' => $partSize),
                NULL,$marketplace->getId(),NULL,NULL);

        if (is_null($response) || empty($response['parts'])) {
            $response = array();
        } else {
            $this->getActualOperationHistory()->addText('Total received parts from eBay: '.count($response['parts']));
        }

        return $response;
    }

    protected function deleteAllKtypesForMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsKtypes = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_ktype');

        $connWrite->delete($tableMotorsKtypes,array('marketplace_id = ?'=>$marketplace->getId()));
    }

    protected function saveKtypesToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $parts)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsKtype = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_ktype');

        $iteration = 0;
        $iterationsForOneStep = 1000;

        $totalCountItems = 0;
        foreach ($parts as $part) {
            $totalCountItems += count($part['items']);
        }

        $percentsForOneStep = ($this->getPercentsInterval()/2) / ($totalCountItems/$iterationsForOneStep);

        foreach ($parts as $part) {
            $itemsForInsert = array();

            for ($i = 0; $i < count($part['items']); $i++) {

                $item = $part['items'][$i];

                $itemsForInsert[] = array(
                    'marketplace_id' => $marketplace->getId(),
                    'ktype'          => (int)$item['ktype'],
                    'make'           => $item['make'],
                    'model'          => $item['model'],
                    'variant'        => $item['variant'],
                    'body_style'     => $item['body_style'],
                    'type'           => $item['type'],
                    'from_year'      => (int)$item['from_year'],
                    'to_year'        => (int)$item['to_year'],
                    'engine'         => $item['engine'],
                );

                if (count($itemsForInsert) >= 100 || $i >= (count($part['items']) - 1)) {
                    $connWrite->insertMultiple($tableMotorsKtype, $itemsForInsert);
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

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // M2ePro_TRANSLATIONS
        // The "Parts Compatibility" action for eBay Site: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Parts Compatibility" action for eBay Site: "%mrk%" has been successfully completed.',
            array('mrk'=>$marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}