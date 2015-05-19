<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response
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
            // Item was successfully Relisted
            return 'Item was successfully Relisted';
        }

        // M2ePro_TRANSLATIONS
        // QTY was successfully Relisted
        return 'QTY was successfully Relisted';
    }

    // ########################################
}