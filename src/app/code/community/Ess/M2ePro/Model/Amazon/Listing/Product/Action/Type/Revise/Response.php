<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess($params = array())
    {
        $data = array();

        if ($this->getConfigurator()->isAllPermitted()) {
            $data['synch_status'] = Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK;
            $data['synch_reasons'] = NULL;
        }

        if ($this->getConfigurator()->isDetails() || $this->getConfigurator()->isImages()) {
            $data['defected_messages'] = null;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendAfnChannelValues($data);

        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
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

        if ($this->getRequestData()->hasProductData() ||
            $this->getRequestData()->hasDescriptionData() ||
            $this->getRequestData()->hasBrowsenodeId() ||
            $this->getRequestData()->hasCondition()
        ) {
            // M2ePro_TRANSLATIONS
            // details
            $sequenceString .= 'details,';
        }

        if ($this->getRequestData()->hasImagesData()) {
            // M2ePro_TRANSLATIONS
            // images
            $sequenceString .= 'images,';
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
}