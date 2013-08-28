<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_License implements Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'license';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        if (isset($data['validation']) && is_array($data['validation'])) {

            $this->updateValidationMainData($data['validation']);

            if (isset($data['validation']['checks']) && is_array($data['validation']['checks'])) {
                $this->updateValidationChecksData($data['validation']['checks']);
            }
        }

        if (isset($data['components']) && is_array($data['components'])) {
            $this->updateComponentsData($data['components']);
        }
    }

    // ########################################

    private function updateValidationMainData(array $validationData)
    {
        if (isset($validationData['domain'])) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain',(string)$validationData['domain']
            );
        }

        if (isset($validationData['ip'])) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip',(string)$validationData['ip']
            );
        }

        if (isset($validationData['directory'])) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory',(string)$validationData['directory']
            );
        }
    }

    private function updateValidationChecksData(array $validationChecksData)
    {
        if (isset($validationChecksData['domain'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/license/validation/domain/notification/',
                                                                      'mode',(int)$validationChecksData['domain']);
        }
        if (isset($validationChecksData['ip'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/license/validation/ip/notification/',
                                                                      'mode',(int)$validationChecksData['ip']);
        }
        if (isset($validationChecksData['directory'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/license/validation/directory/notification/',
                                                                      'mode',(int)$validationChecksData['directory']);
        }
    }

    private function updateComponentsData(array $componentsData)
    {
        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {

            if (!isset($componentsData[$component]) ||
                !is_array($componentsData[$component])) {
                continue;
            }

            $componentData = $componentsData[$component];
            $componentConfigGroup = '/'.Mage::helper('M2ePro/Module')->getName().
                                    '/license/'.strtolower($component).'/';

            if (isset($componentData['mode'])) {
                Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'mode', (int)$componentData['mode']
                );
            }

            if (isset($componentData['status'])) {
                Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'status', (int)$componentData['status']
                );
            }

            if (isset($componentData['expiration_date'])) {
                Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'expiration_date', (string)$componentData['expiration_date']
                );
            }

            if (isset($componentData['is_free'])) {
                Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'is_free', (int)$componentData['is_free']
                );
            }
        }
    }

    // ########################################
}