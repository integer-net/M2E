<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        if (!$this->getConfigurator()->isQty()) {
            return array();
        }

        if (!isset($this->validatorsData['qty'])) {
            $this->validatorsData['qty'] = $this->getAmazonListingProduct()->getQty();
        }

        $data = array(
            'qty' => $this->validatorsData['qty'],
        );

        $this->checkQtyWarnings();

        if (!isset($this->validatorsData['handling_time'])) {
            $handlingTime = $this->getAmazonListingProduct()->getListingSource()->getHandlingTime();
            $this->validatorsData['handling_time'] = $handlingTime;
        }

        if (!isset($this->validatorsData['restock_date'])) {
            $restockDate = $this->getAmazonListingProduct()->getListingSource()->getRestockDate();
            $this->validatorsData['restock_date'] = $restockDate;
        }

        if (!empty($this->validatorsData['handling_time'])) {
            $data['handling_time'] = $this->validatorsData['handling_time'];
        }

        if (!empty($this->validatorsData['restock_date'])) {
            $data['restock_date'] = $this->validatorsData['restock_date'];
        }

        return $data;
    }

    // ########################################

    public function checkQtyWarnings()
    {
        $qtyMode = $this->getAmazonListing()->getAmazonSellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getAmazonListingProduct()->getActualMagentoProduct()->getProductId();
            $storeId = $this->getListing()->getStoreId();

            if(!empty(Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'])) {

                $qtys = Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings($type);
                }
            }
        }
    }

    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            // M2ePro_TRANSLATIONS
            // During the Quantity Calculation the Settings in the "Manage Stock No" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Manage Stock No" '.
                'field were takken into consideration.');
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            // M2ePro_TRANSLATIONS
            // During the Quantity Calculation the Settings in the "Backorders" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Backorders" '.
                'field were taken into consideration.');
        }
    }

    // ########################################
}