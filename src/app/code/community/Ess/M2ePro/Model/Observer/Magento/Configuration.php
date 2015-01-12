<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Observer_Magento_Configuration
{
    //####################################

    public function systemConfigSaveAction(Varien_Event_Observer $observer)
    {
        try {

            $request = Mage::app()->getRequest();

            if ($request->getParam('M2ePro_already_forwarded')) {
                return;
            }

            $section = Mage::app()->getRequest()->getParam('section');
            $action = 'save';

            switch ($section) {

                case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_COMPONENTS;
                    $controllerName = 'adminhtml_configuration_components';
                    break;

                case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_SETTINGS;
                    $controllerName = 'adminhtml_configuration_settings';
                    break;

                case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_LOGS_CLEANING;
                    $controllerName = 'adminhtml_configuration_logsCleaning';
                    break;

                case Ess_M2ePro_Helper_View_Configuration::CONFIG_SECTION_LICENSE;
                    $controllerName = 'adminhtml_configuration_license';
                    $action = 'confirmKey';
                    break;

                default:
                    return;
                    break;
            }

            $request->initForward()
                ->setParam('M2ePro_already_forwarded', true)
                ->setModuleName('M2ePro')
                ->setControllerName($controllerName)
                ->setActionName($action)
                ->setDispatched(false);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################
}