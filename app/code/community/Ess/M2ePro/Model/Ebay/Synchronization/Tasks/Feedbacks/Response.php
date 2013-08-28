<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Feedbacks_Response extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 50;
    const PERCENTS_END = 100;
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
        $this->_profiler->addTitle($componentName.'Response Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Response" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Response" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Get no responsed feedbacks
        //-----------------------
        /** @var $feedbacks Ess_M2ePro_Model_Ebay_Feedback[] */
        $feedbacks = Mage::getModel('M2ePro/Ebay_Feedback')->getLastUnanswered(5);

        $responseInterval = (int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/ebay/synchronization/settings/feedbacks/response/', 'attempt_interval');

        $tempFeedbacks = array();
        foreach ($feedbacks as $feedback) {

            $lastResponseAttemptDate = $feedback->getData('last_response_attempt_date');
            $currentGmtDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);

            if (!is_null($lastResponseAttemptDate) &&
                strtotime($lastResponseAttemptDate) + $responseInterval > $currentGmtDate) {
                continue;
            }

            $ebayAccount = $feedback->getEbayAccount();

            if (!$ebayAccount->isFeedbacksReceive()) {
                continue;
            }
            if ($ebayAccount->isFeedbacksAutoResponseDisabled()) {
                continue;
            }
            if ($ebayAccount->isFeedbacksAutoResponseOnlyPositive() && !$feedback->isPositive()) {
                continue;
            }
            if (!$ebayAccount->hasFeedbackTemplate()) {
                continue;
            }

            $tempFeedbacks[] = $feedback;
        }
        $feedbacks = $tempFeedbacks;

        if (count($feedbacks) == 0) {
            return;
        }
        //-----------------------

        // Process feedbacks
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / count($feedbacks);

        foreach ($feedbacks as $feedback) {

            /** @var $feedback Ess_M2ePro_Model_Ebay_Feedback */
            $account = $feedback->getAccount();

            if ($account->getChildObject()->isFeedbacksAutoResponseCycled()) {
                // Load is needed to get correct feedbacks_last_used_id
                $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                    'Account', $feedback->getData('account_id')
                );
            }

            // Get response body
            //-----------------------
            $body = $this->getResponseBody($account);
            if ($body == '') {
                 continue;
            }
            //-----------------------

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            // Create connector
            //-----------------------
            $feedback->sendResponse($body,Ess_M2ePro_Model_Ebay_Feedback::TYPE_POSITIVE);
            //-----------------------

            $this->_profiler->addTitle('Send feedback for "'.$feedback->getData('buyer_name').'"');
            $this->_profiler->addTitle('His feedback "'
                .$feedback->getData('buyer_feedback_text').'" ('.$feedback->getData('buyer_feedback_type').')');
            $this->_profiler->addTitle('Our feedback "'.$body.'"');

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;
        }
        //-----------------------
    }

    //####################################

    private function getResponseBody(Ess_M2ePro_Model_Account $account)
    {
        if ($account->getChildObject()->isFeedbacksAutoResponseCycled()) {

            $lastUsedId = 0;
            if ($account->getChildObject()->getFeedbacksLastUsedId() != null) {
                $lastUsedId = (int)$account->getChildObject()->getFeedbacksLastUsedId();
            }

            $feedbackTemplatesIds = Mage::getModel('M2ePro/Ebay_Feedback_Template')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->setOrder('id','ASC')
                ->getAllIds();

            if (!count($feedbackTemplatesIds)) {
                return '';
            }

            $feedbackTemplate = Mage::getModel('M2ePro/Ebay_Feedback_Template');
            if (max($feedbackTemplatesIds) > $lastUsedId) {
                foreach ($feedbackTemplatesIds as $templateId) {
                    if ($templateId <= $lastUsedId) {
                        continue;
                    }

                    $feedbackTemplate->load($templateId);
                    break;
                }
            } else {
                $feedbackTemplate->load(min($feedbackTemplatesIds));
            }

            if (!$feedbackTemplate->getId()) {
                return '';
            }

            $account->setData('feedbacks_last_used_id', $feedbackTemplate->getId())->save();

            return $feedbackTemplate->getBody();
        }

        if ($account->getChildObject()->isFeedbacksAutoResponseRandom()) {

            $feedbackTemplatesIds = Mage::getModel('M2ePro/Ebay_Feedback_Template')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->getAllIds();

            if (!count($feedbackTemplatesIds)) {
                return '';
            }

            $index = rand(0, count($feedbackTemplatesIds) - 1);
            $feedbackTemplate = Mage::getModel('M2ePro/Ebay_Feedback_Template')->load($feedbackTemplatesIds[$index]);

            if (!$feedbackTemplate->getId()) {
                return '';
            }

            return $feedbackTemplate->getBody();
        }

        return '';
    }

    //####################################
}