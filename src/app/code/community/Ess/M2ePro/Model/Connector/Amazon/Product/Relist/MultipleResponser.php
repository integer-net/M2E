<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Relist_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            Mage::getModel('M2ePro/Connector_Amazon_Product_Helper')
                        ->updateAfterRelistAction($listingProduct,
                                                  $this->getListingProductRequestNativeData($listingProduct),
                                                  $this->params);

            // M2ePro_TRANSLATIONS
            // Item was successfully relisted
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully relisted',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################
}