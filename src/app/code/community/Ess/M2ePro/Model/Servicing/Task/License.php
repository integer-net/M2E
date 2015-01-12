<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_License extends Ess_M2ePro_Model_Servicing_Task
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

            if (isset($data['validation']['validation']) && is_array($data['validation']['validation'])) {
                $this->updateValidationValidData($data['validation']['validation']);
            }
        }

        if (isset($data['components']) && is_array($data['components'])) {
            $this->updateComponentsData($data['components']);
        }

        if (isset($data['connection']) && is_array($data['connection'])) {
            $this->updateConnectionData($data['connection']);
        }
    }

    // ########################################

    private function updateValidationMainData(array $validationData)
    {
        if (isset($validationData['domain'])) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain',(string)$validationData['domain']
            );
        }

        if (isset($validationData['ip'])) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip',(string)$validationData['ip']
            );
        }

        if (isset($validationData['directory'])) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/',
                'directory',(string)$validationData['directory']
            );
        }
    }

    private function updateValidationValidData(array $validationValidData)
    {
        if (isset($validationValidData['domain'])) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/',
                'domain',(int)$validationValidData['domain']
            );
        }

        if (isset($validationValidData['ip'])) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/',
                'ip',(int)$validationValidData['ip']
            );
        }

        if (isset($validationValidData['directory'])) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/',
                'directory',(int)$validationValidData['directory']
            );
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
                Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'mode', (int)$componentData['mode']
                );
            }

            if (isset($componentData['status'])) {
                Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'status', (int)$componentData['status']
                );
            }

            if (isset($componentData['expiration_date'])) {
                Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'expiration_date', (string)$componentData['expiration_date']
                );
            }

            if (isset($componentData['is_free'])) {
                Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                    $componentConfigGroup, 'is_free', (int)$componentData['is_free']
                );
            }
        }
    }

    private function updateConnectionData($data)
    {
        if (isset($data['domain'])) {
            Mage::helper('M2ePro/Module')->getCacheConfig()
                ->setGroupValue('/license/connection/', 'domain', $data['domain']);
        }

        if (isset($data['ip'])) {
            Mage::helper('M2ePro/Module')->getCacheConfig()
                ->setGroupValue('/license/connection/', 'ip', $data['ip']);
        }

        if (isset($data['directory'])) {
            Mage::helper('M2ePro/Module')->getCacheConfig()
                ->setGroupValue('/license/connection/', 'directory', $data['directory']);
        }
    }

    // ########################################
}