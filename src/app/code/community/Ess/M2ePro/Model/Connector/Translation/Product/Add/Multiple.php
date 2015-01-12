<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Translation_Product_Add_Multiple
    extends Ess_M2ePro_Model_Connector_Translation_Requester
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    protected $logsActionId = NULL;
    protected $neededRemoveLocks = array();

    protected $isProcessingItems = false;
    protected $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    protected $listingsProducts = array();
    protected $listingProductRequestsData = array();

    const MAX_LIFE_TIME_INTERVAL = 864000; // 10 days

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

        $translationData    = $listingsProducts[0]->getSetting('additional_data',array('translation_service'),array());
        $tempSourceLanguage = $translationData['from']['language'];
        $tempTargetLanguage = $translationData['to']['language'];
        $tempService      = $listingsProducts[0]->getTranslationService();

        $tempListing = $listingsProducts[0]->getListing();
        foreach($listingsProducts as $listingProduct) {
            if ($tempListing->getId() != $listingProduct->getListing()->getId()) {
                throw new Exception('Product connector has received products from different listings');
            }

            $translationData = $listingProduct->getSetting('additional_data',array('translation_service'),array());

            if ($tempSourceLanguage != $translationData['from']['language']) {
                throw new Exception('Product connector has received products from different source languages');
            }

            if ($tempTargetLanguage != $translationData['to']['language']) {
                throw new Exception('Product connector has received products from different target languages');
            }

            if ($tempService != $listingProduct->getTranslationService()) {
                throw new Exception('Product connector has received products from different translation services');
            }
        }

        parent::__construct($params, $listingsProducts[0]->getListing()->getAccount());

        $this->marketplace = $listingsProducts[0]->getListing()->getMarketplace();

        $listingsProducts = $this->filterLockedListingsProducts($listingsProducts);
        $listingsProducts = $this->prepareListingsProducts($listingsProducts);

        $this->listingsProducts = array_values($listingsProducts);
    }

    public function __destruct()
    {
        $this->checkUnlockListings();
    }

    // ########################################

    protected function getProcessingExpirationInterval()
    {
        return Ess_M2ePro_Model_Connector_Translation_Product_Add_Multiple::MAX_LIFE_TIME_INTERVAL;
    }

    // ########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    protected function getResponserModel()
    {
        return 'Translation_Product_Add_MultipleResponser';
    }

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

    // ----------------------------------------

    public function isProcessingItems()
    {
        return (bool)$this->isProcessingItems;
    }

    protected function setIsProcessingItems($isProcessingItems)
    {
        $this->isProcessingItems = (bool)$isProcessingItems;
    }

    // ########################################

    protected function getRequestData()
    {
         $requestData = array(
            'service'      => $this->params['service'],
            'source_language' => $this->params['source_language'],
            'target_language' => $this->params['target_language'],
            'products' => array()
        );

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $tempData = $listingProduct->getSetting('additional_data', array('translation_service', 'from'), array());

            $listingProductRequestData = array(
                'title'          => $tempData['description']['title'],
                'subtitle'       => $tempData['description']['subtitle'],
                'description'    => $tempData['description']['description'],
                'sku'            => $tempData['sku'],
                'item_specifics' => $tempData['item_specifics'],
                'category'       => $tempData['category']
            );

            $this->listingProductRequestsData[$listingProduct->getId()] = $listingProductRequestData;
            $requestData['products'][] = $listingProductRequestData;
        }

        return $requestData;
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

        $this->updateOrLockListingProducts();
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
            $tempProductsData[$listingProduct->getId()] =
                isset($this->listingProductRequestsData[$listingProduct->getId()])
                    ? $this->listingProductRequestsData[$listingProduct->getId()]
                    : array();
        }

        return array(
            'account_id'     => $this->account->getId(),
            'marketplace_id' => $this->marketplace->getId(),
            'logs_action_id' => $this->logsActionId,
            'status_changer' => $this->params['status_changer'],
            'params'         => $this->params,
            'products'       => $tempProductsData
        );
    }

    protected function setLocks($hash)
    {
        $tempListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->addObjectLock(NULL,$hash);
            $listingProduct->addObjectLock('in_action',$hash);
            $listingProduct->addObjectLock('translation_action',$hash);

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->addObjectLock(NULL,$hash);
            $listingProduct->getListing()->addObjectLock('products_in_action',$hash);
            $listingProduct->getListing()->addObjectLock('products_translation_action',$hash);

            $tempListings[$listingProduct->getListingId()] = true;
        }

        $this->account->addObjectLock('products_in_action', $hash);
        $this->account->addObjectLock('products_translation_action', $hash);

        $this->marketplace->addObjectLock('products_in_action', $hash);
        $this->marketplace->addObjectLock('products_translation_action', $hash);
    }

    // ########################################

    protected function updateOrLockListingProducts()
    {
        foreach ($this->listingsProducts as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$product->getId());

            if (!$lockItem->isExist()) {
                $lockItem->create();
                $lockItem->makeShutdownFunction();
                $this->neededRemoveLocks[$product->getId()] = $lockItem;
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
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT;

        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

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

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logModel->addProductMessage($listingProduct->getListingId() ,
                                     $listingProduct->getProductId() ,
                                     $listingProduct->getId() ,
                                     $initiator ,
                                     $this->logsActionId ,
                                     $action , $text, $type , $priority);
    }

    // ########################################

    protected function filterLockedListingsProducts($listingsProducts)
    {
        foreach ($listingsProducts as $key => $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action') ||
                $listingProduct->isLockedObject('translation_action')) {

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

    protected function prepareListingsProducts($listingProducts)
    {
        foreach ($listingProducts as $key => $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->getChildObject()->isTranslatable()) {

                // M2ePro_TRANSLATIONS
                // 'Product is translated or being translated'
                $this->addListingsProductsLogsMessage($listingProduct, 'Product is translated or being translated',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
                unset($listingProducts[$key]);
                continue;
            }

            $listingProduct->getChildObject()->setData(
                'translation_status',
                Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_IN_PROGRESS
            )->save();
        }

        return array_values($listingProducts);
    }

    // ########################################
}