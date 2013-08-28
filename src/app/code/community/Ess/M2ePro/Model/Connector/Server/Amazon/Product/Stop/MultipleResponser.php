<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Product_Stop_MultipleResponser
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
                        ->updateAfterStopAction($listingProduct,$requestData,$tempParams);

            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully stopped',
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