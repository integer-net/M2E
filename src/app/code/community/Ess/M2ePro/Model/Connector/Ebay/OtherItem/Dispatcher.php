<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Dispatcher
{
    private $logsActionId = NULL;

    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_Listing_Other $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;

        $this->logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $products = $this->prepareProducts($products);

        switch ($action) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $result = $this->processProducts(
                    $products, 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Relist_Single', $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $result = $this->processProducts(
                    $products, 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Stop_Single', $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $result = $this->processProducts(
                    $products, 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Revise_Single', $params
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
     * @param array $products
     * @param string $connectorNameSingle
     * @param array $params
     * @return int
     * @throws LogicException
     */
    protected function processProducts(array $products, $connectorNameSingle, array $params = array())
    {
        $results = array();

        if (count($products) == 0) {
            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        if (!class_exists($connectorNameSingle)) {
            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        $needRemoveLockItem = false;

        $lockItemParams = array('component' => Ess_M2ePro_Helper_Component_Ebay::NICK);
        $lockItem = Mage::getModel('M2ePro/Listing_Other_LockItem',$lockItemParams);

        if ($lockItem->isExist()) {
            if (!isset($params['status_changer']) ||
                $params['status_changer'] != Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
                // M2ePro_TRANSLATIONS
                // Other listings locked by other process.
                throw new LogicException("Other listings locked by other process.");
            }
            $lockItem->activate();
        } else {
            $lockItem->create();
            $lockItem->makeShutdownFunction();
            $needRemoveLockItem = true;
        }

        try {

            foreach ($products as $product) {
                $connector = new $connectorNameSingle($params,$product);
                $connector->process();
                $results[] = $connector->getStatus();
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $logModel->addGlobalMessage(
                Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                $this->logsActionId,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNKNOWN,
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            $results[] = Ess_M2ePro_Helper_Data::STATUS_ERROR;
        }

        $needRemoveLockItem && $lockItem->isExist() && $lockItem->remove();

        return Mage::helper('M2ePro')->getMainStatus($results);
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
            if ($product instanceof Ess_M2ePro_Model_Listing_Other) {
                $tempProduct = $product;
            } else {
                $tempProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Other',(int)$product);
            }

            if (in_array((int)$tempProduct->getId(),$productsIdsTemp)) {
                continue;
            }

            $productsIdsTemp[] = (int)$tempProduct->getId();
            $productsTemp[] = $tempProduct;
        }

        return $productsTemp;
    }

    // ########################################
}