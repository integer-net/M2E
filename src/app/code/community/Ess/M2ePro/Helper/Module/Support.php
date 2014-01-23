<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 * Shipping method with custom title and price
 */

class Ess_M2ePro_Helper_Module_Support extends Mage_Core_Helper_Abstract
{
    //#############################################

    public function getPageUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl($this->getPageRoute(), $params);
    }

    public function getPageRoute()
    {
        return 'M2ePro/'.$this->getPageControllerName().'/index';
    }

    public function getPageControllerName()
    {
        return 'adminhtml_support';
    }

    //#############################################

    public function getDocumentationUrl($view = NULL)
    {
        is_null($view) && $view = Mage::helper('M2ePro/View')->getCurrentView();

        switch ($view) {
            case Ess_M2ePro_Helper_View_Common::NICK:
                return Mage::helper('M2ePro/View_Common')->getDocumentationUrl();

            case Ess_M2ePro_Helper_View_Ebay::NICK:
            default:
                return Mage::helper('M2ePro/View_Ebay')->getDocumentationUrl();
        }
    }

    public function getVideoTutorialsUrl($view = NULL)
    {
        is_null($view) && $view = Mage::helper('M2ePro/View')->getCurrentView();

        switch ($view) {
            case Ess_M2ePro_Helper_View_Common::NICK:
                return Mage::helper('M2ePro/View_Common')->getVideoTutorialsUrl();

            case Ess_M2ePro_Helper_View_Ebay::NICK:
            default:
                return Mage::helper('M2ePro/View_Ebay')->getVideoTutorialsUrl();
        }
    }

    //#############################################

    public function getKnowledgeBaseUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'knowledge_base_url');
    }

    //----------------------------------

    public function getClientsPortalBaseUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'clients_portal_url');
    }

    public function getClientsPortalUrl()
    {
        return $this->getClientsPortalBaseUrl().'?version='.Mage::helper('M2ePro/Module')->getVersion();
    }

    //----------------------------------

    public function getMainWebsiteUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'main_website_url');
    }

    public function getMainSupportUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'main_support_url');
    }

    public function getMagentoConnectUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'magento_connect_url');
    }

    //#############################################

    public function getContactEmail()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'contact_email');
    }

    //#############################################
}