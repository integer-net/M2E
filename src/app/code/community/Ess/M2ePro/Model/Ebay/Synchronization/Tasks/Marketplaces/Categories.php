<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces_Categories
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 25;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 75;

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
        $this->_profiler->addTitle($componentName.'Categories Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Categories Synchronization" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Categories Synchronization" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        if (!empty($this->_params['marketplace_id'])) {
            $markeptlaceObj = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Marketplace',$this->_params['marketplace_id']);

            if ($markeptlaceObj->getComponentMode() != Ess_M2ePro_Helper_Component_Ebay::NICK) {
                return;
            }
        }

        // Prepare MySQL data
        //-----------------------
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');
        //-----------------------

        // Get marketplaces
        //-----------------------
        $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')->getModel('Marketplace')
                ->getCollection()
                ->addFieldToFilter('status',Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                ->setOrder('sorder','ASC')
                ->setOrder('title','ASC');

        if (isset($this->_params['marketplace_id'])) {
            $marketplacesCollection->addFieldToFilter('id',(int)$this->_params['marketplace_id']);
        }

        $marketplaces = $marketplacesCollection->getItems();

        if (count($marketplaces) == 0) {
            return;
        }

        if (isset($this->_params['marketplace_id'])) {
            foreach ($marketplaces as $marketplace) {
                $componentName = '';
                if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
                    $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
                }
                /** @var $marketplace Ess_M2ePro_Model_Marketplace */
                $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.$marketplace->getTitle()));
            }
        }
        //-----------------------

        // Get and update categories
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / (count($marketplaces)*10);

        foreach ($marketplaces as $marketplace) {

            /** @var $marketplace Ess_M2ePro_Model_Marketplace */

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            $this->_profiler->addTitle('Starting marketplace "'.$marketplace->getTitle().'"');

            $this->_profiler->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get categories from eBay');

            // ->__('The "Categories Synchronization" action for marketplace: "%s" is started. Please wait...')
            $status = 'The "Categories Synchronization" action for marketplace: "%s" is started. Please wait...';
            $tempString =  Mage::helper('M2ePro')->__($status, Mage::helper('M2ePro')->__($marketplace->getTitle()));
            $this->_lockItem->setStatus($tempString);

            // Create connector
            //-----------------------
            $categories = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                            ->processVirtualAbstract('marketplace','get','info',
                                                     array('include_categories'=>1),'info',
                                                     $marketplace->getId(),NULL,NULL);
            if (is_null($categories)) {
                $categories = array();
            } else {
                $categories = $categories['categories'];
            }
            //-----------------------

            $this->_profiler->addTitle('Total receive categories "'.count($categories).'"');
            $this->_profiler->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;

            $this->_profiler->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save categories to DB');

            $status = <<<STATUS
The "Categories Synchronization" action for marketplace: "%s" is in data processing mode. Please wait...
STATUS;
            $tempString = Mage::helper('M2ePro')->__($status, Mage::helper('M2ePro')->__($marketplace->getTitle()));
            $this->_lockItem->setStatus($tempString);

            // Save categories
            //-----------------------
            Mage::getSingleton('core/resource')->getConnection('core_write')
                ->delete($tableCategories,array('marketplace_id = ?'=>$marketplace->getId()));

            $tempIterationLast = 0;
            $tempIterationTotal = 0;
            $tempCategoriesForOnePercent = count($categories)/($percentsForStep*8);

            foreach ($categories as &$data) {
                $data['features'] = json_encode($data['features']);
                $data['marketplace_id'] = $marketplace->getId();
                $connWrite->insertOnDuplicate($tableCategories, $data);
                if ($tempIterationTotal - $tempIterationLast > 250) {
                    $tempIterationLast = $tempIterationTotal;
                    $tempPercent = (int)($tempIterationTotal/$tempCategoriesForOnePercent);
                    $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep + $tempPercent);
                    $this->_lockItem->activate();
                }
                $tempIterationTotal++;
            }
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration+=8;
        }
        //-----------------------

        // Send success message
        //-----------------------
        $logMarketplacesString = '';
        foreach ($marketplaces as $marketplace) {
            if ($logMarketplacesString != '') {
                $logMarketplacesString .= ', ';
            }
            $logMarketplacesString .= $marketplace->getTitle();
        }

        // ->__('The "Categories Synchronization" action for marketplace: "%mrk%" has been successfully completed.');
        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories Synchronization" action for marketplace: "%mrk%" has been successfully completed.',
            array('mrk'=>$logMarketplacesString)
        );
        $this->_logs->addMessage($tempString,
                                 Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                 Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
        //-----------------------
    }

    //####################################
}