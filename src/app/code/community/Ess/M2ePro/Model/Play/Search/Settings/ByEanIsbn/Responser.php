<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Settings_ByEanIsbn_Responser
    extends Ess_M2ePro_Model_Connector_Play_Search_ByEanIsbn_ItemsResponser
{
    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->getObjectByParam('Listing_Product', 'listing_product_id');
    }

    // ########################################

    protected function processResponseData($response)
    {
        /** @var Ess_M2ePro_Model_Play_Search_Settings $settingsSearch */
        $settingsSearch = Mage::getModel('M2ePro/Play_Search_Settings');
        $settingsSearch->setListingProduct($this->getListingProduct());
        $settingsSearch->setStep($this->params['step']);
        if (!empty($response)) {
            $settingsSearch->setStepData(array(
                'params' => $this->params,
                'result' => $response,
            ));
        }

        $settingsSearch->process();
    }

    // ########################################
}