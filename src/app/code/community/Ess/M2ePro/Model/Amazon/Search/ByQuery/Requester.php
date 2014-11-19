<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_ByQuery_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Search_ByQuery_Items
{
    // ########################################

    private $listingProduct = NULL;

    // ########################################

    protected function getResponserModel()
    {
        return 'Amazon_Search_ByQuery_Responser';
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
        $this->getListingProduct()->addObjectLock(NULL,$hash);
        $this->getListingProduct()->addObjectLock('in_action',$hash);
        $this->getListingProduct()->addObjectLock('search_action',$hash);

        $this->getListingProduct()->getListing()->addObjectLock(NULL,$hash);
        $this->getListingProduct()->getListing()->addObjectLock('products_in_action',$hash);
        $this->getListingProduct()->getListing()->addObjectLock('products_search_action',$hash);

        $this->account->addObjectLock('products_in_action',$hash);
        $this->account->addObjectLock('products_search_action',$hash);

        $this->account->getChildObject()->getMarketplace()->addObjectLock('products_in_action',$hash);
        $this->account->getChildObject()->getMarketplace()->addObjectLock('products_search_action',$hash);

        $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING;
        $this->getListingProduct()->getChildObject()->setData('general_id_search_status',$processingStatus)->save();
    }

    // ########################################

    protected function getQuery()
    {
        return $this->params['item'];
    }

    protected function getOnlyRealTime()
    {
        return !empty($this->params['only_realtime']);
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject(
                'Listing_Product',
                $this->params['listing_product_id']
            );
        }

        return $this->listingProduct;
    }

    // ########################################
}