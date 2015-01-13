<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Exceptions extends Ess_M2ePro_Model_Servicing_Task
{
    /** @var Varien_Db_Adapter_Pdo_Mysql */
    private $connectionRead = NULL;
    /** @var Varien_Db_Adapter_Pdo_Mysql */
    private $connectionWrite = NULL;

    private $tableName = NULL;
    private $separatorHash = NULL;

    // ########################################

    public function getPublicNick()
    {
        return 'exceptions';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        $this->initResponseData();
        $data = $this->prepareAndCheckReceivedData($data);

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/debug/exceptions/','filters_mode',(int)$data['is_filter_enable']
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/debug/fatal_error/','send_to_server',(int)$data['send_to_server']['fatal']
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/debug/exceptions/','send_to_server',(int)$data['send_to_server']['exception']
        );

        $receivedFilters = $data['filters'];
        $currentFilters = $this->getCurrentFilters();

        $this->addNewReceivedFilters($currentFilters,$receivedFilters);
        $this->clearRemovedFilters($currentFilters,$receivedFilters);
    }

    // ########################################

    private function initResponseData()
    {
        $this->tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_exceptions_filters');
        $this->separatorHash = sha1(rand(0,1000000).microtime(true));

        $this->connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    // ########################################

    private function prepareAndCheckReceivedData($data)
    {
        //-- Send To Server
        // -------------------------------------
        if (!isset($data['send_to_server']['fatal']) || !is_bool($data['send_to_server']['fatal'])) {
            $data['send_to_server']['fatal'] = true;
        }
        if (!isset($data['send_to_server']['exception']) || !is_bool($data['send_to_server']['exception'])) {
            $data['send_to_server']['exception'] = true;
        }
        // -------------------------------------

        //-- Exceptions Filters
        // -------------------------------------
        if (!isset($data['is_filter_enable']) || !is_bool($data['is_filter_enable'])) {
            $data['is_filter_enable'] = false;
        }

        if (!isset($data['filters']) || !is_array($data['filters'])) {
            $data['filters'] = array();
        }

        $validatedFilters = array();

        $allowedFilterTypes = array(
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_TYPE,
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_INFO
        );

        foreach ($data['filters'] as $filter) {

            if (!isset($filter['preg_match']) || $filter['preg_match'] == '' ||
                !in_array($filter['type'],$allowedFilterTypes)) {
                continue;
            }

            $validatedFilters[] = $filter;
        }

        $data['filters'] = $validatedFilters;
        // -------------------------------------

        return $data;
    }

    private function getCurrentFilters()
    {
        $result = array();

        $stmtQuery = $this->connectionRead
                          ->select()
                          ->from($this->tableName,array('id','preg_match','type'))
                          ->query();

        while ($filter = $stmtQuery->fetch()) {
            $result[$filter['id']] = $filter['preg_match'].$this->separatorHash.$filter['type'];
        }

        return $result;
    }

    // ########################################

    private function addNewReceivedFilters($currentFilters, $receivedFilters)
    {
        $calculatedFilters = $this->getFiltersForAdding($currentFilters, $receivedFilters);

        if (count($calculatedFilters) <= 0) {
            return;
        }

        $this->connectionWrite->insertMultiple($this->tableName,$calculatedFilters);
    }

    private function clearRemovedFilters($currentFilters, $receivedFilters)
    {
        $calculatedFiltersIds = $this->getFiltersForDeleting($currentFilters, $receivedFilters);

        if (count($calculatedFiltersIds) <= 0) {
            return;
        }

        $this->connectionWrite->delete($this->tableName,'`id` IN ('.implode(',',$calculatedFiltersIds).')');
    }

    //-----------------------------------------

    private function getFiltersForAdding($currentFilters, $receivedFilters)
    {
        $result = array();

        foreach ($receivedFilters as $receivedFilter) {

            $tempIndexKey = $receivedFilter['preg_match'].$this->separatorHash.$receivedFilter['type'];

            if (!in_array($tempIndexKey,$currentFilters)) {
                $receivedFilter['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $receivedFilter['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $result[] = $receivedFilter;
            }
        }

        return $result;
    }

    private function getFiltersForDeleting($currentFilters, $receivedFilters)
    {
        $result = array();

        foreach ($currentFilters as $currentFilterId => $currentFilterKey) {

            $found = false;

            foreach ($receivedFilters as $receivedFilter) {

                $tempIndexKey = $receivedFilter['preg_match'].$this->separatorHash.$receivedFilter['type'];

                if ($currentFilterKey == $tempIndexKey) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $result[] = (int)$currentFilterId;
            }
        }

        return $result;
    }

    // ########################################
}