<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher
{
    private $logsActionId = NULL;

    // ########################################

    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_Listing_Product $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;

        $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $products = $this->prepareProducts($products);
        $listings = $this->sortProductsByListings($products);

        switch ($action) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $result = $this->processListings(
                    $listings, 5, 'Ess_M2ePro_Model_Connector_Ebay_Item_List_Single',
                    'Ess_M2ePro_Model_Connector_Ebay_Item_List_Multiple', $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $result = $this->processListings(
                    $listings, NULL, 'Ess_M2ePro_Model_Connector_Ebay_Item_Relist_Single',
                    NULL, $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $result = $this->processListings(
                    $listings, NULL, 'Ess_M2ePro_Model_Connector_Ebay_Item_Revise_Single',
                    NULL, $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $result = $this->processListings(
                    $listings, 10, 'Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Single',
                    'Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Multiple', $params
                );
                break;

            default;
                $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;
                break;
        }

        return $result;
    }

    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    // ########################################

    /**
     * @param array $listings
     * @param int $maxProductsForOneRequest
     * @param string $connectorNameSingle
     * @param string|null $connectorNameMultiple
     * @param array $params
     * @return int
     * @throws LogicException
     */
    protected function processListings(array $listings,
                                       $maxProductsForOneRequest,
                                       $connectorNameSingle,
                                       $connectorNameMultiple = NULL,
                                       array $params = array())
    {
        $results = array();

        foreach ($listings as $listing) {

            $listingId = (int)$listing['id'];
            $products = (array)$listing['products'];

            if (count($products) == 0) {
                continue;
            }

            if (!class_exists($connectorNameSingle)) {
                continue;
            }

            if (!is_null($connectorNameMultiple) && !class_exists($connectorNameMultiple)) {
                continue;
            }

            $needRemoveLockItem = false;

            $lockItemParams = array(
                'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'id' => (int)$listingId
            );
            $lockItem = Mage::getModel('M2ePro/Listing_LockItem',$lockItemParams);

            if ($lockItem->isExist()) {
                if (!isset($params['status_changer']) ||
                    $params['status_changer'] != Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
                    // M2ePro_TRANSLATIONS
                    // Listing "%listing_id%" locked by other process.
                    throw new LogicException("Listing \"{$listingId}\" locked by other process.");
                }
                $lockItem->activate();
            } else {
                $lockItem->create();
                $lockItem->makeShutdownFunction();
                $needRemoveLockItem = true;
            }

            if (is_null($maxProductsForOneRequest)) {
                $results[] = $this->processProducts(
                    $listingId, $products, $connectorNameSingle, $connectorNameMultiple, $params
                );
            } else {
                for ($i=0; $i<count($products);$i+=$maxProductsForOneRequest) {
                    $productsForRequest = array_slice($products,$i,$maxProductsForOneRequest);
                    $results[] = $this->processProducts(
                        $listingId, $productsForRequest, $connectorNameSingle, $connectorNameMultiple, $params
                    );
                }
            }

            $needRemoveLockItem && $lockItem->isExist() && $lockItem->remove();
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    /**
     * @param int $listingId
     * @param array $products
     * @param string $connectorNameSingle
     * @param string|null $connectorNameMultiple
     * @param array $params
     * @return int
     */
    protected function processProducts($listingId, array $products,
                                       $connectorNameSingle,
                                       $connectorNameMultiple = NULL,
                                       array $params = array())
    {
        try {

            if (count($products) > 1) {

                if (is_null($connectorNameMultiple)) {

                    $results = array();
                    foreach ($products as $product) {
                        $results[] = $this->processProducts(
                            $listingId, array($product), $connectorNameSingle, $connectorNameMultiple, $params
                        );
                    }
                    return Mage::helper('M2ePro')->getMainStatus($results);

                } else {

                    $productsInstances = array();
                    foreach ($products as $product) {
                        $productsInstances[] = $product;
                    }
                    $connector = new $connectorNameMultiple($params,$productsInstances);

                }

            } else {
                $productInstance = $products[0];
                $connector = new $connectorNameSingle($params,$productInstance);
            }

            $connector->process();

            return $connector->getStatus();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $logModel->addListingMessage(
                $listingId,
                Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                $this->logsActionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN,
                $exception->getMessage(),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            return Ess_M2ePro_Helper_Data::STATUS_ERROR;
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
                $tempProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product',(int)$product);
            }

            if (in_array((int)$tempProduct->getId(),$productsIdsTemp)) {
                continue;
            }

            $productsIdsTemp[] = (int)$tempProduct->getId();
            $productsTemp[] = $tempProduct;
        }

        return $productsTemp;
    }

    protected function sortProductsByListings($products)
    {
        $listings = array();

        foreach ($products as $product) {
            $listingId = $product->getListing()->getId();
            if (!isset($listings[$listingId])) {
                $listings[$listingId] = array(
                    'id' => $listingId,
                    'products' => array()
                );
            }
            $listings[$listingId]['products'][] = $product;
        }

        return array_values($listings);
    }

    // ########################################
}