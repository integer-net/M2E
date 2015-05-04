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
        $data = array(
            'ignore_next_inventory_synch' => 1
        );

        if ($this->getConfigurator()->isAllPermitted()) {
            $data['synch_status'] = Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK;
            $data['synch_reasons'] = NULL;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendAfnChannelValues($data);

        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();
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

        if ($this->getRequestData()->hasQty()) {
            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceString .= 'QTY,';
        }

        if ($this->getRequestData()->hasPrice() || $this->getRequestData()->hasSalePrice()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceString .= 'Price,';
        }

        if ($this->getRequestData()->hasProductData() ||
            $this->getRequestData()->hasDescriptionData() ||
            $this->getRequestData()->hasBrowsenodeId()
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
            // Item was successfully revised
            return 'Item was successfully revised';
        }

        // M2ePro_TRANSLATIONS
        // was successfully revised
        return ucfirst(trim($sequenceString,',')).' was successfully revised';
    }

    // ########################################
}