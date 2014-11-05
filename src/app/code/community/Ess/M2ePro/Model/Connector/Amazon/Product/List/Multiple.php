<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_List_Multiple
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Requester
{
    // ########################################

    private $skusToCheck = array();

    // ########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'list';
    }

    protected function getResponserModel()
    {
        return 'Amazon_Product_List_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function prepareListingsProducts($listingProducts)
    {
        $this->params['list_types'] = array();

        foreach ($listingProducts as $key => $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isNotListed()) {

                // M2ePro_TRANSLATIONS
                // Item is already on Amazon, or not available.
                $this->addListingsProductsLogsMessage($listingProduct, 'Item is already on Amazon, or not available.',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                unset($listingProducts[$key]);
                continue;
            }

            $addingSku = $listingProduct->getChildObject()->getSku();
            empty($addingSku) && $addingSku = $listingProduct->getChildObject()->getAddingSku();

            if (!$this->validateSku($addingSku,$listingProduct)) {
                unset($listingProducts[$key]);
                continue;
            }

            if ($this->isSkuExistsInM2ePro($addingSku,$listingProduct)) {

                if ($listingProduct->getChildObject()->getAmazonListing()->isGenerateSkuModeNo()) {
                    unset($listingProducts[$key]);
                    continue;
                }

                $addingSku = $this->generateSku($listingProduct);

                if ($addingSku === false) {
                    // -> Mage::helper('M2ePro')->__('Can\'t generate SKU.');
                    $this->addListingsProductsLogsMessage(
                        $listingProduct, 'Can\'t generate SKU.',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    unset($listingProducts[$key]);
                    continue;
                }
            }

            $this->skusToCheck[$addingSku] = true;
            $listingProduct->setData('sku',$addingSku);
        }

        $this->checkSkuExistence($listingProducts);

        foreach ($listingProducts as $key => $listingProduct) {

            // exception happened
            if (is_null($listingProduct->getData('found_on_amazon'))) {
                unset($listingProducts[$key]);
                continue;
            }

            if ($listingProduct->getData('found_on_amazon')) {
                $this->linkItem($listingProduct);
                unset($listingProducts[$key]);
                continue;
            }

            if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
                $listType = $this->getListTypeChangerUser($listingProduct);
            } else {
                $listType = $this->getListTypeChangerAutomatic($listingProduct);
            }

            if ($listType === false) {
                unset($listingProducts[$key]);
                continue;
            }

            if (!$this->validateConditions($listingProduct)) {
                unset($listingProducts[$key]);
                continue;
            }

            $price = $listingProduct->getChildObject()->getPrice();

            if ($price <= 0) {

            //->__('The price must be greater than 0. Please, check the Selling Format Template and Product settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The price must be greater than 0. Please, check the Selling Format Template and Product settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                unset($listingProducts[$key]);
                continue;
            }

            $this->params['list_types'][$listingProduct->getId()] = $listType;
        }

        return array_values($listingProducts);
    }

    // ########################################

    protected function getRequestData()
    {
        $tempSkus = array();

        $requestData = array(
            'items' => array()
        );

        foreach ($this->listingsProducts as $listingProduct) {

            $tempSkus[] = $listingProduct->getData('sku');

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $nativeData = Mage::getModel('M2ePro/Connector_Amazon_Product_Helper')
                                         ->getListRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        $this->checkQtyWarnings();

        $this->addSkusToQueue($tempSkus);

        return $requestData;
    }

    // ########################################

    private function getListTypeChangerUser(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $generalId = $listingProduct->getChildObject()->getGeneralId();

        if (!empty($generalId)) {
            if (!$this->validateGeneralId($generalId)) {
                // M2ePro_TRANSLATIONS
                // ASIN/ISBN has a wrong format.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'ASIN/ISBN has a wrong format.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_GENERAL_ID;
        }

        $worldWideId = $listingProduct->getChildObject()->getWorldWideId();

        if (!empty($worldWideId)) {
            if (!$this->validateWorldWideId($worldWideId)) {
                // M2ePro_TRANSLATIONS
                // UPC/EAN has a wrong format.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'UPC/EAN has a wrong format.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_WORLDWIDE_ID;
        }

        $templateNewProductId = $listingProduct->getChildObject()->getTemplateNewProductId();

        if (empty($templateNewProductId)) {
            // M2ePro_TRANSLATIONS
            // ASIN/ISBN or New ASIN template is required.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'ASIN/ISBN or New ASIN template is required.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            return false;
        }

        $worldWideId = $listingProduct->getChildObject()->getTemplateNewProductSource()->getWorldWideId();
        $isWorldWideIdValid = !empty($worldWideId) && $this->validateWorldWideId($worldWideId);

        if ($isWorldWideIdValid && $this->isWorldWideIdAlreadyExists($worldWideId,$listingProduct)) {
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_TEMPLATE_NEW_PRODUCT_WORLDWIDE_ID;
        }

        $registeredParameter = $listingProduct->getChildObject()->getTemplateNewProduct()->getRegisteredParameter();

        if (!$registeredParameter && !$isWorldWideIdValid) {
            // M2ePro_TRANSLATIONS
            // Valid EAN/UPC or Product ID Override is required for adding new ASIN. Please check Template Settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
            'Valid EAN/UPC or Product ID Override is required for adding new ASIN. Please check Template Settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            return false;
        }

        if (!empty($worldWideId) && !$isWorldWideIdValid) {
            // M2ePro_TRANSLATIONS
            // UPC/EAN has a wrong format. Please check New ASIN Template Settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'UPC/EAN has a wrong format. Please check New ASIN Template Settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            return false;
        }

        return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_TEMPLATE_NEW_PRODUCT;
    }

    private function getListTypeChangerAutomatic(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $generalId = $listingProduct->getChildObject()->getGeneralId();

        if (!empty($generalId)) {
            if (!$this->validateGeneralId($generalId)) {
                // M2ePro_TRANSLATIONS
                // ASIN/ISBN has a wrong format.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'ASIN/ISBN has a wrong format.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_GENERAL_ID;
        }

        $worldWideId = $listingProduct->getChildObject()->getWorldWideId();

        if (!empty($worldWideId)) {
            if (!$this->validateWorldWideId($worldWideId)) {
                // M2ePro_TRANSLATIONS
                // UPC/EAN has a wrong format.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'UPC/EAN has a wrong format.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_WORLDWIDE_ID;
        }

        $templateNewProductId = $listingProduct->getChildObject()->getTemplateNewProductId();

        if (!empty($templateNewProductId)) {

            $worldWideId = $listingProduct->getChildObject()->getTemplateNewProductSource()->getWorldWideId();
            $isWorldWideIdValid = !empty($worldWideId) && $this->validateWorldWideId($worldWideId);

            if ($isWorldWideIdValid && $this->isWorldWideIdAlreadyExists($worldWideId,$listingProduct)) {
                return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_TEMPLATE_NEW_PRODUCT_WORLDWIDE_ID;
            }

            $registeredParameter = $listingProduct->getChildObject()->getTemplateNewProduct()->getRegisteredParameter();

            if (!$registeredParameter && !$isWorldWideIdValid) {
                // M2ePro_TRANSLATIONS
    // Valid EAN/UPC or Product ID Override is required for adding new ASIN. Please check Template Settings.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
            'Valid EAN/UPC or Product ID Override is required for adding new ASIN. Please check Template Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }

            if (!empty($worldWideId) && !$isWorldWideIdValid) {
                // M2ePro_TRANSLATIONS
                // UPC/EAN has a wrong format. Please check New ASIN Template Settings.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'UPC/EAN has a wrong format. Please check New ASIN Template Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }

            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_TEMPLATE_NEW_PRODUCT;
        }

        $generalId = $listingProduct->getChildObject()->getAddingGeneralId();

        if (!empty($generalId)) {
            if (!$this->validateGeneralId($generalId)) {
        // M2ePro_TRANSLATIONS
        // ASIN/ISBN has a wrong format. Please check Search Settings for ASIN / ISBN  in Listing -> Channel Settings.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
        'ASIN/ISBN has a wrong format. Please check Search Settings for ASIN / ISBN  in Listing -> Channel Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_GENERAL_ID;
        }

        $worldWideId = $listingProduct->getChildObject()->getAddingWorldWideId();

        if (!empty($worldWideId)) {
            if (!$this->validateWorldWideId($worldWideId)) {
        // M2ePro_TRANSLATIONS
        // UPC/EAN has a wrong format. Please check Search Settings for ASIN / ISBN  in Listing -> Channel Settings.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
            'UPC/EAN has a wrong format. Please check Search Settings for ASIN / ISBN  in Listing -> Channel Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Connector_Amazon_Product_Helper::LIST_TYPE_WORLDWIDE_ID;
        }

        // M2ePro_TRANSLATIONS
        // ASIN/ISBN or New ASIN template is required.
        $this->addListingsProductsLogsMessage(
            $listingProduct,
            'ASIN/ISBN or UPC/EAN or New ASIN template is required.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );

        return false;
    }

    // ########################################

    private function validateGeneralId($generalId)
    {
        $isAsin = Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId);

        if (!$isAsin) {

            $isIsbn = Mage::helper('M2ePro')->isISBN($generalId);

            if (!$isIsbn) {
                return false;
            }
        }

        return true;
    }

    private function validateWorldWideId($worldWideId)
    {
        $isUpc = Mage::helper('M2ePro')->isUPC($worldWideId);

        if (!$isUpc) {

            $isEan = Mage::helper('M2ePro')->isEAN($worldWideId);

            if (!$isEan) {
                return false;
            }
        }

        return true;
    }

    private function validateConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $addingCondition = $listingProduct->getChildObject()->getCondition();
        $validConditions = $listingProduct->getListing()->getChildObject()->getConditionValues();

        if (empty($addingCondition) || !in_array($addingCondition,$validConditions)) {
            // M2ePro_TRANSLATIONS
            // Condition is invalid or missed. Please, check Listing Channel and product settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Condition is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingConditionNote = $listingProduct->getChildObject()->getConditionNote();

        if (is_null($addingConditionNote)) {
            // M2ePro_TRANSLATIONS
            // Condition note is invalid or missed. Please, check Listing Channel and product settings.
            $this->addListingsProductsLogsMessage(
            $listingProduct, 'Condition note is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (!empty($addingConditionNote) && strlen($addingConditionNote) > 2000) {
            // M2ePro_TRANSLATIONS
            // The length of condition note must be less than 2000 characters.
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'The length of condition note must be less than 2000 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        return true;
    }

    // ########################################

    private function isWorldWideIdAlreadyExists($worldwideId,Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$worldwideId) {
            return false;
        }

        /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
        $results = $dispatcher->runManual($listingProduct,$worldwideId);

        if (empty($results)) {
            return false;
        }

        return true;
    }

    // ########################################

    private function validateSku($sku, Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (empty($sku)) {

            // -> Mage::helper('M2ePro')->__('SKU is not provided. Please, check Listing settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'SKU is not provided. Please, check Listing settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (strlen($sku) > Ess_M2ePro_Model_Amazon_Listing_Product::SKU_MAX_LENGTH) {

            // M2ePro_TRANSLATIONS
            // The length of sku must be less than 40 characters.
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'The length of sku must be less than 40 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        return true;
    }

    // ########################################

    private function checkSkuExistence($listingProducts)
    {
        $listingProductsPacks = array_chunk($listingProducts,20,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            foreach ($listingProductsPack as $key => $listingProduct) {
                $skus[$key] = $listingProduct->getData('sku');
            }

            try {

                $countTriedTemp = 0;

                do {

                    $countTriedTemp != 0 && sleep(3);

                    /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
                    $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
                    $response = $dispatcherObject->processVirtual(
                        'product','search','asinBySku',
                        array('items' => $skus),'items',
                        $this->account->getId()
                    );

                } while (is_null($response) && ++$countTriedTemp <= 3);

                if (is_null($response)) {
                    throw new Exception('Requests are throttled many times.');
                }

            } catch (Exception $exception) {

                Mage::helper('M2ePro/Module_Exception')->process($exception,true);

                foreach ($listingProductsPack as $listingProduct) {

                    $this->addListingsProductsLogsMessage(
                        $listingProduct, Mage::helper('M2ePro')->__($exception->getMessage()),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );
                }

                continue;
            }

            foreach($response as $key => $value) {
                if ($value === false || empty($value['asin']) ) {
                    $listingProductsPack[$key]->setData('found_on_amazon',false);
                } else {
                    $listingProductsPack[$key]->setData('found_on_amazon',true);
                    $listingProductsPack[$key]->setData('general_id',$value['asin']);
                }
            }
        }

        return $listingProducts;
    }

    // ----------------------------------------

    private function isSkuExistsInM2ePro($sku, Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // check in 3rd party by account

        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Other')
            ->addFieldToFilter('sku',$sku)
            ->addFieldToFilter('account_id',$this->account->getId());

        if ($listingOtherCollection->getSize() > 0) {

            if ($listingProduct->getChildObject()->getAmazonListing()->isGenerateSkuModeNo()) {

                $message = Mage::helper('M2ePro')->__('The same Merchant SKU was found among 3rd Party Listings. ');
                $message.= Mage::helper('M2ePro')->__('Merchant SKU must be unique for each Amazon item.');

                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    $message,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            return true;
        }

        // check in M2ePro listings by account

        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Product');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        $listingProductCollection
            ->getSelect()
            ->join(array('l'=>$listingTable),'`main_table`.`listing_id` = `l`.`id`',array());

        $listingProductCollection
            ->addFieldToFilter('sku',$sku)
            ->addFieldToFilter('account_id',$this->account->getId());

        if ($listingProductCollection->getSize() > 0) {

            if ($listingProduct->getChildObject()->getAmazonListing()->isGenerateSkuModeNo()) {

                $message = Mage::helper('M2ePro')->__('The same Merchant SKU was found among M2E Listings. ');
                $message.= Mage::helper('M2ePro')->__('Merchant SKU must be unique for each Amazon item.');

                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    $message,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            return true;
        }

        // check in queue of SKUs by account

        $queue = $this->getQueueOfSkus();
        if (in_array($sku,$queue,true) || isset($this->skusToCheck[$sku])) {

            if ($listingProduct->getChildObject()->getAmazonListing()->isGenerateSkuModeNo()) {
                // M2ePro_TRANSLATIONS
                // The product with the same Merchant SKU is being listed now. SKU must be unique for each Amazon item.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
            'The product with the same Merchant SKU is being listed now. SKU must be unique for each Amazon item.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            return true;
        }

        return false;
    }

    // ########################################

    private function generateSku(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $countTriedTemp = 0;
        do {
            $newSku  = $listingProduct->getChildObject()->getAddingSku();
            $newSku .= '_' . $listingProduct->getProductId() . '_' . $listingProduct->getId();

            if (strlen($newSku) >= (Ess_M2ePro_Model_Amazon_Listing_Product::SKU_MAX_LENGTH - 5)) {
                $newSku = 'SKU_' .$listingProduct->getProductId() . '_' . $listingProduct->getId();
            }

            $newSku = $listingProduct->getChildObject()->createRandomSku($newSku);

        } while ($this->isSkuExistsInM2ePro($newSku,$listingProduct) && ++$countTriedTemp <= 5);

        if ($countTriedTemp >= 5) {
            return false;
        }

        return $newSku;
    }

    // ########################################

    private function linkItem(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $data = array(
            'general_id' => $listingProduct->getData('general_id'),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN(
                $listingProduct->getData('general_id')
            ),
            'sku' => $listingProduct->getData('sku'),
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $listingProduct->addData($data)->save();

        $dataForAdd = array(
            'account_id' => $listingProduct->getListing()->getAccountId(),
            'marketplace_id' => $listingProduct->getListing()->getMarketplaceId(),
            'sku' => $listingProduct->getData('sku'),
            'product_id' => $listingProduct->getProductId(),
            'store_id' => $listingProduct->getListing()->getStoreId()
        );

        Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();

        $message = Mage::helper('M2ePro')->__('The product was found in your Amazon inventory and linked by SKU.');

        $this->addListingsProductsLogsMessage(
            $listingProduct, $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    // ########################################

    private function addSkusToQueue($skus)
    {
        $lockItem = Mage::getModel('M2ePro/LockItem')->load(
            'amazon_list_skus_queue_' . $this->account->getId(),
            'nick'
        );

        $tempSkus = array();

        if ($lockItem->getId()) {
            $tempSkus = json_decode($lockItem->getData('data'),true);
            !is_array($tempSkus) && $tempSkus = array();
        }

        $skus = array_merge($tempSkus,$skus);

        $lockItem->setData('nick','amazon_list_skus_queue_' . $this->account->getId())
                 ->setData('data',json_encode(array_unique($skus)))
                 ->save();
    }

    private function getQueueOfSkus()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem')->load(
            'amazon_list_skus_queue_' . $this->account->getId(),
            'nick'
        );

        if (!$lockItem->getId()) {
            return array();
        }

        return json_decode($lockItem->getData('data'),true);
    }

    // ########################################
}