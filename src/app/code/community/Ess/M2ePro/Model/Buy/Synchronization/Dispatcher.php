<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Dispatcher extends Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    //####################################

    public function process()
    {
        // Check global mode
        //----------------------------------
        if (!(bool)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                ->getGroupValue('/buy/','mode')
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

            $synchGroup = '/defaults/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($synchGroup,'mode');
            $buySynchGroup = '/buy/defaults/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
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

            $synchGroup = '/other_listings/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($synchGroup,'mode');

            $buySynchGroup = '/buy/other_listings/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
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

            $synchGroup = '/orders/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($synchGroup,
                                                                                      'mode');
            $buySynchGroup = '/buy/orders/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
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

            $synchGroup = '/templates/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($synchGroup,'mode');

            $buySynchGroup = '/buy/templates/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
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

            $synchGroup = '/marketplaces/';
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                                      ->getGroupValue($synchGroup,'mode');

            $buySynchGroup = '/buy/marketplaces/';
            $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
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
        Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        return true;
    }

    private function afterDispatch()
    {
        return true;
    }

    //####################################
}