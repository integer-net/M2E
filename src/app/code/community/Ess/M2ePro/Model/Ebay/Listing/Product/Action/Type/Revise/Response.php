<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
        );

        if ($this->getConfigurator()->isAllPermitted()) {
            $data['synch_status'] = Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK;
            $data['synch_reasons'] = NULL;
        }

        $data = $this->appendStatusHiddenValue($data);
        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineBidsValue($data);
        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValues($data);
        $data = $this->appendOnlineInfoDataValues($data);

        $data = $this->appendOutOfStockValues($data);
        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);
        $data = $this->appendGalleryImagesValues($data, $response, $responseParams);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data)->save();

        $this->updateVariationsValues(true);
    }

    public function processAlreadyStopped(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $data = $this->appendStatusChangerValue($data, $responseParams);
        $data = $this->appendStartDateEndDateValues($data, $response);

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $data['additional_data']['ebay_item_fees'] = array();
        $data['additional_data'] = json_encode($data['additional_data']);

        $this->getListingProduct()->addData($data)->save();
    }

    // ########################################

    public function getSuccessfulMessage()
    {
        if ($this->getConfigurator()->isAll() || !$this->getConfigurator()->isOnly()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
            return 'Item was successfully revised';
        }

        $sequenceString = '';

        if ($this->getRequestData()->hasVariationsData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('variations');
            $sequenceString .= 'variations,';
        }

        if ($this->getRequestData()->hasQtyData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('qty');
            $sequenceString .= 'qty,';
        }

        if ($this->getRequestData()->hasPriceData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('price');
            $sequenceString .= 'price,';
        }

        if ($this->getRequestData()->hasTitleData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('title');
            $sequenceString .= 'title,';
        }

        if ($this->getRequestData()->hasSubtitleData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('subtitle');
            $sequenceString .= 'subtitle,';
        }

        if ($this->getRequestData()->hasDescriptionData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('description');
            $sequenceString .= 'description,';
        }

        if ($this->getRequestData()->hasImagesData()) {
            // Parser hack -> Mage::helper('M2ePro')->__('images');
            $sequenceString .= 'images,';
        }

        if (empty($sequenceString)) {
            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
            return 'Item was successfully revised';
        }

        // Parser hack -> Mage::helper('M2ePro')->__('was successfully revised');
        return ucfirst(trim($sequenceString,',')).' was successfully revised';
    }

    // ########################################

    protected function appendOnlineBidsValue($data)
    {
        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
            return parent::appendOnlineBidsValue($data);
        }
        return $data;
    }

    protected function appendOnlineQtyValues($data)
    {
        $data = parent::appendOnlineQtyValues($data);

        $data['online_qty_sold'] = (int)$this->getEbayListingProduct()->getOnlineQtySold();
        isset($data['online_qty']) && $data['online_qty'] += $data['online_qty_sold'];

        return $data;
    }

    protected function appendOnlinePriceValues($data)
    {
        $data = parent::appendOnlinePriceValues($data);

        $params = $this->getParams();

        if (!isset($params['replaced_action']) ||
            $params['replaced_action'] != Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_STOP) {
            return $data;
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getRequestData()->hasVariationsData() ||
            !isset($data['online_buyitnow_price'])) {
            return $data;
        }

        $data['online_buyitnow_price'] = $this->getRequestData()->getVariationPriceData(true);

        return $data;
    }

    // ----------------------------------------

    protected function appendItemFeesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['ebay_item_fees'])) {

            foreach ($response['ebay_item_fees'] as $feeCode => $feeData) {

                if ($feeData['fee'] == 0) {
                    continue;
                }

                if (!isset($data['additional_data']['ebay_item_fees'][$feeCode])) {
                    $data['additional_data']['ebay_item_fees'][$feeCode] = $feeData;
                } else {
                    $data['additional_data']['ebay_item_fees'][$feeCode]['fee'] += $feeData['fee'];
                }
            }
        }

        return $data;
    }

    // ########################################
}