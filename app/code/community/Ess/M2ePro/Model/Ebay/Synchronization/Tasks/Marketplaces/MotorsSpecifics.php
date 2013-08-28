<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces_MotorsSpecifics
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    private $configGroup = '/ebay/synchronization/settings/marketplaces/motors_specifics/';
    private $synchName = 'Parts Compatibility Synchronization';

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

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Parts Compatibility Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Receive Parts Compatibility" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Receive Parts Compatibility" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS
        );
        $marketplaceTitle = Mage::helper('M2ePro')->__($marketplace->getTitle());

        if (!$marketplace->isStatusEnabled()) {
            return;
        }

        //-----------------------
        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        $this->_lockItem->setTitle($marketplaceTitle);
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();
        //-----------------------

        //-----------------------
        $this->_profiler->addTitle('Starting marketplace "'.$marketplace->getTitle().'"');
        $this->_profiler->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get parts compatibility from eBay');
        //-----------------------

        // Prepare MySQL data
        //-----------------------
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsSpecifics = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_ebay_motor_specific');
        //-----------------------

        //-----------------------
        $partNumber = (int)$config->getGroupValue($this->configGroup, 'part_next');
        $partNumber <= 0 && $partNumber = 1;
        $partSize = (int)$config->getGroupValue($this->configGroup, 'part_size');
        //-----------------------

        //-----------------------
        while (true) {

            //-----------------------
            $this->setLockItemStatus('Retrieving data for "%mrk%" marketplace.', '%mrk%', $marketplaceTitle);
            $this->setPercent(self::PERCENTS_START);
            //-----------------------

            //-----------------------
            $params = array('part_number' => $partNumber, 'part_size' => $partSize);
            $response = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')->processVirtualAbstract(
                'marketplace','get','motorsSpecifics', $params, null, $marketplace->getId(),NULL,NULL
            );

            if (!isset($response['parts']) || !is_array($response['parts']) || count($response['parts']) == 0) {
                break;
            }
            //-----------------------

            //-----------------------
            $percentsForDataRetrieve = self::PERCENTS_INTERVAL / 5;
            $percentsForDataSave = self::PERCENTS_INTERVAL - $percentsForDataRetrieve;
            $percentsPerPart = floor($percentsForDataSave / count($response['parts']));
            //-----------------------

            //-----------------------
            $status = 'Processing data for "%mrk%" marketplace (%cur%/%total%).';
            $search = array('%mrk%', '%cur%', '%total%');
            $replace = array($marketplaceTitle, (int)$response['current'], (int)$response['total']);
            $this->setLockItemStatus($status, $search, $replace);
            $this->setPercent(self::PERCENTS_START + $percentsForDataRetrieve);

            $this->_profiler->addTitle('Total received parts "'.count($response['parts']).'"');
            $this->_profiler->saveTimePoint(__METHOD__.'get'.$marketplace->getId());
            //-----------------------

            $useTransactions = false;

            foreach ($response['parts'] as $part) {
                $productType = (int)$part['product_type'];

                $itemsProcessed = 0;
                $itemsForOnePercent = floor(count($part['items']) / $percentsPerPart);

                try {
                    $useTransactions && $connWrite->beginTransaction();

                    foreach ($part['items'] as $item) {
                        $item['marketplace_id'] = $marketplace->getId();
                        $item['product_type'] = $productType;
                        $connWrite->insertOnDuplicate($tableMotorsSpecifics, $item);

                        $itemsProcessed++;

                        if (!$useTransactions && $itemsProcessed % $itemsForOnePercent == 0) {
                            $this->addPercent(1);
                        }
                    }

                    $useTransactions && $connWrite->commit();
                    $this->addPercent($percentsPerPart);
                } catch (Exception $e) {
                    $useTransactions && $connWrite->rollback();
                    throw $e;
                }
            }

            //-----------------------
            $this->setPercent(self::PERCENTS_END);
            //-----------------------

            if (is_null($response['next'])) {
                $config->deleteGroupValue($this->configGroup, 'part_next');
                break;
            }

            $partNumber = $response['next'];
            $config->setGroupValue($this->configGroup, 'part_next', $partNumber);
        }
        //-----------------------

        //-----------------------
        $description = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Parts Compatibility Synchronization" action for marketplace "%mrk%" has been successfully completed.',
            array('mrk'=>$marketplace->getTitle())
        );
        $this->_logs->addMessage(
            $description,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
        //-----------------------
    }

    private function addPercent($percent)
    {
        $current = $this->_lockItem->getPercents();
        $this->setPercent($current + $percent);
    }

    private function setPercent($percent)
    {
        $this->_lockItem->setPercents($percent);
        $this->_lockItem->activate();
    }

    private function setLockItemStatus($status, $search, $replace)
    {
        $description = str_replace($search, $replace, Mage::helper('M2ePro')->__($status));
        $description = str_replace('%synch_name%', $this->synchName, $description);
        $this->_lockItem->setStatus($description);
    }
}