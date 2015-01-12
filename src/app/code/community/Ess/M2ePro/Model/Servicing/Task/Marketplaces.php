<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Marketplaces extends Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'marketplaces';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        if (isset($data['categories_versions']) && is_array($data['categories_versions'])) {
            $this->processCategoriesVersions($data['categories_versions']);
        }
    }

    // ########################################

    protected function processCategoriesVersions($versions)
    {
        $enabledMarketplaces = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Marketplace')->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dictionaryTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        /* @var $marketplace Ess_M2ePro_Model_Marketplace */
        foreach ($enabledMarketplaces as $marketplace) {

            if (!isset($versions[$marketplace->getNativeId()])) {
                continue;
            }

            $serverVersion = (int)$versions[$marketplace->getNativeId()];

            $expr = "IF(client_categories_version is NULL,{$serverVersion},client_categories_version)";

            $writeConn->update(
                $dictionaryTable,
                array(
                    'server_categories_version' => $serverVersion,
                    'client_categories_version' => new Zend_Db_Expr($expr)
                ),
                array('marketplace_id = ?' => $marketplace->getId())
            );
        }
    }

    // ########################################
}