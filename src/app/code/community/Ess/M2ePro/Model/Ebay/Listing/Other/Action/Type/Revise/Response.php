<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
{
    // ########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
        );

        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValue($data);
        $data = $this->appendTitleValue($data);

        $data = $this->appendStartDateEndDateValues($data, $response);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }

        $this->getListingOther()->addData($data)->save();
    }

    public function processAlreadyStopped(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $data = $this->appendStatusChangerValue($data, $responseParams);
        $data = $this->appendStartDateEndDateValues($data, $response);

        $this->getListingOther()->addData($data)->save();
    }

    // ########################################

    public function getSuccessfulMessage()
    {
        if ($this->getConfigurator()->isAll() || !$this->getConfigurator()->isOnly()) {
            // M2ePro_TRANSLATIONS
            // Item was successfully revised
            return 'Item was successfully revised';
        }

        $sequenceString = '';

        if ($this->getRequestData()->hasQtyData()) {
            // M2ePro_TRANSLATIONS
            // qty
            $sequenceString .= 'qty,';
        }

        if ($this->getRequestData()->hasPriceData()) {
            // M2ePro_TRANSLATIONS
            // price
            $sequenceString .= 'price,';
        }

        if ($this->getRequestData()->hasTitleData()) {
            // M2ePro_TRANSLATIONS
            // title
            $sequenceString .= 'title,';
        }

        if ($this->getRequestData()->hasSubtitleData()) {
            // M2ePro_TRANSLATIONS
            // subtitle
            $sequenceString .= 'subtitle,';
        }

        if ($this->getRequestData()->hasDescriptionData()) {
            // M2ePro_TRANSLATIONS
            // description
            $sequenceString .= 'description,';
        }

        if (empty($sequenceString)) {
            // M2ePro_TRANSLATIONS
            // Item was successfully revised
            return 'Item was successfully revised';
        }
        // M2ePro_TRANSLATIONS
        // was successfully revised
        return ucfirst(trim($sequenceString,',')).' was successfully revised';
    }

    // ########################################

    protected function appendOnlineQtyValues($data)
    {
        $data = parent::appendOnlineQtyValues($data);

        $data['online_qty_sold'] = (int)$this->getEbayListingOther()->getOnlineQtySold();
        isset($data['online_qty']) && $data['online_qty'] += $data['online_qty_sold'];

        return $data;
    }

    // ########################################
}