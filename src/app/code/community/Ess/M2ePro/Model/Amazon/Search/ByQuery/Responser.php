<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_ByQuery_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Search_ByQuery_ItemsResponser
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
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    // ########################################

    protected function unsetLocks($isFailed = false, $message = NULL)
    {
        $this->getListingProduct()->deleteObjectLocks(NULL,$this->hash);
        $this->getListingProduct()->deleteObjectLocks('in_action',$this->hash);
        $this->getListingProduct()->deleteObjectLocks('search_action',$this->hash);

        $this->getListingProduct()->getListing()->deleteObjectLocks(NULL,$this->hash);
        $this->getListingProduct()->getListing()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getListingProduct()->getListing()->deleteObjectLocks('products_search_action',$this->hash);

        $this->getAccount()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getAccount()->deleteObjectLocks('products_search_action',$this->hash);

        $this->getMarketplace()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getMarketplace()->deleteObjectLocks('products_search_action',$this->hash);

        if ($isFailed) {

            $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
            $this->getListingProduct()->getChildObject()->setData('general_id_search_status',$processingStatus)->save();

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

            $logModel->addProductMessage($this->getListingProduct()->getListingId() ,
                                         $this->getListingProduct()->getProductId() ,
                                         $this->getListingProduct()->getId() ,
                                         Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN ,
                                         NULL ,
                                         Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN ,
                                         $message,
                                         Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR ,
                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    // ########################################

    protected function processParsedResult($result)
    {
        $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
        $this->getListingProduct()->getChildObject()->setData('general_id_search_status',$processingStatus)->save();

        Mage::getModel('M2ePro/Amazon_Search_'.$this->params['type'])->processResponse(
            $this->getListingProduct(), $result, $this->params
        );
    }

    // ########################################
}