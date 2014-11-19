<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Configuration extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Configuration

    const NICK  = 'configuration';
    const TITLE = 'Configuration';

    const CONFIG_SECTION_COMPONENTS     = 'm2epro_components';
    const CONFIG_SECTION_SETTINGS       = 'm2epro_settings';
    const CONFIG_SECTION_LOGS_CLEANING  = 'm2epro_logs_cleaning';
    const CONFIG_SECTION_LICENSE        = 'm2epro_license';

   // ########################################

    public function getComponentsUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_COMPONENTS
        ), $params));
    }

    public function getSettingsUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_SETTINGS
        ), $params));
    }

    public function getLogsCleaningUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_LOGS_CLEANING
        ), $params));
    }

    public function getLicenseUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit',array_merge(array(
            'section' => self::CONFIG_SECTION_LICENSE
        ), $params));
    }

    // ########################################
}