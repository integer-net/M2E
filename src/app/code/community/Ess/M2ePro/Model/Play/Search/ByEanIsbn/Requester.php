<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_ByEanIsbn_Requester extends Ess_M2ePro_Model_Connector_Play_Search_ByEanIsbn_Items
{
    // ########################################

    private $listingProduct = NULL;

    // ########################################

    protected function getResponserModel()
    {
        return 'Play_Search_ByEanIsbn_Responser';
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
            $this->listingProduct = Mage::helper('M2ePro/Component_Play')->getObject(
                'Listing_Product',
                $this->params['listing_product_id']
            );
        }

        return $this->listingProduct;
    }

    // ########################################
}