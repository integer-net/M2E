<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Configuration_LogsCleaningController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //#############################################

    public function saveAction()
    {
        // Save settings
        //--------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            Mage::getModel('M2ePro/Log_Cleaning')->saveSettings(
                Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS,
                $post['listings_log_mode'],
                $post['listings_log_days']
            );
            Mage::getModel('M2ePro/Log_Cleaning')->saveSettings(
                Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS,
                $post['other_listings_log_mode'],
                $post['other_listings_log_days']
            );
            Mage::getModel('M2ePro/Log_Cleaning')->saveSettings(
                Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS,
                $post['synchronizations_log_mode'],
                $post['synchronizations_log_days']
            );
            Mage::getModel('M2ePro/Log_Cleaning')->saveSettings(
                Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS,
                $post['orders_log_mode'],
                0
            );

            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('The clearing settings has been successfully saved.')
            );
        }
        //--------------------

        // Get actions
        //--------------------
        $task = $this->getRequest()->getParam('task');
        $log = $this->getRequest()->getParam('log');

        if (!is_null($task)) {

            switch ($task) {
                case 'run_now':
                    Mage::getModel('M2ePro/Log_Cleaning')->clearOldRecords($log);
                    $tempString = Mage::helper('M2ePro')->__(
                        'Log for %title% has been successfully cleared.',
                         str_replace('_',' ',$log)
                    );
                    $this->_getSession()->addSuccess($tempString);
                    break;

                case 'clear_all':
                    Mage::getModel('M2ePro/Log_Cleaning')->clearAllLog($log);
                    $tempString = Mage::helper('M2ePro')->__(
                        'All log for %title% has been successfully cleared.',
                         str_replace('_',' ',$log)
                    );
                    $this->_getSession()->addSuccess($tempString);
                    break;
            }
        }
        //--------------------

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################
}