<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento_Store extends Mage_Core_Helper_Abstract
{
    private $defaultWebsite = NULL;
    private $defaultStoreGroup = NULL;
    private $defaultStore = NULL;

    private $storeIdsByAttributeAndStore = array();

    // ########################################

    public function isSingleStoreMode()
    {
        return Mage::getModel('core/store')->getCollection()->getSize() <= 2;
    }

    public function isMultiStoreMode()
    {
        return !$this->isSingleStoreMode();
    }

    // ########################################

    public function getDefaultWebsite()
    {
        if (is_null($this->defaultWebsite)) {
            $this->defaultWebsite = Mage::getModel('core/website')->load(1,'is_default');
            if (is_null($this->defaultWebsite->getId())) {
                $this->defaultWebsite = Mage::getModel('core/website')->load(0);
                if (is_null($this->defaultWebsite->getId())) {
                    throw new Exception('Getting default website is failed');
                }
            }
        }
        return $this->defaultWebsite;
    }

    public function getDefaultStoreGroup()
    {
        if (is_null($this->defaultStoreGroup)) {

            $defaultWebsite = $this->getDefaultWebsite();
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->defaultStoreGroup = Mage::getModel('core/store_group')->load($defaultStoreGroupId);
            if (is_null($this->defaultStoreGroup->getId())) {
                $this->defaultStoreGroup = Mage::getModel('core/store_group')->load(0);
                if (is_null($this->defaultStoreGroup->getId())) {
                    throw new Exception('Getting default store group is failed');
                }
            }
        }
        return $this->defaultStoreGroup;
    }

    public function getDefaultStore()
    {
        if (is_null($this->defaultStore)) {

            $defaultStoreGroup = $this->getDefaultStoreGroup();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = Mage::getModel('core/store')->load($defaultStoreId);
            if (is_null($this->defaultStore->getId())) {
                $this->defaultStore = Mage::getModel('core/store')->load(0);
                if (is_null($this->defaultStore->getId())) {
                    throw new Exception('Getting default store is failed');
                }
            }
        }
        return $this->defaultStore;
    }

    //------------------------------------------

    public function getDefaultWebsiteId()
    {
        return (int)$this->getDefaultWebsite()->getId();
    }

    public function getDefaultStoreGroupId()
    {
        return (int)$this->getDefaultStoreGroup()->getId();
    }

    public function getDefaultStoreId()
    {
        return (int)$this->getDefaultStore()->getId();
    }

    // ########################################

    public function getStoreIdsByAttributeAndStore($attributeCode, $storeId)
    {
        $cacheKey = $attributeCode.'_'.$storeId;

        if (isset($this->storeIdsByAttributeAndStore[$cacheKey])) {
            return $this->storeIdsByAttributeAndStore[$cacheKey];
        }

        $attributeInstance = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);

        if (!($attributeInstance instanceof Mage_Catalog_Model_Resource_Eav_Attribute)) {
            return $this->storeIdsByAttributeAndStore[$cacheKey] = array();
        }

        $storeIds = array();

        switch ((int)$attributeInstance->getData('is_global')) {

            case Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL:
                foreach (Mage::app()->getWebsites() as $website) {
                    /** @var $website Mage_Core_Model_Website */
                    $storeIds = array_merge($storeIds,$website->getStoreIds());
                }
                break;

            case Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE:
                if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $storeIds = Mage::getModel('core/store')->load($storeId)->getWebsite()->getStoreIds();
                }
                break;

            case Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE:
                if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $storeIds = array($storeId);
                }
                break;
        }

        $storeIds = array_values(array_unique($storeIds));
        foreach ($storeIds as &$storeIdTemp) {
            $storeIdTemp = (int)$storeIdTemp;
        }

        return $this->storeIdsByAttributeAndStore[$cacheKey] = $storeIds;
    }

    // ########################################

    public function getStorePath($storeId)
    {
        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            return Mage::helper('M2ePro')->__('Admin (Default Values)');
        }

        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            return '';
        }

        $path = $store->getWebsite()->getName();
        $path .= ' -> ' . $store->getGroup()->getName();
        $path .= ' -> ' . $store->getName();

        return $path;
    }

    // ########################################

    public function getWebsite($storeId)
    {
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            return NULL;
        }

        return $store->getWebsite();
    }

    public function getWebsiteName($storeId)
    {
        $website = $this->getWebsite($storeId);

        return $website ? $website->getName() : '';
    }

    // ########################################
}