<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Play_Product_Requester
    extends Ess_M2ePro_Model_Connector_Play_Requester
{
    protected $logsActionId = NULL;

    protected $isProcessingItems = false;
    protected $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
    protected $listingsProducts = array();
    protected $listingProductRequestsData = array();

    // ########################################

    /**
     * @param array $params
     * @param Ess_M2ePro_Model_Listing_Product[] $listingsProducts
     * @throws Exception
     */
    public function __construct(array $params = array(), array $listingsProducts)
    {
        if (!isset($params['logs_action_id']) || !isset($params['status_changer'])) {
            throw new Exception('Product connector has not received some params');
        }

        $this->logsActionId = (int)$params['logs_action_id'];

        if (empty($listingsProducts)) {
            throw new Exception('Product connector has received empty array');
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = reset($listingsProducts)->getAccount();

        foreach($listingsProducts as $listingProduct) {

            $listingProduct->loadInstance($listingProduct->getId());

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                throw new Exception('Product connector has received invalid product data type');
            }

            if ($account->getId() != $listingProduct->getListing()->getAccountId()) {
                throw new Exception('Product connector has received products from different accounts');
            }

            $this->listingsProducts[$listingProduct->getId()] = $listingProduct;
        }

        parent::__construct($params,$account);
    }

    // ########################################

    public function process()
    {
        try {

            $this->setIsProcessingItems(false);
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);

            $this->filterLockedListingsProducts();
            $this->lockListingsProducts();
            $this->filterManualListingsProducts();

            if (empty($this->listingsProducts)) {
                return;
            }

            $this->setIsProcessingItems(true);

            parent::process();

        } catch (Exception $exception) {
            $this->unlockListingsProducts();
            throw $exception;
        }

        // When all items are failed in response
        (isset($this->response['data']['messages'])) && $tempMessages = $this->response['data']['messages'];
        if (isset($tempMessages) && is_array($tempMessages) && count($tempMessages) > 0) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
        }

        $this->unlockListingsProducts();
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

        Mage::helper('M2ePro/Component_Play')->getMarketplace()->addObjectLock(
            'products_in_action', $hash
        );
        Mage::helper('M2ePro/Component_Play')->getMarketplace()->addObjectLock(
            'products_'.$actionIdentifier.'_action', $hash
        );
    }

    // ########################################

    protected function lockListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Play::NICK.'_listing_product_'.$listingProduct->getId());

            $lockItem->create();
            $lockItem->makeShutdownFunction();
        }
    }

    protected function unlockListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Play::NICK.'_listing_product_'.$listingProduct->getId());

            $lockItem->remove();
        }
    }

    // ########################################

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                      $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                      $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
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
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

        $logModel->addProductMessage($listingProduct->getListingId() ,
                                     $listingProduct->getProductId() ,
                                     $listingProduct->getId() ,
                                     $initiator ,
                                     $this->logsActionId ,
                                     $action , $text, $type , $priority);
    }

    // ########################################

    abstract protected function getActionIdentifier();

    abstract protected function getListingsLogsCurrentAction();

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

    protected function filterLockedListingsProducts()
    {
        foreach ($this->listingsProducts as $key => $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Play::NICK.'_listing_product_'.$listingProduct->getId());

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action') ||
                $listingProduct->isLockedObject($this->getActionIdentifier().'_action') ||
                $lockItem->isExist()
            ) {

                // M2ePro_TRANSLATIONS
                // Another action is being processed. Try again when the action is completed.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'Another action is being processed. Try again when the action is completed.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                unset($this->listingsProducts[$listingProduct->getId()]);
                continue;
            }
        }
    }

    abstract protected function filterManualListingsProducts();

    // ----------------------------------------

    protected function removeAndUnlockListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Play::NICK.'_listing_product_'.$listingProduct->getId());
        $lockItem->remove();

        unset($this->listingsProducts[$listingProduct->getId()]);
    }

    // ########################################

    public function checkQtyWarnings()
    {
        foreach ($this->listingsProducts as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $productsIds = array();

            if ($listingProduct->getChildObject()->isVariationProduct()) {
                $variations = $listingProduct->getVariations(true);

                foreach ($variations as $variation) {
                    $options = $variation->getOptions();
                    foreach ($options as $option) {
                        $productsIds[] = $option['product_id'];
                    }
                }
            } else {
                $productsIds[] = $listingProduct->getProductId();
            }

            $qtyMode = $listingProduct->getChildObject()->getPlaySellingFormatTemplate()->getQtyMode();
            if ($qtyMode == Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
                $qtyMode == Ess_M2ePro_Model_Play_Template_SellingFormat::QTY_MODE_PRODUCT) {

                $productsIds = array_unique($productsIds);
                $qtyWarnings = array();

                $listingProductId = $listingProduct->getId();
                $storeId = $listingProduct->getListing()->getStoreId();

                foreach ($productsIds as $productId) {
                    if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics
                    [$listingProductId][$productId][$storeId]['qty'])) {

                        $qtys = Ess_M2ePro_Model_Magento_Product::$statistics
                        [$listingProductId][$productId][$storeId]['qty'];
                        $qtyWarnings = array_unique(array_merge($qtyWarnings, array_keys($qtys)));
                    }

                    if (count($qtyWarnings) === 2) {
                        break;
                    }
                }

                foreach ($qtyWarnings as $qtyWarningType) {
                    $this->addQtyWarnings($qtyWarningType, $listingProduct);
                }
            }
        }
    }

    public function addQtyWarnings($type, $listingProduct)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
        // M2ePro_TRANSLATIONS
        // During the quantity calculation the settings in the "Manage Stock No" field were taken into consideration.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'During the quantity calculation the settings in the "Manage Stock No" '.
                'field were taken into consideration.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            // M2ePro_TRANSLATIONS
            // During the quantity calculation the settings in the "Backorders" field were taken into consideration.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'During the quantity calculation the settings in the "Backorders" '.
                'field were taken into consideration.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }
    }

    // ########################################
}