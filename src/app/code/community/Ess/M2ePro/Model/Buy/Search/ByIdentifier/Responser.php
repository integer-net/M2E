<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_ByIdentifier_Responser
    extends Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsResponser
{
    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->getObjectByParam('Listing_Product', 'listing_product_id');
    }

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListingProduct()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return Mage::helper('M2ePro/Component_Buy')->getMarketplace();
    }

    // ########################################

    protected function unsetLocks($isFailed = false, $message = NULL)
    {

    }

    // ########################################

    protected function processParsedResult($result)
    {
        Mage::getModel('M2ePro/Buy_Search_'.$this->params['type'])->processResponse(
            $this->getListingProduct(), $result, $this->params
        );
    }

    // ########################################
}