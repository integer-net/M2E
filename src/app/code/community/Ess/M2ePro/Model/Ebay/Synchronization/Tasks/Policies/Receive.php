<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Policies_Receive
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    protected $synchName = 'Receive Business Policies Synchronization';

    // long translations
    // ->__('The "Business Policies Receive" action for eBay Site: "%s" and Account: "%s" is started. Please wait...')

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
        $this->_profiler->addTitle($componentName.$this->synchName);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "'.$this->synchName.'" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "'.$this->synchName.'" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        if (!empty($this->_params['account_id'])) {
            $accountObj = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Account', $this->_params['account_id']);

            if (!$accountObj->isComponentModeEbay()) {
                return;
            }
        }

        if (!empty($this->_params['marketplace_id'])) {
            $marketplaceObj = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Marketplace', $this->_params['marketplace_id']);

            if (!$marketplaceObj->isComponentModeEbay()) {
                return;
            }
        }

        // Get marketplaces
        //-----------------------
        $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Marketplace');
        $marketplacesCollection
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC');

        if (isset($this->_params['marketplace_id'])) {
            $marketplacesCollection->addFieldToFilter('id', (int)$this->_params['marketplace_id']);
        }

        $marketplaces = $marketplacesCollection->getItems();

        if (count($marketplaces) == 0) {
            return;
        }
        //------------------------------

        // Get accounts
        //------------------------------
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Account');

        if (isset($this->_params['account_id'])) {
            $accountsCollection->addFieldToFilter('id', (int)$this->_params['account_id']);
        }

        $accounts = $accountsCollection->getItems();

        if (count($accounts) == 0) {
            return;
        }
        //------------------------------

        // Get and update policies
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / ((count($marketplaces) + count($accounts)) * 10);

        foreach ($marketplaces as $marketplace) {

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            $this->_profiler->addTitle('Starting marketplace "'.$marketplace->getTitle().'"');

            //------------------------------
            foreach ($accounts as $account) {
                $this->_profiler->addTimePoint(
                    __METHOD__.'get'.$marketplace->getId().$account->getId(),
                    'Get business policies from eBay for Account "' . $account->getTitle() . '"'
                );

                $status = 'The "Business Policies Receive" action for eBay Site: "%s" and Account: "%s" is started.'
                    . ' Please wait...';
                $status = sprintf($status, $marketplace->getTitle(), $account->getTitle());
                $this->_lockItem->setStatus($status);

                // Create connector
                //-----------------------
                $policies = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                    ->processVirtualAbstract(
                        'account', 'get', 'policies',
                        array(),
                        'policies',
                        $marketplace->getId(),
                        $account->getId(),
                        NULL
                    );

                if (is_null($policies)) {
                    $policies = array();
                }
                //-----------------------

                $this->_profiler->addTitle('Total received policies "'.count($policies).'"');
                $this->_profiler->saveTimePoint(__METHOD__.'get'.$marketplace->getId().$account->getId());

                $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
                $this->_lockItem->activate();
                $iteration++;

                $this->_profiler->addTimePoint(
                    __METHOD__.'save'.$marketplace->getId().$account->getId(),
                    'Save policies to DB'
                );

                // Save policies
                //-----------------------
                Mage::getSingleton('core/resource')
                    ->getConnection('core_write')
                        ->delete(
                            Mage::getResourceModel('M2ePro/Ebay_Account_Policy')->getMainTable(),
                            array(
                                'marketplace_id = ?' => $marketplace->getId(),
                                'account_id = ?' => $account->getId()
                            )
                        );

                $insertData = array();

                foreach ($policies as $policy) {
                    $insertData[] = array(
                        'account_id' => $account->getId(),
                        'marketplace_id' => $marketplace->getId(),
                        'type' => $policy['type'],
                        'api_name' => $policy['name'],
                        'api_identifier' => $policy['id'],
                        'api_info' => json_encode($policy['info']),
                    );
                }

                unset($policies);

                if (count($insertData) > 0) {
                    Mage::getSingleton('core/resource')
                        ->getConnection('core_write')
                        ->insertArray(
                            Mage::getResourceModel('M2ePro/Ebay_Account_Policy')->getMainTable(),
                            array('account_id', 'marketplace_id', 'type', 'api_name', 'api_identifier', 'api_info'),
                            $insertData
                        );
                }
                //-----------------------

                $this->_profiler->saveTimePoint( __METHOD__.'save'.$marketplace->getId().$account->getId());
            }
            //------------------------------

            $this->_profiler->addTitle('Ending marketplace "'.$marketplace->getTitle().'"');

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration+=8;
        }
        //-----------------------
    }

    //####################################
}