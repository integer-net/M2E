<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Play_Product_Dispatcher
{
    const ACTION_LIST    = 1;
    const ACTION_RELIST  = 2;
    const ACTION_REVISE  = 3;
    const ACTION_STOP    = 4;

    private $logsActionId = NULL;
    private $isProcessingItems = false;

    // ########################################

    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_Listing_Product $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $result = Ess_M2ePro_Model_Connector_Server_Play_Product_Requester::STATUS_ERROR;

        $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $products = $this->prepareProducts($products);
        $sortedProductsData = $this->sortProductsByAccountAndMarketplace($products);

        switch ($action) {
            case self::ACTION_LIST:
                $result = $this->processGroupedProducts($sortedProductsData,
                                                        100,
                                                        'Ess_M2ePro_Model_Connector_Server_Play_Product_List_Multiple',
                                                        $params);
                break;

            case self::ACTION_RELIST:
                $result = $this->processGroupedProducts($sortedProductsData,
                                                        100,
                                                        'Ess_M2ePro_Model_Connector_Server_Play_Product_Relist_Multiple',
                                                        $params);
                break;

            case self::ACTION_REVISE:
                $result = $this->processGroupedProducts($sortedProductsData,
                                                        100,
                                                        'Ess_M2ePro_Model_Connector_Server_Play_Product_Revise_Multiple',
                                                        $params);
                break;

            case self::ACTION_STOP:
                $result = $this->processGroupedProducts($sortedProductsData,
                                                        100,
                                                        'Ess_M2ePro_Model_Connector_Server_Play_Product_Stop_Multiple',
                                                        $params);
                break;

            default;
                $result = Ess_M2ePro_Model_Connector_Server_Play_Product_Requester::STATUS_ERROR;
                break;
        }

        return $result;
    }

    //-----------------------------------------

    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    public function isProcessingItems()
    {
        return (bool)$this->isProcessingItems;
    }

    // ########################################

    /**
     * @param array $sortedProductsData
     * @param int $maxProductsForOneRequest
     * @param string $connectorName
     * @param array $params
     * @return int
     */
    protected function processGroupedProducts(array $sortedProductsData,
                                              $maxProductsForOneRequest,
                                              $connectorName,
                                              array $params = array())
    {
        $results = array();

        foreach ($sortedProductsData as $tempProductsData) {

            $products = (array)$tempProductsData['products'];

            if (count($products) <= 0 || !class_exists($connectorName)) {
                continue;
            }

            $needRemoveLockItems = array();
            foreach ($products as $product) {

                /** @var $product Ess_M2ePro_Model_Listing_Product */
                if (isset($needRemoveLockItems[$product->getListingId()])) {
                    continue;
                }
                $lockItemParams = array(
                    'component' => Ess_M2ePro_Helper_Component_Play::NICK,
                    'id' => $product->getListingId()
                );
                $lockItem = Mage::getModel('M2ePro/Listing_LockItem',$lockItemParams);
                if ($lockItem->isExist()) {
                    if (!isset($params['status_changer']) ||
                        $params['status_changer'] != Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER
                    ) {
                        // Parser hack -> Mage::helper('M2ePro')->__('Listing "%listingId%" locked by other process.');
                        throw new LogicException('Listing "'.$product->getListingId().'" locked by other process.');
                    }
                    $lockItem->activate();
                } else {
                    $lockItem->create();
                    $lockItem->makeShutdownFunction();
                    $needRemoveLockItems[$product->getListingId()] = $lockItem;
                }
            }

            if (is_null($maxProductsForOneRequest)) {
                $results[] = $this->processProducts($products, $connectorName, $params);
            } else {
                for ($i=0; $i<count($products);$i+=$maxProductsForOneRequest) {
                    $productsForRequest = array_slice($products,$i,$maxProductsForOneRequest);
                    $results[] = $this->processProducts($productsForRequest, $connectorName, $params);
                }
            }

            foreach ($needRemoveLockItems as $lockItem) {
                $lockItem->isExist() && $lockItem->remove();
            }
        }

        return Ess_M2ePro_Model_Connector_Server_Play_Product_Requester::getMainStatus($results);
    }

    /**
     * @param array $products
     * @param string $connectorName
     * @param array $params
     * @return int
     */
    protected function processProducts(array $products, $connectorName, array $params = array())
    {
        try {

            $connector = new $connectorName($params,$products);
            $connector->process();

            $this->isProcessingItems = $connector->isProcessingItems();

            return $connector->getStatus();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);

            $tempListings = array();
            foreach ($products as $product) {
                /** @var $product Ess_M2ePro_Model_Listing_Product */
                if (isset($tempListings[$product->getListingId()])) {
                    continue;
                }
                $logModel->addListingMessage(
                    $product->getListingId(),
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                    $this->logsActionId,
                    Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN,
                    $exception->getMessage(),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                );
                $tempListings[$product->getListingId()] = true;
            }

            return Ess_M2ePro_Model_Connector_Server_Play_Product_Requester::STATUS_ERROR;
        }
    }

    // ########################################

    protected function prepareProducts($products)
    {
        $productsTemp = array();

        if (!is_array($products)) {
            $products = array($products);
        }

        $productsIdsTemp = array();
        foreach ($products as $product) {

            $tempProduct = NULL;
            if ($product instanceof Ess_M2ePro_Model_Listing_Product) {
                $tempProduct = $product;
            } else {
                $tempProduct = Mage::helper('M2ePro/Component_Play')->getObject('Listing_Product',(int)$product);
            }

            if (in_array((int)$tempProduct->getId(),$productsIdsTemp)) {
                continue;
            }

            $productsIdsTemp[] = (int)$tempProduct->getId();
            $productsTemp[] = $tempProduct;
        }

        return $productsTemp;
    }

    protected function sortProductsByAccountAndMarketplace($products)
    {
        $accountsAndMarketplaces = array();

        foreach ($products as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $accountId = $product->getListing()->getAccountId();
            $marketplaceId = $product->getListing()->getMarketplaceId();

            $tempKey = $accountId.'_'.$marketplaceId;

            if (!isset($accountsAndMarketplaces[$tempKey])) {
                $accountsAndMarketplaces[$tempKey] = array(
                    'account_id' => $accountId,
                    'marketplaces_id' => $marketplaceId,
                    'products' => array()
                );
            }

            $accountsAndMarketplaces[$tempKey]['products'][] = $product;
        }

        return array_values($accountsAndMarketplaces);
    }

    // ########################################

    public static function getActionTitle($action)
    {
        $title = Mage::helper('M2ePro')->__('Unknown');

        switch ($action) {
            case self::ACTION_LIST:   $title = Mage::helper('M2ePro')->__('Listing'); break;
            case self::ACTION_RELIST: $title = Mage::helper('M2ePro')->__('Relisting'); break;
            case self::ACTION_REVISE: $title = Mage::helper('M2ePro')->__('Revising'); break;
            case self::ACTION_STOP:   $title = Mage::helper('M2ePro')->__('Stopping'); break;
        }

        return $title;
    }

    // ########################################
}