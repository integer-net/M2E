<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Product_Relist_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $requestData = $this->getListingProductRequestNativeData($listingProduct);

            $tempParams = array(
                'status_changer' => $this->getStatusChanger()
            );

            Mage::getModel('M2ePro/Amazon_Connector_Product_Helper')
                        ->updateAfterRelistAction($listingProduct,$requestData,$tempParams);

            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully relisted');
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully relisted',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################
}