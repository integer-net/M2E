<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Details
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/details/';
    }

    protected function getTitle()
    {
        return 'Details';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 25;
    }

    //####################################

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualOperationHistory()->addText('Starting marketplace "'.$marketplace->getTitle().'"');

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get details from eBay');
        $details = $this->receiveFromEbay($marketplace);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->getActualLockItem()->activate();

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save details to DB');
        $this->saveDetailsToDb($marketplace,$details);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        $this->logSuccessfulOperation($marketplace);
    }

    //####################################

    protected function receiveFromEbay(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $details = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                            ->processVirtual('marketplace','get','info',
                                             array('include_details'=>1),'info',
                                             $marketplace->getId(),NULL,NULL);
        if (is_null($details)) {
            $details = array();
        } else {
            $details['details']['categories_version'] = $details['categories_version'];
            $details = $details['details'];
        }

        return $details;
    }

    protected function saveDetailsToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $details)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $coreResourceModel = Mage::getSingleton('core/resource');

        $tableMarketplaces = $coreResourceModel->getTableName('m2epro_ebay_dictionary_marketplace');
        $tableShipping = $coreResourceModel->getTableName('m2epro_ebay_dictionary_shipping');
        $tableShippingCategories = $coreResourceModel->getTableName('m2epro_ebay_dictionary_shipping_category');

        // Save marketplaces
        //-----------------------
        $connWrite->delete($tableMarketplaces, array('marketplace_id = ?' => $marketplace->getId()));

        $insertData = array(
            'marketplace_id'               => $marketplace->getId(),
            'client_categories_version'    => $details['categories_version'],
            'server_categories_version'    => $details['categories_version'],
            'dispatch'                     => json_encode($details['dispatch']),
            'packages'                     => json_encode($details['packages']),
            'return_policy'                => json_encode($details['return_policy']),
            'listing_features'             => json_encode($details['listing_features']),
            'payments'                     => json_encode($details['payments']),
            'shipping_locations'           => json_encode($details['shipping_locations']),
            'shipping_locations_exclude'   => json_encode($details['shipping_locations_exclude']),
            'categories_features_defaults' => json_encode($details['categories_features_defaults']),
            'tax_categories'               => json_encode($details['tax_categories']),
            'charities'                    => json_encode($details['charities']),
        );

        unset($details['categories_version']);
        $connWrite->insert($tableMarketplaces, $insertData);
        //-----------------------

        // Save shipping
        //-----------------------
        $connWrite->delete($tableShipping, array('marketplace_id = ?' => $marketplace->getId()));

        foreach ($details['shipping'] as $data) {
            $insertData = array(
                'marketplace_id'   => $marketplace->getId(),
                'ebay_id'          => $data['ebay_id'],
                'title'            => $data['title'],
                'category'         => $data['category'],
                'is_flat'          => $data['is_flat'],
                'is_calculated'    => $data['is_calculated'],
                'is_international' => $data['is_international'],
                'data'             => $data['data']
            );
            $connWrite->insert($tableShipping, $insertData);
        }
        //-----------------------

        // Save shipping categories
        //-----------------------
        $connWrite->delete($tableShippingCategories, array('marketplace_id = ?' => $marketplace->getId()));

        foreach ($details['shipping_categories'] as $data) {
            $insertData = array(
                'marketplace_id' => $marketplace->getId(),
                'ebay_id'        => $data['ebay_id'],
                'title'          => $data['title']
            );
            $connWrite->insert($tableShippingCategories, $insertData);
        }
        //-----------------------
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // ->__('The "Details" action for eBay Site: "%mrk%" has been successfully completed.');

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Details" action for eBay Site: "%mrk%" has been successfully completed.',
            array('mrk'=>$marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}