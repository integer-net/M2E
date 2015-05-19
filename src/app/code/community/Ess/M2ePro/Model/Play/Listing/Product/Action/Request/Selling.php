<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Selling
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        if (!$this->getConfigurator()->isSelling()) {
            return array();
        }

        $data = array();

        // -----------------------
        if (!isset($this->validatorsData['qty'])) {
            $this->validatorsData['qty'] = $this->getPlayListingProduct()->getQty();
        }
        $data['qty'] = $this->validatorsData['qty'];
        // -----------------------

        // -----------------------
        if (!isset($this->validatorsData['dispatch_to'])) {
            $dispatchTo = $this->getPlayListingProduct()->getListingSource()->getDispatchTo();
            $this->validatorsData['dispatch_to'] = $dispatchTo;
        }
        $data['dispatch_to'] = $this->validatorsData['dispatch_to'];
        // -----------------------

        // -----------------------
        if (!isset($this->validatorsData['dispatch_from'])) {
            $dispatchTo = $this->getPlayListing()->getDispatchFrom();
            $this->validatorsData['dispatch_from'] = $dispatchTo;
        }
        $data['dispatch_from'] = $this->validatorsData['dispatch_from'];
        // -----------------------

        // -----------------------
        if (Mage::helper('M2ePro/Component_Play')->isDispatchToAllowedForGbr($data['dispatch_to'])) {
            if (!isset($this->validatorsData['price_gbr'])) {
                $this->validatorsData['price_gbr'] = $this->getPlayListingProduct()->getPriceGbr(true);
            }
            $data['price_gbr'] = $this->validatorsData['price_gbr'];

            if (!isset($this->validatorsData['shipping_price_gbr'])) {
                $this->validatorsData['shipping_price_gbr'] = $this->getPlayListingProduct()
                    ->getListingSource()
                    ->getShippingPriceGbr();
            }
            $data['shipping_price_gbr'] = $this->validatorsData['shipping_price_gbr'];
        }
        // -----------------------

        // -----------------------
        if (Mage::helper('M2ePro/Component_Play')->isDispatchToAllowedForEuro($data['dispatch_to'])) {
            if (!isset($this->validatorsData['price_euro'])) {
                $this->validatorsData['price_euro'] = $this->getPlayListingProduct()->getPriceEuro(true);
            }
            $data['price_euro'] = $this->validatorsData['price_euro'];

            if (!isset($this->validatorsData['shipping_price_euro'])) {
                $this->validatorsData['shipping_price_euro'] = $this->getPlayListingProduct()
                    ->getListingSource()
                    ->getShippingPriceEuro();
            }
            $data['shipping_price_euro'] = $this->validatorsData['shipping_price_euro'];
        }
        // -----------------------

        return $data;
    }

    // ########################################

    public function checkQtyWarnings()
    {
        $qtyMode = $this->getPlayListing()->getPlaySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getPlayListingProduct()->getActualMagentoProduct()->getProductId();
            $storeId = $this->getListing()->getStoreId();

            if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'])) {

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