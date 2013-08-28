<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Feedback extends Ess_M2ePro_Model_Component_Abstract
{
    const ROLE_BUYER  = 'Buyer';
    const ROLE_SELLER = 'Seller';

    const TYPE_NEUTRAL  = 'Neutral';
    const TYPE_POSITIVE = 'Positive';
    const TYPE_NEGATIVE = 'Negative';

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Feedback');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->accountModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account',$this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ########################################

    public function isNeutral()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEUTRAL;
    }

    public function isNegative()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEGATIVE;
    }

    public function isPositive()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_POSITIVE;
    }

    // ----------------------------------------

    public function sendResponse($text, $type = self::TYPE_POSITIVE)
    {
        $paramsConnector = array(
            'item_id'        => $this->getData('ebay_item_id'),
            'transaction_id' => $this->getData('ebay_transaction_id'),
            'text'           => $text,
            'type'           => $type,
            'target_user'    => $this->getData('buyer_name')
        );

        $this->setData('last_response_attempt_date', Mage::helper('M2ePro')->getCurrentGmtDate())->save();

        try {
            $response = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                                ->processVirtualAbstract('feedback', 'add', 'entity',
                                                         $paramsConnector, NULL, NULL,
                                                         $this->getAccount()
            );
        } catch (Exception $e) {
            Mage::helper('M2ePro/Exception')->process($e);
            return;
        }

        if (!isset($response['feedback_id'])) {
            return;
        }

        $this->setData('seller_feedback_id', $response['feedback_id']);
        $this->setData('seller_feedback_type', $type);
        $this->setData('seller_feedback_text', $text);
        $this->save();
    }

    // ########################################

    public static function haveNew($onlyNegative = false)
    {
        $showFeedbacksNotification = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/feedbacks/notification/', 'mode');

        if (!$showFeedbacksNotification) {
            return false;
        }

        $lastCheckDate = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/feedbacks/notification/', 'last_check');

        if (is_null($lastCheckDate)) {
            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/feedbacks/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $feedbacksCollection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection()
            ->addFieldToFilter('buyer_feedback_date', array('gt' => $lastCheckDate));

        if ($onlyNegative) {
            $feedbacksCollection->addFieldToFilter('buyer_feedback_type', self::TYPE_NEGATIVE);
        }

        $newFeedbacksCount = $feedbacksCollection->getSize();

        if ($newFeedbacksCount > 0) {
            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/feedbacks/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        return $newFeedbacksCount > 0;
    }

    public static function receiveFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        // Create connector
        //-----------------------
        $feedbacks = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                                ->processVirtualAbstract('feedback','get','entity',
                                                         $paramsConnector,'feedbacks',
                                                         NULL,$account->getId(),NULL);
        is_null($feedbacks) && $feedbacks = array();
        //-----------------------

        // Get and update feedbacks
        //-----------------------
        $countNewFeedbacks = 0;
        foreach ($feedbacks as $feedback) {

            $dbFeedback = array(
                'account_id' => $account->getId(),
                'ebay_item_id' => $feedback['item_id'],
                'ebay_transaction_id' => $feedback['transaction_id']
            );

            if ($feedback['item_title'] != '') {
                $dbFeedback['ebay_item_title'] = $feedback['item_title'];
            }

            if ($feedback['from_role'] == self::ROLE_BUYER) {
                $dbFeedback['buyer_name'] = $feedback['user_sender'];
                $dbFeedback['buyer_feedback_id'] = $feedback['id'];
                $dbFeedback['buyer_feedback_text'] = $feedback['info']['text'];
                $dbFeedback['buyer_feedback_date'] = $feedback['info']['date'];
                $dbFeedback['buyer_feedback_type'] = $feedback['info']['type'];
            } else {
                $dbFeedback['seller_feedback_id'] = $feedback['id'];
                $dbFeedback['seller_feedback_text'] = $feedback['info']['text'];
                $dbFeedback['seller_feedback_date'] = $feedback['info']['date'];
                $dbFeedback['seller_feedback_type'] = $feedback['info']['type'];
            }

            $existFeedback = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('ebay_item_id', $feedback['item_id'])
                ->addFieldToFilter('ebay_transaction_id', $feedback['transaction_id'])
                ->getFirstItem();

            if (!is_null($existFeedback->getId())) {
                if ($feedback['from_role'] == self::ROLE_BUYER && !$existFeedback->getData('buyer_feedback_id')) {
                    $countNewFeedbacks++;
                }
                if ($feedback['from_role'] == self::ROLE_SELLER && !$existFeedback->getData('seller_feedback_id')) {
                    $countNewFeedbacks++;
                }
            } else {
                $countNewFeedbacks++;
            }

            $existFeedback->addData($dbFeedback)->save();
        }
        //-----------------------

        return array(
            'total' => count($feedbacks),
            'new'   => $countNewFeedbacks
        );
    }

    public static function getLastUnanswered($daysAgo = 30)
    {
        $tableAccounts  = Mage::getResourceModel('M2ePro/Account')->getMainTable();

        $feedbacksCollection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection();
        $feedbacksCollection->getSelect()
            ->join(array('a'=>$tableAccounts),'`a`.`id` = `main_table`.`account_id`',array())
            ->where('`main_table`.`seller_feedback_id` = 0 OR `main_table`.`seller_feedback_id` IS NULL')
            ->where('`main_table`.`buyer_feedback_date` > DATE_SUB(NOW(), INTERVAL ? DAY)',(int)$daysAgo)
            ->order(array('buyer_feedback_date ASC'));

        return $feedbacksCollection->getItems();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order|null
     */
    public function getOrder()
    {
        /** @var $collection Ess_M2ePro_Model_Mysql4_Order_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection->getSelect()
            ->join(
                array('oi' => Mage::getResourceModel('M2ePro/Order_Item')->getMainTable()),
                '`oi`.`order_id` = `main_table`.`id`',
                array()
            )
            ->join(
                array('eoi' => Mage::getResourceModel('M2ePro/Ebay_Order_Item')->getMainTable()),
                '`eoi`.`order_item_id` = `oi`.`id`',
                array()
            );

        $collection->addFieldToFilter('account_id', $this->getData('account_id'));
        $collection->addFieldToFilter('eoi.item_id', $this->getData('ebay_item_id'));
        $collection->addFieldToFilter('eoi.transaction_id', $this->getData('ebay_transaction_id'));

        $collection->getSelect()->limit(1);

        $order = $collection->getFirstItem();

        return !is_null($order->getId()) ? $order : NULL;
    }

    // ########################################
}