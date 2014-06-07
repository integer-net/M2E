<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Specifics
    extends Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/specifics/';
    }

    protected function getTitle()
    {
        return 'Specifics';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 75;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Marketplace', (int)$params['marketplace_id']
        );

        $this->getActualOperationHistory()->addText('Starting marketplace "'.$marketplace->getTitle().'"');

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get specifics from Amazon');
        $specifics = $this->receiveFromAmazon($marketplace);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->getActualLockItem()->activate();

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save specifics to DB');

        $this->clearXsdsHashes($marketplace);
        $this->saveSpecificsToDb($marketplace,$specifics);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        $this->logSuccessfulOperation($marketplace);
    }

    //####################################

    protected function receiveFromAmazon(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $specifics = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher')
                          ->processVirtual('marketplace','get','info',
                                           array('include_specifics' => true,
                                                 'marketplace' => $marketplace->getNativeId()),
                                           'info',NULL,NULL);

        $specifics = is_null($specifics) ? array() : $specifics['specifics'];

        $this->getActualOperationHistory()->addText('Total received specifics from Amazon: '.count($specifics));

        return $specifics;
    }

    protected function clearXsdsHashes(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableMarketplaces = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');
        $tableSpecifics = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        $nodesData = $connWrite->select()
                               ->from($tableMarketplaces, 'nodes')
                               ->where('marketplace_id = ?', $marketplace->getId())
                               ->query()->fetchColumn();

        $nodesData = json_decode($nodesData,true);

        $marketplaceXsds = array();
        foreach ($nodesData as $nodeData) {
            foreach ($nodeData['xsds'] as $xsd) {
                $marketplaceXsds[] = $connWrite->quote($xsd['hash']);
            }
        }

        array_unique($marketplaceXsds);

        if (count($marketplaceXsds) <= 0) {
            return;
        }

        $connWrite->delete(
            $tableSpecifics, array('xsd_hash IN ('.implode(',', $marketplaceXsds).')')
        );
    }

    protected function saveSpecificsToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $specifics)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableSpecifics = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        if (!count($specifics)) {
            return;
        }

        $iteration = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep = ($this->getPercentsInterval()/2) / (count($specifics)/$iterationsForOneStep);

        foreach ($specifics as &$data) {

            $connWrite->insert($tableSpecifics, $data);

            if (++$iteration % $iterationsForOneStep == 0) {
                $percentsShift = ($iteration/$iterationsForOneStep) * $percentsForOneStep;
                $this->getActualLockItem()->setPercents(
                    $this->getPercentsStart() + $this->getPercentsInterval()/2 + $percentsShift
                );
            }
        }
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // ->__('The "Specifics" action for Amazon Marketplace: "%mrk%" has been successfully completed.');

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Specifics" action for Amazon Marketplace: "%mrk%" has been successfully completed.',
            array('mrk' => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}