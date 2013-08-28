<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces_Details
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 25;
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
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Details Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Receive Details" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Receive Details" action is finished. Please wait...')
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
        $tableMarketplaces = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');
        $tableShippings = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_shipping');
        $tableShippingsCategories = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_ebay_dictionary_shipping_category');
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

        // Get and update details
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / (count($marketplaces)*2);

        foreach ($marketplaces as $marketplace) {

            /** @var $marketplace Ess_M2ePro_Model_Marketplace */

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            $this->_profiler->addTitle('Starting marketplace "'.$marketplace->getTitle().'"');

            $this->_profiler->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get details from eBay');

            // ->__('The "Receive Details" action for marketplace: "%s" is started. Please wait...')
            $status = 'The "Receive Details" action for marketplace: "%s" is started. Please wait...';
            $tempString = Mage::helper('M2ePro')->__($status, Mage::helper('M2ePro')->__($marketplace->getTitle()));
            $this->_lockItem->setStatus($tempString);

            // Create connector
            //-----------------------
            $details = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                                ->processVirtualAbstract('marketplace','get','info',
                                                          array('include_details'=>1),'info',
                                                          $marketplace->getId(),NULL,NULL);
            if (is_null($details)) {
                $details = array();
            } else {
                $details = $details['details'];
            }
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;

            $this->_profiler->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save details to DB');

            // ->__('The "Receive Details" action for marketplace "%s" is in data processing mode. Please wait...')
            $status = 'The "Receive Details" action for marketplace "%s" is in data processing mode. Please wait...';
            $tempString = Mage::helper('M2ePro')->__($status, Mage::helper('M2ePro')->__($marketplace->getTitle()));
            $this->_lockItem->setStatus($tempString);

            // Save marketplaces
            //-----------------------
            Mage::getSingleton('core/resource')->getConnection('core_write')
                ->delete($tableMarketplaces,array('marketplace_id = ?'=>$marketplace->getId()));

            $data = array(
                'marketplace_id'      => $marketplace->getId(),
                'dispatch'            => json_encode($details['dispatch']),
                'packages'            => json_encode($details['packages']),
                'return_policy'       => json_encode($details['return_policy']),
                'listing_features'    => json_encode($details['listing_features']),
                'payments'            => json_encode($details['payments']),
                'shipping_locations'  => json_encode($details['shipping_locations']),
                'shipping_locations_exclude' => json_encode($details['shipping_locations_exclude']),
                'categories_features_defaults' => json_encode($details['categories_features_defaults']),
                'tax_categories' => json_encode($details['tax_categories'])
            );

            $connWrite->insertOnDuplicate($tableMarketplaces, $data);
            //-----------------------

            // Save shippings
            //-----------------------
            Mage::getSingleton('core/resource')->getConnection('core_write')
                ->delete($tableShippings,array('marketplace_id = ?'=>$marketplace->getId()));

            foreach ($details['shipping'] as $data) {
                $data['marketplace_id'] = $marketplace->getId();
                $connWrite->insertOnDuplicate($tableShippings, $data);
            }
            //-----------------------

            // Save shipping categories
            //-----------------------
            Mage::getSingleton('core/resource')->getConnection('core_write')
                ->delete($tableShippingsCategories,array('marketplace_id = ?'=>$marketplace->getId()));

            foreach ($details['shipping_categories'] as $data) {
                $data['marketplace_id'] = $marketplace->getId();
                $connWrite->insertOnDuplicate($tableShippingsCategories, $data);
            }
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;
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

        // ->__('The "Receive Details" action for marketplace: "%mrk%"  has been successfully completed.');
        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Receive Details" action for marketplace: "%mrk%"  has been successfully completed.',
            array('mrk'=>$logMarketplacesString)
        );
        $this->_logs->addMessage($tempString,
                                 Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                 Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
        //-----------------------
    }

    //####################################
}