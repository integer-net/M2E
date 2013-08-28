<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_HelperVariations
{
    // ########################################

    public function getRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return array();
        }

        $requestData = array();

        // Get Request Variations Data
        //-----------------------------
        $productVariations = $listingProduct->getVariations(true);
        foreach ($productVariations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $tempItem = array(
                'price' => $variation->getChildObject()->getPrice(),
                'qty' => $variation->isDelete() ? 0 : $variation->getChildObject()->getQty(),
                'sku' => $variation->getChildObject()->getSku(),
                'specifics' => array()
            );

            $tempParamKey = 'return_variation_has_sales_key_when_qty_is_zero';
            if (isset($params[$tempParamKey]) && $params[$tempParamKey] && $tempItem['qty'] <= 0) {
                $temp = $variation->getChildObject()->hasSales();
                $temp && $tempItem['has_sales'] = $temp;
            }

            $productVariationsOptions = $variation->getOptions(true);
            foreach ($productVariationsOptions as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $tempItem['specifics'][$option->getAttribute()] = $option->getOption();
            }

            $requestData[] = $tempItem;
        }
        //-----------------------------

        return $requestData;
    }

    public function getImagesData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return array();
        }

        $tempSpecifics = array();

        if ($listingProduct->getMagentoProduct()->isConfigurableType() &&
            $listingProduct->getDescriptionTemplate()->getChildObject()->isVariationConfigurableImages()) {

            $attributeCode = $listingProduct->getDescriptionTemplate()
                ->getChildObject()->getVariationConfigurableImages();
            $attributeData = $listingProduct->getMagentoProduct()
                ->getProduct()->getResource()->getAttribute($attributeCode)->getData();

            $tempProduct = $listingProduct->getMagentoProduct()->getProduct();
            $configurableAttributes = $tempProduct->getTypeInstance()
                ->setStoreFilter($listingProduct->getListing()->getStoreId())
                ->getConfigurableAttributesAsArray(
                $tempProduct
            );

            foreach ($configurableAttributes as $configurableAttribute) {
                if ((int)$attributeData['attribute_id'] == (int)$configurableAttribute['attribute_id']) {
                    $tempSpecifics = array(
                        $configurableAttribute['label'],
                        $configurableAttribute['frontend_label'],
                        $configurableAttribute['store_label']
                    );
                    break;
                }
            }
        }

        if ($listingProduct->getMagentoProduct()->isGroupedType()) {
            $tempSpecifics = array(Ess_M2ePro_Model_Magento_Product::GROUPED_PRODUCT_ATTRIBUTE_LABEL);
        }

        $requestData = array(
            'specific' => '',
            'images' => array()
        );

        if (count($tempSpecifics) > 0) {

            $productVariations = $listingProduct->getVariations(true);
            foreach ($productVariations as $variation) {

                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

                if ($variation->isDelete()) {
                    continue;
                }

                $productVariationsOptions = $variation->getOptions(true);

                foreach ($productVariationsOptions as $option) {

                    /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                    $findedSpecific = false;
                    foreach ($tempSpecifics as $tempSpecific) {
                        if (strtolower($tempSpecific) == strtolower($option->getAttribute())) {
                            $findedSpecific = $option->getAttribute();
                        }
                    }

                    if ($findedSpecific === false) {
                        continue;
                    }

                    $requestData['specific'] = $findedSpecific;

                    $images = $option->getChildObject()->getImagesForEbay();
                    if (count($images) > 0) {
                        $requestData['images'][$option->getOption()] = array_slice($images,0,1);
                        /*!isset($requestData['images'][$option->getOption()]) &&
                         $requestData['images'][$option->getOption()] = array();
                        $requestData['images'][$option->getOption()] =
                        array_merge($requestData['images'][$option->getOption()],$images);
                        $requestData['images'][$option->getOption()] =
                        array_unique($requestData['images'][$option->getOption()]);
                        $requestData['images'][$option->getOption()] =
                        array_slice($requestData['images'][$option->getOption()],0,12);*/
                    }
                }
            }
        }

        if ($requestData['specific'] == '' || count($requestData['images']) <= 0) {
            return array();
        }

        return $requestData;
    }

    // ########################################

    public function updateAfterAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                      array $nativeRequestData = array(),
                                      array $params = array(),
                                      $saveEbayQtySold = false)
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode() ||
            $listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return;
        }

        // Delete Variations
        //-----------------------------
        $productVariations = $listingProduct->getVariations(true);
        foreach ($productVariations as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation->isDelete() && $variation->deleteInstance();
        }
        //-----------------------------

        // Update Variations
        //-----------------------------
        $productVariations = $listingProduct->getVariations(true);
        foreach ($productVariations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            $dataForUpdate = array(
                'online_price' => $variation->getChildObject()->getPrice(),
                'add' => Ess_M2ePro_Model_Listing_Product_Variation::ADD_NO,
                'delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO,
                'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
            );

            if ($saveEbayQtySold) {
                $dataForUpdate['online_qty_sold'] = is_null($variation->getChildObject()->getOnlineQtySold())
                    ? 0 : $variation->getChildObject()->getOnlineQtySold();
                $dataForUpdate['online_qty'] = $variation->getChildObject()->getQty()+$dataForUpdate['online_qty_sold'];
            } else {
                $dataForUpdate['online_qty_sold'] = 0;
                $dataForUpdate['online_qty'] = $variation->getChildObject()->getQty();
            }

            if ($dataForUpdate['online_qty'] <= $dataForUpdate['online_qty_sold']) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
            }
            if ($dataForUpdate['online_qty'] <= 0) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
            }

            $variation->addData($dataForUpdate)->save();
        }
        //-----------------------------
    }

    // ########################################
}