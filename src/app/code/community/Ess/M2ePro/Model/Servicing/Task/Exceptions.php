<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Exceptions extends Ess_M2ePro_Model_Servicing_Task
{
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

        /**  @var $registryModel Ess_M2ePro_Model_Registry */
        $registryModel = Mage::getModel('M2ePro/Registry')->load('/exceptions_filters/', 'key');

        $registryModel->addData(array(
            'key' => '/exceptions_filters/',
            'value' => json_encode($data['filters'])
        ))->save();
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
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_INFO,
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_MESSAGE
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

    // ########################################
}