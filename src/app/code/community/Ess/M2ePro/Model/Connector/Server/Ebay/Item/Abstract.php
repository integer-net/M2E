<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Abstract
{
    const STATUS_ERROR      = 1;
    const STATUS_WARNING    = 2;
    const STATUS_SUCCESS    = 3;

    /**
     * @var Ess_M2ePro_Model_Listing
     */
    protected $listing = NULL;

    protected $logsActionId = NULL;
    protected $status = self::STATUS_SUCCESS;

    /**
     * @var Ess_M2ePro_Model_Listing_LockItem
     */
    protected $lockItem = NULL;
    protected $isNeedRemoveLock = false;

    protected $nativeRequestData = array();

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing $listing)
    {
        $defaultParams = array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );
        $params = array_merge($defaultParams, $params);

        if (isset($params['logs_action_id'])) {
            $this->logsActionId = (int)$params['logs_action_id'];
            unset($params['logs_action_id']);
        } else {
            $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        }

        $this->listing = $listing;

        parent::__construct($params,$this->listing->getMarketplace(),
                            $this->listing->getAccount(),NULL);
    }

    public function __destruct()
    {
        $this->checkUnlockListing();
    }

    // ########################################

    public function process()
    {
        $this->setStatus(self::STATUS_SUCCESS);

        if (!$this->validateNeedRequestSend()) {
            return array();
        }

        $this->updateOrLockListing();
        $result = parent::process();
        $this->checkUnlockListing();

        return $result;
    }

    // ########################################

    protected function updateOrLockListing()
    {
        $params = array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'id' => $this->listing->getId()
        );
        $this->lockItem = Mage::getModel('M2ePro/Listing_LockItem', $params);

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

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                      array $message,
                                                      $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage($listingProduct,$message,$priority);
    }

    protected function addListingsLogsMessage(array $message,
                                              $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage(NULL,$message,$priority);
    }

    //-----------------------------------------

    protected function addBaseListingsLogsMessage($listingProduct,
                                                array $message,
                                                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $action = $this->getListingsLogsCurrentAction();
        is_null($action) && $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

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
                    $this->setStatus(self::STATUS_ERROR);
                break;
            case parent::MESSAGE_TYPE_WARNING:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                    $this->setStatus(self::STATUS_WARNING);
                break;
            case parent::MESSAGE_TYPE_SUCCESS:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                    $this->setStatus(self::STATUS_SUCCESS);
                break;
            case parent::MESSAGE_TYPE_NOTICE:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                    $this->setStatus(self::STATUS_SUCCESS);
                break;
            default:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                    $this->setStatus(self::STATUS_ERROR);
                break;
        }

        $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        } else if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION;
        }

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        if (is_null($listingProduct)) {
            $logModel->addListingMessage($this->listing->getId() ,
                                         $initiator ,
                                         $this->logsActionId ,
                                         $action , $text, $type , $priority);
        } else {
            $logModel->addProductMessage($this->listing->getId() ,
                                         $listingProduct->getProductId() ,
                                         $listingProduct->getId() ,
                                         $initiator ,
                                         $this->logsActionId ,
                                         $action , $text, $type , $priority);
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
        if (!in_array($status,array(self::STATUS_ERROR, self::STATUS_WARNING, self::STATUS_SUCCESS))) {
            return;
        }

        if ($status == self::STATUS_ERROR) {
            $this->status = self::STATUS_ERROR;
            return;
        }

        if ($this->status == self::STATUS_ERROR) {
            return;
        }

        if ($status == self::STATUS_WARNING) {
            $this->status = self::STATUS_WARNING;
            return;
        }

        if ($this->status == self::STATUS_WARNING) {
            return;
        }

        $this->status = self::STATUS_SUCCESS;
    }

    public static function getMainStatus($statuses)
    {
        foreach (array(self::STATUS_ERROR, self::STATUS_WARNING) as $status) {
            if (in_array($status, $statuses)) {
                return $status;
            }
        }

        return self::STATUS_SUCCESS;
    }

    // ########################################

    protected function logAdditionalWarningMessages(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $messages = $listingProduct->getData('__additional_warning_messages__');
        !$messages && $messages = array();

        foreach ($messages as $message) {
            $this->addListingsProductsLogsMessage($listingProduct,array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            ),Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }

        $listingProduct->setData('__additional_warning_messages__',NULL);
    }

    protected function isTheSameProductAlreadyListed(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        $listingProductCollection
            ->getSelect()
            ->join(array('l'=>$listingTable),'`main_table`.`listing_id` = `l`.`id`',array());

        $listingProductCollection
            ->addFieldToFilter('status',array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED))
            ->addFieldToFilter('product_id',$listingProduct->getProductId())
            ->addFieldToFilter('account_id',$this->account->getId())
            ->addFieldToFilter('marketplace_id',$this->marketplace->getId());

        $theSameListingProduct = $listingProductCollection->getFirstItem();

        if (!$theSameListingProduct->getId()) {
            return false;
        }

        $listing = $theSameListingProduct->getListing();

        $message = Mage::helper('M2ePro')->__(
        'There is another item with the same eBay user ID, product ID and marketplace presented in "%s" (%d) Listing.',
        $listing->getTitle(),
        $listing->getId()
        );

        $message = array(
            parent::MESSAGE_TEXT_KEY => $message,
            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
        );

        $this->addListingsProductsLogsMessage($listingProduct,$message,
                                              Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        return true;
    }

    // ########################################
}