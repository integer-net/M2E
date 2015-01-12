<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Delete_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            Mage::getModel('M2ePro/Connector_Amazon_Product_Helper')
                        ->updateAfterDeleteAction($listingProduct,
                                                  $this->getListingProductRequestNativeData($listingProduct),
                                                  $this->params);

            // M2ePro_TRANSLATIONS
            // Item was successfully deleted
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully deleted',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        parent::unsetLocks($fail,$message);

        if (isset($this->params['params']['remove']) && (bool)$this->params['params']['remove']) {
            foreach ($this->listingsProducts as $listingProduct) {
                /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                $listingProduct->deleteInstance();
            }
        }
    }

    protected function inspectProducts()
    {
        if (isset($this->params['params']['remove']) && (bool)$this->params['params']['remove']) {
            return;
        }
        parent::inspectProducts();
    }

    // ########################################
}