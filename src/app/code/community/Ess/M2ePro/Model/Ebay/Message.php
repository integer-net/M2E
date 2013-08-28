<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Message extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Message');
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

    public function sendResponse($text)
    {
        $paramsConnector = array(
            'body' => $text,
            'parent_message_id' => $this->getData('message_id'),
            'recipient_id' => $this->getData('sender_name'),
            'item_id' => $this->getData('ebay_item_id')
        );

        Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                    ->processVirtualAbstract('message','add','entity',
                                             $paramsConnector,NULL,
                                             NULL,$this->getAccount()->getId(),NULL);
    }

    // ########################################

    public static function haveNew()
    {
        $showMessagesNotification = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/messages/notification/', 'mode');

        if (!$showMessagesNotification) {
            return false;
        }

        $lastCheckDate = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/messages/notification/', 'last_check');

        if (is_null($lastCheckDate)) {
            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/messages/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $newMessagesCount = Mage::getModel('M2ePro/Ebay_Message')->getCollection()
            ->addFieldToFilter('message_date', array('gt' => $lastCheckDate))
            ->getSize();

        if ($newMessagesCount > 0) {
            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/messages/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        return $newMessagesCount > 0;
    }

    public static function receiveFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        // Create connector
        //-----------------------
        $messages = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                                ->processVirtualAbstract('message','get','memberList',
                                                         $paramsConnector,'messages',
                                                         NULL,$account->getId(),NULL);
        is_null($messages) && $messages = array();
        //-----------------------

        // Get new messages
        //-----------------------
        $countNewMessages = 0;
        foreach ($messages as $message) {
            $dbMessage = array(
                'account_id'      => $account->getId(),
                'ebay_item_id'    => $message['item_id'],
                'ebay_item_title' => $message['item_title'],
                'sender_name'     => $message['sender_name'],
                'message_id'      => $message['id'],
                'message_subject' => $message['subject'],
                'message_text'    => $message['body'],
                'message_date'    => $message['creation_date'],
                'message_type'    => $message['type']
            );

            if (isset($message['responses'])) {
                $dbMessage['message_responses'] = json_encode($message['responses'], JSON_FORCE_OBJECT);
            }

            $existMessage = Mage::getModel('M2ePro/Ebay_Message')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('message_id', $message['id'])
                ->getFirstItem();

            if (is_null($existMessage->getId())) {
                $countNewMessages++;
            }

            $existMessage->addData($dbMessage)->save();
        }
        //-----------------------

        return array(
            'new'   => $countNewMessages,
            'total' => count($messages)
        );
    }

    // ########################################
}