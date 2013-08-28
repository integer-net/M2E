<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Dispatcher extends Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    //####################################

    public function process()
    {
        // Check global mode
        //----------------------------------
        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()
                                                ->getGroupValue('/buy/synchronization/settings/','mode')
        ) {
            return false;
        }
        //----------------------------------

        // Before dispatch actions
        //---------------------------
        if (!$this->beforeDispatch()) {
            return false;
        }
        //---------------------------

        try {

            // DEFAULTS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS);

            $synchGroup = '/synchronization/settings/defaults/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                      ->getGroupValue($synchGroup,'mode');
            $buySynchGroup = '/buy/synchronization/settings/defaults/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                     ->getGroupValue($buySynchGroup,
                                                                                     'mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // OTHER LISTINGS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS);

            $synchGroup = '/synchronization/settings/other_listings/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                      ->getGroupValue($synchGroup,'mode');

            $buySynchGroup = '/buy/synchronization/settings/other_listings/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                     ->getGroupValue($buySynchGroup,'mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_OtherListings();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // ORDERS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::ORDERS);

            $synchGroup = '/synchronization/settings/orders/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                      ->getGroupValue($synchGroup,
                                                                                      'mode');
            $buySynchGroup = '/buy/synchronization/settings/orders/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                     ->getGroupValue($buySynchGroup,
                                                                                     'mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // TEMPLATES SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES);

            $synchGroup = '/synchronization/settings/templates/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                      ->getGroupValue($synchGroup,'mode');

            $buySynchGroup = '/buy/synchronization/settings/templates/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                     ->getGroupValue($buySynchGroup,'mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Templates();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // MARKETPLACES SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::MARKETPLACES);

            $synchGroup = '/synchronization/settings/marketplaces/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                      ->getGroupValue($synchGroup,'mode');

            $buySynchGroup = '/buy/synchronization/settings/marketplaces/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                     ->getGroupValue($buySynchGroup,'mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Marketplaces();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        // After dispatch actions
        //---------------------------
        if (!$this->afterDispatch()) {
            return false;
        }
        //---------------------------

        return true;
    }

    //####################################

    private function beforeDispatch()
    {
        Mage::helper('M2ePro')->getGlobalValue('synchLogs')->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        return true;
    }

    private function afterDispatch()
    {
        return true;
    }

    //####################################
}