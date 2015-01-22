<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_List_Multiple
    extends Ess_M2ePro_Model_Connector_Buy_Product_Requester
{
    // ########################################

    private $skusToCheck = array();

    // ########################################

    public function getCommand()
    {
        return array('product','update','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'list';
    }

    protected function getResponserModel()
    {
        return 'Buy_Product_List_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isNotListed()) {

                // M2ePro_TRANSLATIONS
                // Item is already on Rakuten.com, or not available.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'Item is already on Rakuten.com, or not available.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
            $buyListingProduct = $listingProduct->getChildObject();

            if ($buyListingProduct->isVariationProduct() && !$buyListingProduct->isVariationMatched()) {

                // M2ePro_TRANSLATIONS
                // You have to select variation.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'You have to select variation.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            $addingSku = $buyListingProduct->getSku();
            empty($addingSku) && $addingSku = $buyListingProduct->getAddingSku();

            if (!$this->validateSku($addingSku,$listingProduct)) {
                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if ($this->isSkuExistsInM2ePro($addingSku,$listingProduct)) {

                if ($buyListingProduct->getBuyListing()->isGenerateSkuModeNo()) {
                    $this->removeAndUnlockListingProduct($listingProduct);
                    continue;
                }

                $addingSku = $this->generateSku($listingProduct);

                if ($addingSku === false) {

                    // M2ePro_TRANSLATIONS
                    // Can't generate SKU.
                    $this->addListingsProductsLogsMessage(
                        $listingProduct,
                        'Can\'t generate SKU.',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    $this->removeAndUnlockListingProduct($listingProduct);
                    continue;
                }
            }

            $this->skusToCheck[$addingSku] = true;
            $listingProduct->setData('sku',$addingSku);
        }

        $isNeedToCheckSkuExistence = (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/buy/connector/list/', 'check_sku_existence'
        );

        $isNeedToCheckSkuExistence && $this->checkSkuExistence();

        foreach ($this->listingsProducts as $listingProduct) {

            if ($isNeedToCheckSkuExistence) {

                // exception happened
                if (is_null($listingProduct->getData('found_on_buy'))) {
                    $this->removeAndUnlockListingProduct($listingProduct);
                    continue;
                }

                if ($listingProduct->getData('found_on_buy')) {
                    $this->linkItem($listingProduct);
                    $this->removeAndUnlockListingProduct($listingProduct);
                    continue;
                }
            }

            if (!$this->checkGeneralConditions($listingProduct)) {
                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }
        }
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

            $nativeData = Mage::getModel('M2ePro/Connector_Buy_Product_Helper')
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

    private function checkGeneralConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        $addingGeneralId = $buyListingProduct->getGeneralId();

        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER &&
            empty($addingGeneralId)
        ) {

        $message  = 'You can list a product only with assigned Rakuten.com SKU. ';
        $message .= 'Please, use the Search Rakuten.com SKU tool:  ';
        $message .= 'press the icon in Rakuten.com SKU column or choose appropriate command in the Actions dropdown.';
        $message .= ' Assigned Rakuten.com SKU will be displayed in Rakuten.com SKU column.';

            $this->addListingsProductsLogsMessage(
                $listingProduct,
                $message,
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        empty($addingGeneralId) && $addingGeneralId = $buyListingProduct->getAddingGeneralId();

        if (empty($addingGeneralId)) {

            // M2ePro_TRANSLATIONS
            // Identifier is not provided. Please, check Listing settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'Identifier is not provided. Please, check Listing settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingCondition = $buyListingProduct->getCondition();
        empty($addingCondition) && $addingCondition = $buyListingProduct->getAddingCondition();

        $validConditions = $buyListingProduct->getBuyListing()->getConditionValues();

        if (empty($addingCondition) || !in_array($addingCondition,$validConditions)) {
            // M2ePro_TRANSLATIONS
            // Condition is invalid or missed. Please, check Listing Channel and product settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'Condition is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingConditionNote = $buyListingProduct->getConditionNote();
        if (is_null($addingConditionNote)) {
            $addingConditionNote = $buyListingProduct->getAddingConditionNote();
        }

        if (is_null($addingConditionNote)) {
            // M2ePro_TRANSLATIONS
            // Comment is invalid or missed. Please, check Listing Channel and product settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'Comment is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (!empty($addingConditionNote) && strlen($addingConditionNote) > 250) {
            // M2ePro_TRANSLATIONS
            // -The length of condition note must be less than 250 characters.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'The length of condition note must be less than 250 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingShippingExpeditedMode = $buyListingProduct->getShippingExpeditedMode();
        if (is_null($addingShippingExpeditedMode)) {
            $addingShippingExpeditedMode = $buyListingProduct->getAddingShippingExpeditedMode();
        }

        if (is_null($addingShippingExpeditedMode)) {
        // M2ePro_TRANSLATIONS
        // Offer expedited shipping is invalid or missed. Please, check Listing Channel and product settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'Offer expedited shipping is invalid or missed. Please, check Listing Channel and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if ($buyListingProduct->getPrice() <= 0) {
        // M2ePro_TRANSLATIONS
        // The price must be greater than 0. Please, check the Selling Format Template and Product settings.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'The price must be greater than 0. Please, check the Selling Format Template and Product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        return true;
    }

    // ########################################

    private function validateSku($sku, Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (empty($sku)) {

            // -> Mage::helper('M2ePro')->__('Reference ID is not provided. Please, check Listing settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'Reference ID is not provided. Please, check Listing settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (strlen($sku) > Ess_M2ePro_Model_Buy_Listing_Product::SKU_MAX_LENGTH) {

       // M2ePro_TRANSLATIONS
       // The length of reference ID must be less than 30 characters.
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'The length of reference ID must be less than 30 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        return true;
    }

    // ########################################

    private function checkSkuExistence()
    {
        $listingProductsPacks = array_chunk($this->listingsProducts,5,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            foreach ($listingProductsPack as $key => $listingProduct) {
                $skus[$key] = $listingProduct->getData('sku');
            }

            try {

                /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Buy_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
                $response = $dispatcherObject->processVirtual('product','search','skuByReferenceId',
                    array('items' => $skus),'items', $this->account->getId());

            } catch (Exception $exception) {

                Mage::helper('M2ePro/Module_Exception')->process($exception);

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
                if ($value === false || empty($value['general_id']) ) {
                    $listingProductsPack[$key]->setData('found_on_buy',false);
                } else {
                    $listingProductsPack[$key]->setData('found_on_buy',true);
                    $listingProductsPack[$key]->setData('general_id',$value['general_id']);
                }
            }
        }
    }

    // ----------------------------------------

    private function isSkuExistsInM2ePro($sku, Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        // check in 3rd party by account

        $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Listing_Other')
            ->addFieldToFilter('sku',$sku)
            ->addFieldToFilter('account_id',$this->account->getId());

        if ($listingOtherCollection->getSize() > 0) {

            if ($buyListingProduct->getBuyListing()->isGenerateSkuModeNo()) {

                $message = Mage::helper('M2ePro')->__('The same Reference ID was found among 3rd Party Listings. ');
                $message.= Mage::helper('M2ePro')->__('Reference ID must be unique for each Rakuten.com item.');

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

        $listingProductCollection = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Listing_Product');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        $listingProductCollection
            ->getSelect()
            ->join(array('l'=>$listingTable),'`main_table`.`listing_id` = `l`.`id`',array());

        $listingProductCollection
            ->addFieldToFilter('sku',$sku)
            ->addFieldToFilter('account_id',$this->account->getId());

        if ($listingProductCollection->getSize() > 0) {

            if ($buyListingProduct->getBuyListing()->isGenerateSkuModeNo()) {

                $message = Mage::helper('M2ePro')->__('The same Reference ID was found among M2E Listings. ');
                $message.= Mage::helper('M2ePro')->__('Reference ID must be unique for each Rakuten.com item.');

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

            if ($buyListingProduct->getBuyListing()->isGenerateSkuModeNo()) {

                $message = Mage::helper('M2ePro')->__('The same Reference ID is being listed now. ');
                $message.= Mage::helper('M2ePro')->__('Reference ID must be unique for each Rakuten.com item.');

                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    $message,
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
        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        $countTriedTemp = 0;
        do {
            $newSku  = $buyListingProduct->getAddingSku();
            $newSku .= '_' . $listingProduct->getProductId() . '_' . $listingProduct->getId();

            if (strlen($newSku) >= (Ess_M2ePro_Model_Buy_Listing_Product::SKU_MAX_LENGTH - 5)) {
                $newSku = 'SKU_' . $listingProduct->getProductId() . '_' .$listingProduct->getId();
            }

            $newSku = $buyListingProduct->createRandomSku($newSku);

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

        if ($listingProduct->getChildObject()->isVariationsReady()) {

            $variations = $listingProduct->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);
            $options = $variation->getOptions();

            $dataForAdd['variation_options'] = array();
            foreach ($options as $optionData) {
                $dataForAdd['variation_options'][$optionData['attribute']] = $optionData['option'];
            }
            $dataForAdd['variation_options'] = json_encode($dataForAdd['variation_options']);
        }

        Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save();

        $message = Mage::helper('M2ePro')->__(
            'The product was found in your Rakuten.com inventory and linked by Reference ID.'
        );

        $this->addListingsProductsLogsMessage(
            $listingProduct,
            $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    // ########################################

    private function addSkusToQueue($skus)
    {
        $lockItem = Mage::getModel('M2ePro/LockItem')->load(
            'buy_list_skus_queue_' . $this->account->getId(),
            'nick'
        );

        $tempSkus = array();

        if ($lockItem->getId()) {
            $tempSkus = json_decode($lockItem->getData('data'),true);
            !is_array($tempSkus) && $tempSkus = array();
        }

        $skus = array_merge($tempSkus,$skus);

        $lockItem->setData('nick','buy_list_skus_queue_' . $this->account->getId())
                 ->setData('data',json_encode(array_unique($skus)))
                 ->save();
    }

    private function getQueueOfSkus()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem')->load(
            'buy_list_skus_queue_' . $this->account->getId(),
            'nick'
        );

        if (!$lockItem->getId()) {
            return array();
        }

        return json_decode($lockItem->getData('data'),true);
    }

    // ########################################
}