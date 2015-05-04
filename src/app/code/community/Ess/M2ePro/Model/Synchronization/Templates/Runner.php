<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Templates_Runner
{
    private $items = array();

    private $lockItem = NULL;
    private $percentsStart = 0;
    private $percentsEnd = 100;

    private $maxProductsPerStep = 10;
    private $connectorModel = NULL;

    // ########################################

    public function setLockItem(Ess_M2ePro_Model_Synchronization_LockItem $object)
    {
        $this->lockItem = $object;
    }

    public function getLockItem()
    {
        return $this->lockItem;
    }

    // ----------------------------------------

    public function setPercentsStart($value)
    {
        $this->percentsStart = $value;
    }

    public function getPercentsStart()
    {
        return $this->percentsStart;
    }

    // ----------------------------------------

    public function setPercentsEnd($value)
    {
        $this->percentsEnd = $value;
    }

    public function getPercentsEnd()
    {
        return $this->percentsEnd;
    }

    // ----------------------------------------

    public function setMaxProductsPerStep($value)
    {
        $this->maxProductsPerStep = $value;
    }

    public function getMaxProductsPerStep()
    {
        return $this->maxProductsPerStep;
    }

    // ----------------------------------------

    public function setConnectorModel($value)
    {
        $this->connectorModel = $value;
    }

    public function getConnectorModel()
    {
        return $this->connectorModel;
    }

    // ########################################

    public function addProduct($product, $action, array $params = array())
    {
        if (isset($this->items[$product->getId()])) {

            $existedItem = $this->items[$product->getId()];

            if ($existedItem['action'] == $action) {

                $this->items[$product->getId()]['params'] =
                    $this->mergeParams($this->items[$product->getId()]['params'],$params);

                return true;
            }

            do {

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
                    $this->deleteProduct($existedItem['product']);
                    break;
                }

                if ($existedItem['action'] == Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
                    return false;
                }

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
                    $this->deleteProduct($existedItem['product']);
                    break;
                }

                if ($existedItem['action'] == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
                    return false;
                }

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
                    $this->deleteProduct($existedItem['product']);
                    break;
                }

                if ($existedItem['action'] == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
                    return false;
                }

            } while (false);
        }

        $this->items[$product->getId()] = array(
            'product' => $product,
            'action' => $action,
            'params' => $params
        );

        return true;
    }

    public function deleteProduct($product)
    {
        if (isset($this->items[$product->getId()])) {
            unset($this->items[$product->getId()]);
            return true;
        }

        return false;
    }

    //-----------------------------------------

    public function isExistProduct($product, $action, array $params = array())
    {
        if (!isset($this->items[$product->getId()]) ||
            $this->items[$product->getId()]['action'] != $action) {
            return false;
        }

        return $this->isConsistsParams($this->items[$product->getId()]['params'],$params);
    }

    public function resetProducts()
    {
        $this->items = array();
    }

    // ########################################

    public function execute()
    {
        $this->setPercents($this->getPercentsStart());

        $actions = array(
            Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
            Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
            Ess_M2ePro_Model_Listing_Product::ACTION_LIST
        );

        $results = array();

        $iteration = 0;
        $percentsForOneIteration = $this->getPercentsInterval() / count($actions);

        foreach ($actions as $action) {

            $tempResults = $this->executeAction($action,
                                                $this->getPercentsStart() + $iteration*$percentsForOneIteration,
                                                $this->getPercentsStart() + (++$iteration)*$percentsForOneIteration);

            $results = array_merge($results,$tempResults);
        }

        $this->setPercents($this->getPercentsEnd());
        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    public function executeAction($action, $percentsFrom, $percentsTo)
    {
        $this->setPercents($percentsFrom);

        $results = array();
        $combinations = $this->getCombinations($action);

        $totalProductsCount = 0;
        $processedProductsCount = 0;

        foreach ($combinations as $combination) {
            $totalProductsCount += count($combination['products']);
        }

        if (!$totalProductsCount) {
            return $results;
        }

        $percentsOneProduct = ($percentsTo - $percentsFrom)/$totalProductsCount;

        foreach ($combinations as $combination) {

            $combination['params']['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH;

            foreach (array_chunk($combination['products'],$this->getMaxProductsPerStep()) as $products) {

                $productsCount = count($products);
                $countString = $productsCount.' '.Mage::helper('M2ePro')->__('Product(s).');

                if ($productsCount < 10) {

                    $productsIds = array();
                    foreach ($products as $product) {
                        $productsIds[] = $product->getData('product_id');
                    }

                    $productsIds = implode('", "',$productsIds);
                    $countString = Mage::helper('M2ePro')->__('Product(s) with ID(s)')." \"{$productsIds}\".";
                }

                $this->setStatus(Ess_M2ePro_Model_Listing_Product::getActionTitle($action).
                                 ' '.$countString.
                                 ' '.Mage::helper('M2ePro')->__('Please wait...'));

                $results[] = Mage::getModel('M2ePro/'.$this->getConnectorModel())
                                        ->process($action, $products, $combination['params']);

                $processedProductsCount += count($products);
                $tempPercents = $percentsFrom + ($processedProductsCount * $percentsOneProduct);

                $this->setPercents($tempPercents > $percentsTo ? $percentsTo : $tempPercents);
                $this->activate();
            }
        }

        $this->setPercents($percentsTo);
        return $results;
    }

    // ########################################

    private function getParamsHash($params)
    {
        $hash = '';
        ksort($params);

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                foreach ($value as $key2 => $value2) {
                    $hash .= (string)$key.(string)$key2.(string)$value2;
                }
            } else {
                $hash .= (string)$key.(string)$value;
            }
        }

        return $hash ? md5($hash) : '';
    }

    private function mergeParams($existedParams, $newParams)
    {
        foreach ($newParams as $key => $value) {

            if (isset($existedParams[$key]) && is_array($existedParams[$key]) && is_array($value)) {
                $existedParams[$key] = array_merge($existedParams[$key],$value);
            } else {
                $existedParams[$key] = $value;
            }
        }

        return $existedParams;
    }

    private function isConsistsParams($existedParams, $checkedParams)
    {
        foreach ($checkedParams as $key => $value) {

            if (!isset($existedParams[$key])) {
                return false;
            }

            if (is_array($existedParams[$key]) && is_array($value)) {

                foreach ($value as $key2 => $value2) {

                    if (!isset($existedParams[$key][$key2])) {
                        return false;
                    }

                    if ($existedParams[$key][$key2] != $value2) {
                        return false;
                    }
                }

            } else {

                if ($existedParams[$key] != $value) {
                    return false;
                }
            }
        }

        return true;
    }

    // ----------------------------------------

    private function setPercents($value)
    {
        if (!$this->getLockItem()) {
            return;
        }

        $this->getLockItem()->setPercents($value);
    }

    private function setStatus($text)
    {
        if (!$this->getLockItem()) {
            return;
        }

        $this->getLockItem()->setStatus($text);
    }

    private function activate()
    {
        if (!$this->getLockItem()) {
            return;
        }

        $this->getLockItem()->activate();
    }

    // ----------------------------------------

    private function getCombinations($action)
    {
        $combinations = array();

        foreach ($this->items as $item) {

            if ($item['action'] != $action) {
                continue;
            }

            $combinationIndex = NULL;
            $paramsHash = $this->getParamsHash($item['params']);

            for ($i=0; $i<count($combinations); $i++) {
                if ($combinations[$i]['params_hash'] == $paramsHash) {
                    $combinationIndex = $i;
                    break;
                }
            }

            if (is_null($combinationIndex)) {
                $combinations[] = array(
                    'products' => array($item['product']),
                    'params' => $item['params'],
                    'params_hash' => $paramsHash
                );
            } else {
                $combinations[$combinationIndex]['products'][] = $item['product'];
            }
        }

        return $combinations;
    }

    private function getPercentsInterval()
    {
        return $this->getPercentsEnd() - $this->getPercentsStart();
    }

    // ########################################
}