<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Feedbacks_Receive extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 50;
    const PERCENTS_INTERVAL = 50;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Receive Action');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Receive" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Receive" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare MySQL data
        //-----------------------
        $tableFeedbacks = Mage::getResourceModel('M2ePro/Ebay_Feedback')->getMainTable();
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        //-----------------------

        // Get all accounts
        //-----------------------
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')
            ->addFieldToFilter('feedbacks_receive', Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_RECEIVE_YES);
        $accounts = $accountsCollection->getItems();

        if (count($accounts) == 0) {
            return;
        }
        //-----------------------

        // Process accounts
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / count($accounts);

        foreach ($accounts as $account) {

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            $this->_profiler->addTitle('Starting account "'.$account->getTitle().'"');
            $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getId(),'Get feedbacks from eBay');

            // ->__('The "Receive" action for eBay account: "%s" is started. Please wait...')
            $status = 'The "Receive" action for eBay account: "%s" is started. Please wait...';
            $this->_lockItem->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            // Set Seller Max Date param
            //-----------------------
            $dbSelect = $connRead->select()
                                 ->from($tableFeedbacks,new Zend_Db_Expr('MAX(`seller_feedback_date`)'))
                                 ->where('`account_id` = ?',(int)$account->getId());
            $maxSellerDate = $connRead->fetchOne($dbSelect);
            if (strtotime($maxSellerDate) < strtotime('2001-01-02')) {
                $maxSellerDate = NULL;
            }
            //-----------------------

            // Set Buyer Max Date param
            //-----------------------
            $dbSelect = $connRead->select()
                                 ->from($tableFeedbacks,new Zend_Db_Expr('MAX(`buyer_feedback_date`)'))
                                 ->where('`account_id` = ?',(int)$account->getId());
            $maxBuyerDate = $connRead->fetchOne($dbSelect);
            if (strtotime($maxBuyerDate) < strtotime('2001-01-02')) {
                $maxBuyerDate = NULL;
            }
            //-----------------------

            // Update feedbacks
            //-----------------------
            $paramsConnector = array();
            !is_null($maxSellerDate) && $paramsConnector['seller_max_date'] = $maxSellerDate;
            !is_null($maxBuyerDate) && $paramsConnector['buyer_max_date'] = $maxBuyerDate;

            $resultReceive = Mage::getModel('M2ePro/Ebay_Feedback')->receiveFromEbay($account,$paramsConnector);

            $this->_profiler->addTitle('Total received feedbacks from eBay: '.$resultReceive['total']);
            $this->_profiler->addTitle('Total only new feedbacks from eBay: '.$resultReceive['new']);
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'get'.$account->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;
        }
        //------------------------
    }

    //####################################
}