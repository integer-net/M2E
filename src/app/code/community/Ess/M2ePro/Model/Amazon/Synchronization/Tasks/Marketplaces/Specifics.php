<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Marketplaces_Specifics extends
                                                                        Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 75;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 25;

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
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Specifics Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Receive Specifics" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Receive Specifics" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $markeptlaceObj = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',
                                                                             $this->_params['marketplace_id']);
        if ($markeptlaceObj->getComponentMode() != Ess_M2ePro_Helper_Component_Amazon::NICK) {
            return;
        }

        // Prepare MySQL data
        //-----------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableSpecifics = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');
        //-----------------------

        // Get marketplaces
        //-----------------------
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Marketplace', $this->_params['marketplace_id']
        );

        $componentName = '';
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        }
        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.$marketplace->getTitle()));
        //-----------------------

        // Get and update specifics
        //-----------------------
        $this->_profiler->addTitle('Starting marketplace "'.$marketplace->getTitle().'"');

        $this->_profiler->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get specifics from Amazon');

        $temp = Mage::helper('M2ePro')->__(
            'The "Specifics Synchronization" action for marketplace: "%s" is started. Please wait...',
            Mage::helper('M2ePro')->__($marketplace->getTitle())
        );
        $this->_lockItem->setStatus($temp);

        // Create connector
        //-----------------------
        $specifics = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher')
                            ->processVirtualAbstract('marketplace','get','info',
                                                      array('include_specifics' => true,
                                                            'marketplace' => $marketplace->getNativeId()),
                                                      'info',
                                                      NULL,NULL);
        if (is_null($specifics)) {
            $specifics = array();
        } else {
            $specifics = $specifics['specifics'];
        }
        //-----------------------

        $this->_profiler->addTitle('Total receive specifics "'.count($specifics).'"');
        $this->_profiler->saveTimePoint(__METHOD__.'get'.$marketplace->getId());
        $this->_profiler->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save specifics to DB');

        $temp = Mage::helper('M2ePro')->__(
            'The "Specifics Synchronization" action for marketplace: "%s" is in data processing mode. Please wait...',
            Mage::helper('M2ePro')->__($marketplace->getTitle())
        );
        $this->_lockItem->setStatus($temp);

        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');
        $nodesData = $connRead->select()
            ->from($tempTable,'nodes')
            ->where('marketplace_id = ?',$marketplace->getId())
            ->query()->fetchColumn();

        $nodesData = json_decode($nodesData,true);

        $marketplaceXsds = array();

        foreach ($nodesData as $nodeData) {
            foreach ($nodeData['xsds'] as $xsd) {
                $marketplaceXsds[] = $connRead->quote($xsd['hash']);
            }
        }

        array_unique($marketplaceXsds);

        // Save specifics
        //-----------------------
        if (count($marketplaceXsds) > 0) {
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($tableSpecifics,array('xsd_hash IN ('.implode(',',$marketplaceXsds).')'));
        }

        $tempIterationTotal = 0;
        $tempSpecificsForOnePercent = count($specifics) / self::PERCENTS_INTERVAL;

        foreach ($specifics as &$data) {
            $connWrite->insert($tableSpecifics, $data);
            if ($tempIterationTotal % $tempSpecificsForOnePercent == 0) {
                $this->_lockItem->setPercents(
                    (int)(self::PERCENTS_START + $tempIterationTotal/$tempSpecificsForOnePercent)
                );
                $this->_lockItem->activate();
            }
            $tempIterationTotal++;
        }
        //-----------------------

        $this->_profiler->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        //-----------------------

        // Send success message
        //-----------------------
        $logMarketplacesString = $marketplace->getTitle();

        //->__('The "Specifics Synchronization" action for marketplace: "%mrk%" has been successfully completed.');
        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Specifics Synchronization" action for marketplace: "%mrk%" has been successfully completed.',
            array('mrk'=>$logMarketplacesString)
        );
        $this->_logs->addMessage($tempString,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
        //-----------------------
    }

    //####################################
}