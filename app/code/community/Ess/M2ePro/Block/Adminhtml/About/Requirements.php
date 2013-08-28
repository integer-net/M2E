<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_Requirements extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('systemRequirements');
        //------------------------------

        $this->setTemplate('M2ePro/about/requirements.phtml');
    }

    protected function _beforeToHtml()
    {
        $serverPhpData = Mage::helper('M2ePro/Server')->getPhpSettings();

        //------------------------------
        $temp['recommended'] = '5.3.0';
        $temp['value'] = Mage::helper('M2ePro/Server')->getPhpVersion();
        $temp['validation'] = version_compare($temp['value'], $temp['recommended'], '>=');
        $this->php_version = $temp;
        //------------------------------

        //------------------------------
        $compareTo = '1.4.0.0';
        Mage::helper('M2ePro/Magento')->isGoEdition() && $compareTo = '1.9.0.0';
        Mage::helper('M2ePro/Magento')->isProfessionalEdition() && $compareTo = '1.7.0.0';
        Mage::helper('M2ePro/Magento')->isEnterpriseEdition() && $compareTo = '1.7.0.0';

        $temp['recommended'] = $compareTo;
        $temp['value'] = Mage::helper('M2ePro/Magento')->getVersion(false);
        $temp['validation'] = version_compare($temp['value'], $compareTo, '>=');
        $this->magento_version = $temp;
        //------------------------------

        //------------------------------
        $temp['recommended'] = 256;
        $temp['value'] = (int)$serverPhpData['memory_limit'];
        $temp['validation'] = $temp['value'] >= $temp['recommended'];
        $this->memory_limit = $temp;
        //------------------------------

        //------------------------------
        $max_execution_time_value = (int)$serverPhpData['max_execution_time'];
        $temp['recommended'] = 360;
        if ($max_execution_time_value <= 0) {
            $temp['value'] = Mage::helper('M2ePro')->__('unlimited');
            $temp['validation'] = true;
        } else {
            $temp['value'] = $max_execution_time_value;
            $temp['validation'] = $max_execution_time_value >= $temp['recommended'];
        }
        $this->max_execution_time = $temp;
        //------------------------------

        //------------------------------
        $temp['recommended'] = Mage::helper('M2ePro')->__('enabled');
        $temp['validation'] = function_exists('json_encode');
        $temp['value'] = ($temp['validation']
                            ? Mage::helper('M2ePro')->__('enabled')
                            : Mage::helper('M2ePro')->__('disabled'));
        $this->json = $temp;
        //------------------------------

        //------------------------------
        $temp['recommended'] = Mage::helper('M2ePro')->__('enabled');
        $temp['validation'] = function_exists('curl_init');
        $temp['value'] = ($temp['validation']
                            ? Mage::helper('M2ePro')->__('enabled')
                            : Mage::helper('M2ePro')->__('disabled'));
        $this->curl = $temp;
        //------------------------------

        //------------------------------
        //$temp['recommended'] = Mage::helper('M2ePro')->__('enabled');
        //$temp['validation'] = extension_loaded('gmp');
        //$temp['value'] = ($temp['validation']
        //    ? Mage::helper('M2ePro')->__('enabled')
        //    : Mage::helper('M2ePro')->__('disabled'));
        //$this->gmp = $temp;
        //------------------------------

        return parent::_beforeToHtml();
    }
}