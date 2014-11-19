<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_BySellerSku_Requester extends Ess_M2ePro_Model_Connector_Buy_Search_BySellerSku_Items
{
    // ########################################

    private $listingProduct = NULL;

    // ########################################

    protected function getResponserModel()
    {
        return 'Buy_Search_BySellerSku_Responser';
    }

    protected function makeResponserModel()
    {
        return 'M2ePro/'.$this->getResponserModel();
    }

    protected function getResponserParams()
    {
        return $this->params;
    }

    // ########################################

    protected function setLocks($hash)
    {

    }

    // ########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject(
                'Listing_Product',
                $this->params['listing_product_id']
            );
        }

        return $this->listingProduct;
    }

    // ########################################
}