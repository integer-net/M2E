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
            // M2ePro_TRANSLATIONS
            // Item was successfully Revised
            return 'Item was successfully Revised';
        }

        $sequenceString = '';

        if ($this->getRequestData()->hasVariations()) {
            // M2ePro_TRANSLATIONS
            // Variations
            $sequenceString .= 'Variations,';
        }

        if ($this->getRequestData()->hasQty()) {
            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceString .= 'QTY,';
        }

        if ($this->getRequestData()->hasPrice()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceString .= 'Price,';
        }

        if ($this->getRequestData()->hasTitle()) {
            // M2ePro_TRANSLATIONS
            // Title
            $sequenceString .= 'Title,';
        }

        if ($this->getRequestData()->hasSubtitle()) {
            // M2ePro_TRANSLATIONS
            // Subtitle
            $sequenceString .= 'Subtitle,';
        }

        if ($this->getRequestData()->hasDescription()) {
            // M2ePro_TRANSLATIONS
            // Description
            $sequenceString .= 'Description,';
        }

        if ($this->getRequestData()->hasImages()) {
            // M2ePro_TRANSLATIONS
            // Images
            $sequenceString .= 'Images,';
        }

        if (empty($sequenceString)) {
            // M2ePro_TRANSLATIONS
            // Item was successfully Revised
            return 'Item was successfully Revised';
        }

        // M2ePro_TRANSLATIONS
        // was successfully Revised
        return ucfirst(trim($sequenceString,',')).' was successfully Revised';
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
            $params['replaced_action'] != Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
            return $data;
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getRequestData()->hasVariations() ||
            !isset($data['online_buyitnow_price'])) {
            return $data;
        }

        $data['online_buyitnow_price'] = $this->getRequestData()->getVariationPrice(true);

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