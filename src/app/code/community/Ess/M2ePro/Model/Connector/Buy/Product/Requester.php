<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Product_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    protected $logsActionId = NULL;
    protected $neededRemoveLocks = array();

    protected $isProcessingItems = false;
    protected $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    protected $listingsProducts = array();
    protected $listingProductRequestsData = array();

    // ########################################

    public function __construct(array $params = array(), array $listingsProducts)
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

        if (count($listingsProducts) == 0) {
            throw new Exception('Product connector has received empty array');
        }

        foreach($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                throw new Exception('Product connector has received invalid product data type');
            }
        }

        $accountObj = $listingsProducts[0]->getListing()->getAccount();

        foreach($listingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            if ($accountObj->getId() != $listingProduct->getListing()->getAccountId()) {
                throw new Exception('Product connector has received products from different accounts');
            }
        }

        parent::__construct($params,$accountObj);

        $listingsProducts = $this->filterLockedListingsProducts($listingsProducts);
        $listingsProducts = $this->prepareListingsProducts($listingsProducts);

        $this->listingsProducts = array_values($listingsProducts);
    }

    public function __destruct()
    {
        $this->checkUnlockListings();
    }

    // ########################################

    public function process()
    {
        $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
        $this->setIsProcessingItems(false);

        if (count($this->listingsProducts) <= 0) {
            return;
        }

        $this->setIsProcessingItems(true);

        $this->updateOrLockListings();
        parent::process();

        // When all items are failed in response
        (isset($this->response['data']['messages'])) && $tempMessages = $this->response['data']['messages'];
        if (isset($tempMessages) && is_array($tempMessages) && count($tempMessages) > 0) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
        }

        $this->checkUnlockListings();
    }

    protected function getResponserParams()
    {
        $tempProductsData = array();

        foreach ($this->listingsProducts as $listingProduct) {

            $tempNativeData = array();
            isset($this->listingProductRequestsData[$listingProduct->getId()]['native_data']) &&
                $tempNativeData = $this->listingProductRequestsData[$listingProduct->getId()]['native_data'];

            $tempSendedData = array();
            isset($this->listingProductRequestsData[$listingProduct->getId()]['sended_data']) &&
                $tempSendedData = $this->listingProductRequestsData[$listingProduct->getId()]['sended_data'];

            $tempProductsData[$listingProduct->getId()] = array(
                'id' => $listingProduct->getId(),
                'request' => array(
                    'native_data' => $tempNativeData,
                    'sended_data' => $tempSendedData
                )
            );
        }

        return array(
            'account_id' => $this->listingsProducts[0]->getListing()->getAccountId(),
            'action_identifier' => $this->getActionIdentifier(),
            'listing_log_action' => $this->getListingsLogsCurrentAction(),
            'logs_action_id' => $this->logsActionId,
            'status_changer' => $this->params['status_changer'],
            'params' => $this->params,
            'products' => $tempProductsData
        );
    }

    protected function setLocks($hash)
    {
        $actionIdentifier = $this->getActionIdentifier();

        $tempListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->addObjectLock(NULL,$hash);
            $listingProduct->addObjectLock('in_action',$hash);
            $listingProduct->addObjectLock($actionIdentifier.'_action',$hash);

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->addObjectLock(NULL,$hash);
            $listingProduct->getListing()->addObjectLock('products_in_action',$hash);
            $listingProduct->getListing()->addObjectLock('products_'.$actionIdentifier.'_action',$hash);

            $tempListings[$listingProduct->getListingId()] = true;
        }

        $this->account->addObjectLock(
            'products_in_action', $hash
        );
        $this->account->addObjectLock(
            'products_'.$actionIdentifier.'_action', $hash
        );

        Mage::helper('M2ePro/Component_Buy')->getMarketplace()->addObjectLock(
            'products_in_action', $hash
        );
        Mage::helper('M2ePro/Component_Buy')->getMarketplace()->addObjectLock(
            'products_'.$actionIdentifier.'_action', $hash
        );
    }

    // ########################################

    protected function updateOrLockListings()
    {
        foreach ($this->listingsProducts as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            if (isset($this->neededRemoveLocks[$product->getListingId()])) {
                continue;
            }

            $lockItemParams = array(
                'component' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'id' => $product->getListingId()
            );

            $lockItem = Mage::getModel('M2ePro/Listing_LockItem',$lockItemParams);

            if (!$lockItem->isExist()) {
                $lockItem->create();
                $lockItem->makeShutdownFunction();
                $this->neededRemoveLocks[$product->getListingId()] = $lockItem;
            }

            $lockItem->activate();
        }
    }

    protected function checkUnlockListings()
    {
        foreach ($this->neededRemoveLocks as $lockItem) {
            $lockItem->isExist() && $lockItem->remove();
        }
        $this->neededRemoveLocks = array();
    }

    // ########################################

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                      $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                      $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage($listingProduct,$text,$type,$priority,false);
    }

    protected function addListingsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                              $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                              $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage($listingProduct,$text,$type,$priority,true);
    }

    protected function addBaseListingsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                  $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                  $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM,
                                                  $isListingMode = true)
    {
        $action = $this->getListingsLogsCurrentAction();
        is_null($action) && $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

        switch ($type) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_WARNING);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
                break;
            default:
                    $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
                break;
        }

        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        if ($isListingMode) {
            $logModel->addListingMessage($listingProduct->getListingId() ,
                                         $initiator ,
                                         $this->logsActionId ,
                                         $action , $text, $type , $priority);
        } else {
            $logModel->addProductMessage($listingProduct->getListingId() ,
                                         $listingProduct->getProductId() ,
                                         $listingProduct->getId() ,
                                         $initiator ,
                                         $this->logsActionId ,
                                         $action , $text, $type , $priority);
        }
    }

    // ########################################

    abstract protected function getActionIdentifier();

    abstract protected function getListingsLogsCurrentAction();

    abstract protected function prepareListingsProducts($listingsProducts);

    // ########################################

    public function getStatus()
    {
        return $this->status;
    }

    protected function setStatus($status)
    {
        if (!in_array($status,array(
            Ess_M2ePro_Helper_Data::STATUS_ERROR,
            Ess_M2ePro_Helper_Data::STATUS_WARNING,
            Ess_M2ePro_Helper_Data::STATUS_SUCCESS))) {
            return;
        }

        if ($status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            return;
        }

        if ($this->status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return;
        }

        if ($status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_WARNING;
            return;
        }

        if ($this->status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return;
        }

        $this->status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;
    }

    // ########################################

    public function isProcessingItems()
    {
        return (bool)$this->isProcessingItems;
    }

    protected function setIsProcessingItems($isProcessingItems)
    {
        $this->isProcessingItems = (bool)$isProcessingItems;
    }

    // ########################################

    protected function filterLockedListingsProducts($listingsProducts)
    {
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $key => $listingProduct) {

            if (!in_array($this->getActionIdentifier(),array('stop','stop_and_remove'))) {
                if ($listingProduct->getChildObject()->isVariationProduct() &&
                    !$listingProduct->getChildObject()->isVariationMatched()) {
                    // M2ePro_TRANSLATIONS
                    // You have to select variation first.
                    $this->addListingsProductsLogsMessage(
                        $listingProduct, 'You have to select variation first.',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    unset($listingsProducts[$key]);
                    continue;
                }
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action') ||
                $listingProduct->isLockedObject($this->getActionIdentifier().'_action')) {
                // M2ePro_TRANSLATIONS
                // Another action is being processed. Try again when the action is completed.
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Another action is being processed. Try again when the action is completed.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                unset($listingsProducts[$key]);
                continue;
            }
        }

        return $listingsProducts;
    }

    // ########################################
}