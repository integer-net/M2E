<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Server_Ebay_OtherItem_Abstract
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    protected $otherListing = NULL;

    protected $logsActionId = NULL;
    protected $status = Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS;

    /**
     * @var Ess_M2ePro_Model_Listing_Other_LockItem
     */
    protected $lockItem = NULL;
    protected $isNeedRemoveLock = false;

    protected $nativeRequestData = array();

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $defaultParams = array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );
        $params = array_merge($defaultParams, $params);

        if (isset($params['logs_action_id'])) {
            $this->logsActionId = (int)$params['logs_action_id'];
            unset($params['logs_action_id']);
        } else {
            $this->logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();
        }

        $this->otherListing = $otherListing;

        parent::__construct($params,$this->otherListing->getMarketplace(),
                            $this->otherListing->getAccount(),NULL);
    }

    public function __destruct()
    {
        $this->checkUnlockListing();
    }

    // ########################################

    public function process()
    {
        $this->setStatus(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS);

        if (!$this->validateNeedRequestSend()) {
            return array();
        }

        $this->updateOrLockListing();
        $result = parent::process();
        $this->checkUnlockListing();

        foreach ($this->messages as $message) {
            $priorityMessage = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priorityMessage = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }
            $this->addProductsLogsMessage($this->otherListing, $message, $priorityMessage);
        }

        return $result;
    }

    // ########################################

    protected function updateOrLockListing()
    {
        $params = array('component' => Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->lockItem = Mage::getModel('M2ePro/Listing_Other_LockItem', $params);

        if (!$this->lockItem->isExist()) {
            $this->lockItem->create();
            $this->lockItem->makeShutdownFunction();
            $this->isNeedRemoveLock = true;
        }

        $this->lockItem->activate();
    }

    protected function checkUnlockListing()
    {
        if (!is_null($this->lockItem) && $this->isNeedRemoveLock) {
            $this->lockItem->isExist() && $this->lockItem->remove();
        }
        $this->isNeedRemoveLock = false;
    }

    // ########################################

    protected function addProductsLogsMessage(Ess_M2ePro_Model_Listing_Other $otherListing,
                                              array $message,
                                              $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseLogsMessage($otherListing,$message,$priority);
    }

    protected function addLogsMessage(array $message, $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseLogsMessage(NULL,$message,$priority);
    }

    //-----------------------------------------

    private function addBaseLogsMessage($otherListing,
                                        array $message,
                                        $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $action = $this->getListingsLogsCurrentAction();
        is_null($action) && $action = Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNKNOWN;

        if (!isset($message[parent::MESSAGE_TEXT_KEY]) || $message[parent::MESSAGE_TEXT_KEY] == '') {
            return;
        }
        $text = $message[parent::MESSAGE_TEXT_KEY];

        if (!isset($message[parent::MESSAGE_TYPE_KEY]) || $message[parent::MESSAGE_TYPE_KEY] == '') {
            return;
        }

        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
        switch ($message[parent::MESSAGE_TYPE_KEY]) {
            case parent::MESSAGE_TYPE_ERROR:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                    $this->setStatus(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR);
                break;
            case parent::MESSAGE_TYPE_WARNING:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                    $this->setStatus(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING);
                break;
            case parent::MESSAGE_TYPE_SUCCESS:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                    $this->setStatus(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS);
                break;
            case parent::MESSAGE_TYPE_NOTICE:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                    $this->setStatus(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS);
                break;
            default:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                    $this->setStatus(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR);
                break;
        }

        $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER;
        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        } else if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION;
        }

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        if (is_null($otherListing)) {
            $logModel->addGlobalMessage($initiator , $this->logsActionId , $action , $text , $type , $priority);
        } else {
            $logModel->addProductMessage($otherListing->getId() ,
                                         $initiator ,
                                         $this->logsActionId ,
                                         $action , $text , $type , $priority);
        }
    }

    // ########################################

    abstract protected function validateNeedRequestSend();

    abstract protected function getListingsLogsCurrentAction();

    // ########################################

    public function getStatus()
    {
        return $this->status;
    }

    protected function setStatus($status)
    {
        if (!in_array($status,array(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR,
                                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING,
                                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS))) {
            return;
        }

        if ($status == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR) {
            $this->status = Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR;
            return;
        }

        if ($this->status == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR) {
            return;
        }

        if ($status == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING) {
            $this->status = Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING;
            return;
        }

        if ($this->status == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING) {
            return;
        }

        $this->status = Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS;
    }

    public static function getMainStatus($statuses)
    {
        foreach (array(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR,
                       Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING) as $status) {
            if (in_array($status, $statuses)) {
                return $status;
            }
        }

        return Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS;
    }

    // ########################################
}