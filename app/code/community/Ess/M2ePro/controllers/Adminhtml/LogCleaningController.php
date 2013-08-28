<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_LogCleaningController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################}

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/configuration/log_cleaning')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Logs Clearing'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Configuration/LogCleaningHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/configuration/log_cleaning');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listing_log_cleaning'))
             ->renderLayout();
    }

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
                    $tempString = Mage::helper('M2ePro')->__('Log for %s has been successfully cleared.',
                                                             str_replace('_',' ',$log));
                    $this->_getSession()->addSuccess($tempString);
                    break;

                case 'clear_all':
                    Mage::getModel('M2ePro/Log_Cleaning')->clearAllLog($log);
                    $tempString = Mage::helper('M2ePro')->__('All log for %s has been successfully cleared.',
                                                             str_replace('_',' ',$log));
                    $this->_getSession()->addSuccess($tempString);
                    break;

                case 'run_now_logs':
                    Mage::getModel('M2ePro/Log_Cleaning')->clearOldRecords(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS
                    );
                    Mage::getModel('M2ePro/Log_Cleaning')->clearOldRecords(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS
                    );
                    Mage::getModel('M2ePro/Log_Cleaning')->clearOldRecords(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS
                    );
                    Mage::getModel('M2ePro/Log_Cleaning')->clearOldRecords(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS
                    );
                    $this->_getSession()->addSuccess(
                        Mage::helper('M2ePro')->__('All logs has been successfully cleared.')
                    );
                    break;

                case 'clear_all_logs':
                    Mage::getModel('M2ePro/Log_Cleaning')->clearAllLog(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS
                    );
                    Mage::getModel('M2ePro/Log_Cleaning')->clearAllLog(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS
                    );
                    Mage::getModel('M2ePro/Log_Cleaning')->clearAllLog(
                        Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS
                    );
                    $this->_getSession()->addSuccess(
                        Mage::helper('M2ePro')->__('All logs has been successfully cleared.')
                    );
                    break;
            }
        }
        //--------------------

        $this->_redirect('*/*/index');
    }

    //#############################################
}