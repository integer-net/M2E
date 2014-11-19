<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Translation_Product_Add_Dispatcher
{
    private $logsActionId = NULL;
    private $isProcessingItems = false;

    // ########################################

    /**
     * @param array|Ess_M2ePro_Model_Listing_Product $products
     * @param array $params
     * @return int
     */
    public function process($products, array $params = array())
    {
        $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $tempProducts = $this->prepareProducts($products);
        $sortedProducts = $this->sortProducts($tempProducts);

        $results = array();

        foreach ($sortedProducts as $chunk) {

            $listingId = (int)$chunk['listing_id'];
            $products = (array)$chunk['products'];

            if (count($products) <= 0) {
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

            $params['source_language'] = $chunk['language']['source'];
            $params['target_language'] = $chunk['language']['target'];
            $params['service']      = $chunk['service'];

            for ($i=0; $i<count($products);$i+=100) {
                $productsForRequest = array_slice($products,$i,100);
                $results[] = $this->processProducts($listingId, $productsForRequest, $params);
            }

            $needRemoveLockItem && $lockItem->isExist() && $lockItem->remove();
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    // ########################################

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
     * @param int $listingId
     * @param array $products
     * @param array $params
     * @return int
     */
    protected function processProducts($listingId, array $products,
                                       array $params = array())
    {
        try {

            $connector = new Ess_M2ePro_Model_Connector_Translation_Product_Add_Multiple($params,$products);
            $connector->process();

            $this->isProcessingItems = $connector->isProcessingItems();

            return $connector->getStatus();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $logModel->addListingMessage(
                $listingId,
                Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                $this->logsActionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT,
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

    protected function sortProducts($products)
    {
        $sortedProducts = array();

        foreach ($products as $product) {

            $listingId = $product->getListing()->getId();
            $translationData = $product->getSetting('additional_data',array('translation_service'),array());

            $key = $listingId
                .'_'.$translationData['from']['language']
                .'_'.$translationData['to']['language']
                .'_'.$product->getTranslationService();

            if (!isset($sortedProducts[$key])) {
                $sortedProducts[$key] = array(
                    'listing_id' => $listingId,
                    'language' => array(
                        'source' => $translationData['from']['language'],
                        'target' => $translationData['to']['language']
                    ),
                    'service' => $product->getTranslationService(),
                    'products'   => array()
                );
            }

            $sortedProducts[$key]['products'][] = $product;
        }

        return array_values($sortedProducts);
    }

    // ########################################

}