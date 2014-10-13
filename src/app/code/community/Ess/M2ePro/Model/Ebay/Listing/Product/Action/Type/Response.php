<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $requestData = NULL;

    // ########################################

    abstract public function processSuccess(array $response, array $responseParams = array());

    // ########################################

    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    // ----------------------------------------

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ----------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ----------------------------------------

    public function setRequestData(Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    protected function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    protected function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    protected function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ########################################

    /**
     * @param $itemId
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    protected function createEbayItem($itemId)
    {
        $data = array(
            'account_id' => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'item_id' => (double)$itemId,
            'product_id' => (int)$this->getListingProduct()->getProductId(),
            'store_id' => (int)$this->getListing()->getStoreId()
        );

        /** @var Ess_M2ePro_Model_Ebay_Item $object */
        $object = Mage::getModel('M2ePro/Ebay_Item');
        $object->setData($data)->save();

        return $object;
    }

    protected function updateVariationsValues($saveQtySold)
    {
        if (!$this->getRequestData()->isVariationItem() ||
            !$this->getRequestData()->hasVariationsData()) {
            return;
        }

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            if ($variation->getChildObject()->isDelete()) {
                $variation->deleteInstance();
                continue;
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            $data = array(
                'online_price' => $variation->getChildObject()->getPrice(),
                'add' => 0,
                'delete' => 0,
                'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
            );

            $data['online_qty_sold'] = $saveQtySold ? (int)$variation->getChildObject()->getOnlineQtySold() : 0;
            $data['online_qty'] = $variation->getChildObject()->getQty() + $data['online_qty_sold'];

            if ($data['online_qty'] <= $data['online_qty_sold']) {
                $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
            }
            if ($data['online_qty'] <= 0 &&
                $this->getListingProduct()->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
                $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
            }

            $variation->addData($data)->save();
        }
    }

    // ########################################

    protected function appendStatusHiddenValue($data)
    {
        if (($this->getRequestData()->hasQtyData() && $this->getRequestData()->getQtyData() <= 0) ||
            ($this->getRequestData()->hasVariationsData() && $this->getRequestData()->getVariationQtyData() <= 0)) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
        }
        return $data;
    }

    protected function appendStatusChangerValue($data, $responseParams)
    {
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
        }

        if (isset($responseParams['status_changer'])) {
            $data['status_changer'] = (int)$responseParams['status_changer'];
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendOnlineBidsValue($data)
    {
        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
            $data['online_bids'] = NULL;
        } else {
            $data['online_bids'] = 0;
        }

        return $data;
    }

    protected function appendOnlineQtyValues($data)
    {
        $data['online_qty_sold'] = 0;

        if ($this->getRequestData()->hasVariationsData()) {
            $data['online_qty'] = $this->getRequestData()->getVariationQtyData();
        } else if ($this->getRequestData()->hasQtyData()) {
            $data['online_qty'] = $this->getRequestData()->getQtyData();
        }

        return $data;
    }

    protected function appendOnlinePriceValues($data)
    {
        if ($this->getEbayListingProduct()->isListingTypeFixed()) {

            $data['online_start_price'] = NULL;
            $data['online_reserve_price'] = NULL;

            if ($this->getRequestData()->hasVariationsData()) {
                $data['online_buyitnow_price'] = $this->getRequestData()->getVariationPriceData(false);
            } else if ($this->getRequestData()->hasPriceFixedData()) {
                $data['online_buyitnow_price'] = $this->getRequestData()->getPriceFixedData();
            }

        } else {

            if ($this->getRequestData()->hasPriceStartData()) {
                $data['online_start_price'] = $this->getRequestData()->getPriceStartData();
            }
            if ($this->getRequestData()->hasPriceReserveData()) {
                $data['online_reserve_price'] = $this->getRequestData()->getPriceReserveData();
            }
            if ($this->getRequestData()->hasPriceBuyItNowData()) {
                $data['online_buyitnow_price'] = $this->getRequestData()->getPriceBuyItNowData();
            }
        }

        return $data;
    }

    protected function appendOnlineInfoDataValues($data)
    {
        if ($this->getRequestData()->hasSkuData()) {
            $data['online_sku'] = $this->getRequestData()->getSkuData();
        }

        if ($this->getRequestData()->hasTitleData()) {
            $data['online_title'] = $this->getRequestData()->getTitleData();
        }

        if ($this->getRequestData()->hasPrimaryCategoryData()) {

            $data['online_category'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                $this->getRequestData()->getPrimaryCategoryData(),
                $this->getMarketplace()->getId()
            ).' ('.$this->getRequestData()->getPrimaryCategoryData().')';
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendOutOfStockValues($data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if ($this->getRequestData()->hasOutOfStockControlData()) {
            $data['additional_data']['out_of_stock_control'] = $this->getRequestData()->getOutOfStockControlData();
        }

        return $data;
    }

    protected function appendItemFeesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['ebay_item_fees'])) {
            $data['additional_data']['ebay_item_fees'] = $response['ebay_item_fees'];
        }

        return $data;
    }

    protected function appendStartDateEndDateValues($data, $response)
    {
        if (isset($response['ebay_start_date_raw'])) {
            $data['start_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
                $response['ebay_start_date_raw']
            );
        }

        if (isset($response['ebay_end_date_raw'])) {
            $data['end_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
                $response['ebay_end_date_raw']
            );
        }

        return $data;
    }

    protected function appendGalleryImagesValues($data, $response, $responseParams)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['is_eps_ebay_images_mode'])) {
            $data['additional_data']['is_eps_ebay_images_mode'] = $response['is_eps_ebay_images_mode'];
        }

        if (!isset($responseParams['is_images_upload_error']) || !$responseParams['is_images_upload_error']) {

            if ($this->getRequestData()->hasImagesData()) {
                $imagesData = $this->getRequestData()->getImagesData();

                if (isset($imagesData['images'])) {
                    $data['additional_data']['ebay_product_images_hash'] =
                        Mage::helper('M2ePro/Component_Ebay')->getImagesHash($imagesData['images']);
                }
            }

            if ($this->getRequestData()->hasVariationsImagesData()) {
                $imagesData = $this->getRequestData()->getVariationsImagesData();
                $data['additional_data']['ebay_product_variation_images_hash'] =
                    Mage::helper('M2ePro/Component_Ebay')->getImagesHash($imagesData);
            }
        }

        return $data;
    }

    // ########################################
}