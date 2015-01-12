<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_General extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('generalHtml');
        //------------------------------

        $this->setTemplate('M2ePro/general.phtml');
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->block_notices_show = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/', 'show_block_notices'
        );
        //----------------------------

        $this->initM2eProInfo();
        $this->initAnalyticData();

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function initM2eProInfo()
    {
        $this->m2epro_info = array(
            'platform' => array(
                'name' => Mage::helper('M2ePro/Magento')->getName().' ('.
                          Mage::helper('M2ePro/Magento')->getEditionName().')',
                'version' => Mage::helper('M2ePro/Magento')->getVersion(),
                'revision' => Mage::helper('M2ePro/Magento')->getRevision(),
            ),
            'module' => array(
                'name' => Mage::helper('M2ePro/Module')->getName(),
                'version' => Mage::helper('M2ePro/Module')->getVersion(),
                'revision' => Mage::helper('M2ePro/Module')->getRevision()
            ),
            'location' => array(
                'domain' => Mage::helper('M2ePro/Client')->getDomain(),
                'ip' => Mage::helper('M2ePro/Client')->getIp(),
                'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
            ),
            'locale' => Mage::helper('M2ePro/Magento')->getLocale()
        );
    }

    protected function initAnalyticData()
    {
        $this->analytic = array(
            'mode' => false
        );

        if (Mage::helper('M2ePro/Module_Analytic')->isNavigationModeDisabled() &&
            Mage::helper('M2ePro/Module_Analytic')->isActionModeDisabled()) {
            return;
        }

        if (!Mage::helper('M2ePro/Module_Analytic')->isViewBoth() &&
            Mage::helper('M2ePro/Module_Analytic')->getView() != Mage::helper('M2ePro/View')->getCurrentView()) {
            return;
        }

        $analytic = array(
            'mode' => true,
            'url' => Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'url'),
            'navigation_mode' => Mage::helper('M2ePro/Module_Analytic')->getNavigationMode(),
            'action_mode' => Mage::helper('M2ePro/Module_Analytic')->getActionMode()
        );

        $mageParamsString = '';
        foreach ($this->getRequest()->getUserParams() as $paramKey => $paramValue) {
            $mageParamsString .= '/'.$paramKey.'/'.$paramValue;
        }

        $analytic['data'] = array(
            'identifier' => Mage::helper('M2ePro')->generateUniqueHash(Mage::helper('M2ePro/Client')->getIp()),
            'session_id' => Mage::getSingleton('core/session')->getSessionId(),
            'controller' => $this->getRequest()->getControllerName(),
            'action' => $this->getRequest()->getActionName(),
            'mage_params' => !empty($mageParamsString) ? $mageParamsString.'/' : '',
        );

        if (Mage::helper('M2ePro/Module_Analytic')->isNavigationModeAdvanced()) {
            $analytic['additional_data'] = array(
                'get_params' => (isset($_GET) && is_array($_GET)) ? http_build_query($_GET,'','&') : '',
                'post_params' => (isset($_POST) && is_array($_POST)) ? http_build_query($_POST,'','&') : '',
                //'cookies' => $this->getRequest()->getCookie(),
                //'session' => Mage::getSingleton('core/session')->getData()
            );
        }

        $this->analytic = $analytic;
    }

    // ########################################
}