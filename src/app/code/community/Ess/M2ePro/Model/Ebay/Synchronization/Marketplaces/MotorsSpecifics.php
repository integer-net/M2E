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

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        if ($marketplace->getId() != Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            return false;
        }

        return true;
    }

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $partNumber = (int)$this->getConfigValue($this->getFullSettingsPath(), 'part_next');
        $partNumber <= 0 && $partNumber = 1;

        $this->deleteAllSpecificsForMarketplace($marketplace);

        while (true) {

            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get specifics from eBay');
            $response = $this->receiveFromEbay($marketplace,$partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing data ('.(int)$response['current'].'/'.(int)$response['total'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save specifics to DB');
            $this->saveSpecificsToDb($marketplace,$response['parts']);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->getActualLockItem()->setPercents($this->getPercentsEnd());
            $this->getActualLockItem()->activate();

            $partNumber = $response['next'];
            $this->setConfigValue($this->getFullSettingsPath(), 'part_next', $partNumber);

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
                            ->processVirtual('marketplace','get','motorsSpecifics',
                                             array('part_number' => $partNumber, 'part_size' => $partSize),
                                             NULL,$marketplace->getId(),NULL,NULL);

        if (is_null($response) || empty($response['parts'])) {
            $response = array();
        } else {
            $this->getActualOperationHistory()->addText('Total received parts from eBay: '.count($response['parts']));
        }

        return $response;
    }

    protected function deleteAllSpecificsForMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsSpecifics = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_specific');

        $connWrite->delete($tableMotorsSpecifics,array('marketplace_id = ?'=>$marketplace->getId()));
    }

    protected function saveSpecificsToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $parts)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsSpecifics = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_specific');

        $iteration = 0;
        $iterationsForOneStep = 1000;

        $totalCountItems = 0;
        foreach ($parts as $part) {
            $totalCountItems += count($part['items']);
        }

        $percentsForOneStep = ($this->getPercentsInterval()/2) / ($totalCountItems/$iterationsForOneStep);

        foreach ($parts as $part) {
            foreach ($part['items'] as $item) {

                $insertData = array(
                    'epid'           => $item['ePID'],
                    'marketplace_id' => $marketplace->getId(),
                    'product_type'   => (int)$part['product_type'],
                    'make'           => $item['Make'],
                    'model'          => $item['Model'],
                    'year'           => $item['Year'],
                    'engine'         => (isset($item['Engine']) ? $item['Engine'] : NULL),
                    'submodel'       => (isset($item['Submodel']) ? $item['Submodel'] : NULL)
                );

                $connWrite->insert($tableMotorsSpecifics, $insertData);

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
        // ->__('The "Parts Compatibility" action for eBay Site: "%mrk%" has been successfully completed.');

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