<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Dispatcher extends Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    //####################################

    public function process()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        // Check global mode
        //----------------------------------
        if (!(bool)$config->getGroupValue('/ebay/synchronization/settings/','mode')) {
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
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/defaults/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/defaults/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Defaults();
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
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/other_listings/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/other_listings/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings();
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
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/orders/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/orders/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders();
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
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/templates/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/templates/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // FEEDBACKS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS);
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/feedbacks/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/feedbacks/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Feedbacks();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // MESSAGES SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES);
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/messages/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/messages/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Messages();
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
            $tempGlobalMode = (bool)(int)$config->getGroupValue('/synchronization/settings/marketplaces/','mode');
            $tempLocalMode = (bool)(int)$config->getGroupValue('/ebay/synchronization/settings/marketplaces/','mode');
            if ($tempTask && $tempGlobalMode && $tempLocalMode) {
                $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces();
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
        Mage::helper('M2ePro')->getGlobalValue('synchLogs')->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        return true;
    }

    private function afterDispatch()
    {
        return true;
    }

    //####################################
}