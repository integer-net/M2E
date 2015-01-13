<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Policies_Receive
    extends Ess_M2ePro_Model_Ebay_Synchronization_Policies_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/receive/';
    }

    protected function getTitle()
    {
        return 'Receive';
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

        if (!empty($params['account_id'])) {

            /** @var $account Ess_M2ePro_Model_Account **/
            $account = Mage::helper('M2ePro/Component')
                            ->getUnknownObject('Account', (int)$params['account_id']);

            if (!$account->isComponentModeEbay()) {
                return false;
            }
        }

        if (!empty($params['marketplace_id'])) {

            /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
            $marketplace = Mage::helper('M2ePro/Component')
                                ->getUnknownObject('Marketplace', (int)$params['marketplace_id']);

            if (!$marketplace->isComponentModeEbay()) {
                return false;
            }
        }

        return true;
    }

    protected function performActions()
    {
        $marketplaces = $this->getPermittedMarketplaces();

        if (count($marketplaces) <= 0) {
            return;
        }

        $accounts = $this->getPermittedAccounts();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / (count($marketplaces) * count($accounts));

        foreach ($marketplaces as $marketplace) {

            /** @var $marketplace Ess_M2ePro_Model_Marketplace **/

            $this->getActualOperationHistory()->addText('Starting marketplace "'.$marketplace->getTitle().'"');

            foreach ($accounts as $account) {

                /** @var $account Ess_M2ePro_Model_Account **/

                // M2ePro_TRANSLATIONS
                // The "Business Policies Receive" action for eBay Site: "%marketplace%" and Account: "%account_title%" is finished. Please wait...
                $this->getActualOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
                $status = 'The "Business Policies Receive" action for eBay Site: "%marketplace%" and Account: ';
                $status .= '"%account_title%" is started. Please wait...';
                $this->getActualLockItem()
                     ->setStatus(Mage::helper('M2ePro')->__($status, $marketplace->getTitle(), $account->getTitle()));

                $this->getActualOperationHistory()
                     ->addTimePoint(__METHOD__.'get'.$marketplace->getId().$account->getId(), 'Get policies from eBay');

                $this->processMarketplaceAccount($marketplace, $account);

                $this->getActualOperationHistory()
                     ->saveTimePoint(__METHOD__.'get'.$marketplace->getId().$account->getId());
                // M2ePro_TRANSLATIONS
                // The "Business Policies Receive" action for eBay Site: "%marketplace%" and Account: "%account_title%" is finished. Please wait...
                $status = 'The "Business Policies Receive" action for eBay Site: "%marketplace%" and Account: ';
                $status .= '"%account_title%" is finished. Please wait...';
                $this->getActualLockItem()
                     ->setStatus(Mage::helper('M2ePro')->__($status, $marketplace->getTitle(), $account->getTitle()));
                $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
                $this->getActualLockItem()->activate();

                $iteration++;
            }
        }
    }

    //####################################

    protected function getPermittedAccounts()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')
                                ->getCollection('Account');

        $params = $this->getParams();

        if (!empty($params['account_id'])) {
            $collection->addFieldToFilter('id', (int)$params['account_id']);
        }

        return $collection->getItems();
    }

    protected function getPermittedMarketplaces()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')
                                ->getCollection('Marketplace');

        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                   ->setOrder('sorder', 'ASC')
                   ->setOrder('title', 'ASC');

        $params = $this->getParams();

        if (!empty($params['marketplace_id'])) {
            $collection->addFieldToFilter('id', (int)$params['marketplace_id']);
        }

        return $collection->getItems();
    }

    // -----------------------------------

    protected function processMarketplaceAccount(Ess_M2ePro_Model_Marketplace $marketplace,
                                                 Ess_M2ePro_Model_Account $account)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $policies = $this->receiveFromEbay($marketplace, $account);

        $connWrite->delete(
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
            $connWrite->insertArray(
                Mage::getResourceModel('M2ePro/Ebay_Account_Policy')->getMainTable(),
                array('account_id', 'marketplace_id', 'type', 'api_name', 'api_identifier', 'api_info'),
                $insertData
            );
        }
    }

    protected function receiveFromEbay(Ess_M2ePro_Model_Marketplace $marketplace,
                                       Ess_M2ePro_Model_Account $account)
    {
        $policies = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                            ->processVirtual('account', 'get', 'policies',
                                             array(),'policies',
                                             $marketplace->getId(),$account->getId(),NULL);
        is_null($policies) && $policies = array();

        $this->getActualOperationHistory()->addText('Total received policies from eBay: '.count($policies));

        return $policies;
    }

    //####################################
}