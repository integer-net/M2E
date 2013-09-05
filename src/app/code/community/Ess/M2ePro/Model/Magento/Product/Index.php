<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */
class Ess_M2ePro_Model_Magento_Product_Index
{
    /** @var Mage_Index_Model_Indexer */
    private $indexer = null;

    /** @var Ess_M2ePro_Model_Config_Module */
    private $config = null;
    private $configGroup = '/product/index/';

    private static $switchableIndexes = array(
        'cataloginventory_stock'
    );

    private static $requiredIndexes = array(
        'cataloginventory_stock',
        'catalog_product_attribute',
        'catalog_product_price'
    );

    public function __construct()
    {
        $this->indexer = Mage::getSingleton('index/indexer');
        $this->config  = Mage::helper('M2ePro/Module')->getConfig();
    }

    public function getSwitchableIndexes()
    {
        return self::$switchableIndexes;
    }

    public function getRequiredIndexes()
    {
        return self::$requiredIndexes;
    }

    public function hasDisabledSwitchableIndexes()
    {
        foreach (self::$switchableIndexes as $code) {
            $disabled = (bool)(int)$this->getCacheValue($code, 'disabled');

            if ($disabled) {
                return true;
            }
        }

        return false;
    }

    public function disableAutomaticReindex($code)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = $this->indexer->getProcessByCode($code);

        if ($process === false) {
            return;
        }

        if ($process->getMode() == Mage_Index_Model_Process::MODE_MANUAL) {
            return;
        }

        $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();

        $this->setCacheValue($code, 'disabled', 1);
    }

    public function enableAutomaticReindex($code)
    {
        $disabled = (bool)(int)$this->getCacheValue($code, 'disabled');

        if (!$disabled) {
            // mode was not disabled by M2E Pro, skip action
            return;
        }

        /** @var $process Mage_Index_Model_Process */
        $process = $this->indexer->getProcessByCode($code);

        if ($process === false) {
            return;
        }

        if ($process->getMode() != Mage_Index_Model_Process::MODE_REAL_TIME) {
            $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
        }

        $this->setCacheValue($code, 'disabled', 0);
    }

    public function reindex($code)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = $this->indexer->getProcessByCode($code);

        if ($process === false) {
            return false;
        }

        /** @var $eventsCollection Mage_Index_Model_Resource_Event_Collection */
        $eventsCollection = Mage::getResourceModel('index/event_collection')
            ->addProcessFilter($process, Mage_Index_Model_Process::EVENT_STATUS_NEW);

        if ($eventsCollection->getSize() == 0) {
            return false;
        }

        $process->reindexEverything();

        return true;
    }

    private function setCacheValue($code, $key, $value)
    {
        $this->config->setGroupValue($this->configGroup.$code.'/', $key, $value);
        return $this;
    }

    private function getCacheValue($code, $key)
    {
        return $this->config->getGroupValue($this->configGroup.$code.'/', $key);
    }
}