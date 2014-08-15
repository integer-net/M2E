<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Analytic implements Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'analytic';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        if (isset($data['url']) && !empty($data['url'])) {
            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/view/analytic/', 'url', $data['url']);
        }

        $validValues = array(
            Ess_M2ePro_Helper_Module_Analytic::VIEW_BOTH,
            Ess_M2ePro_Helper_Module_Analytic::VIEW_EBAY,
            Ess_M2ePro_Helper_Module_Analytic::VIEW_COMMON
        );

        if (isset($data['view']) && in_array($data['view'],$validValues)) {
            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/view/analytic/', 'view', $data['view']);
        }

        $validValues = array(
            Ess_M2ePro_Helper_Module_Analytic::NAVIGATION_MODE_DISABLED,
            Ess_M2ePro_Helper_Module_Analytic::NAVIGATION_MODE_STANDARD,
            Ess_M2ePro_Helper_Module_Analytic::NAVIGATION_MODE_ADVANCED
        );

        if (isset($data['navigation_mode']) && in_array($data['navigation_mode'],$validValues)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                '/view/analytic/', 'navigation_mode', $data['navigation_mode']
            );
        }

        $validValues = array(
            Ess_M2ePro_Helper_Module_Analytic::ACTION_MODE_DISABLED,
            Ess_M2ePro_Helper_Module_Analytic::ACTION_MODE_ALL,
            Ess_M2ePro_Helper_Module_Analytic::ACTION_MODE_SPECIAL
        );

        if (isset($data['action_mode']) && in_array($data['action_mode'],$validValues)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                '/view/analytic/', 'action_mode', $data['action_mode']
            );
        }
    }

    // ########################################
}