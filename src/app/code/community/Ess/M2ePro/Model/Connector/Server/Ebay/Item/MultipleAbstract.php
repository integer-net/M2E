<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Server_Ebay_Item_MultipleAbstract
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract
{
    protected $listingsProducts = array();

    // ########################################

    public function __construct(array $params = array(), array $listingsProducts)
    {
        if (count($listingsProducts) == 0) {
            throw new Exception('Multiple Item Connector has received empty array');
        }

        foreach($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                throw new Exception('Multiple Item Connector has received invalid product data type');
            }
        }

        $tempListing = $listingsProducts[0]->getListing();
        foreach($listingsProducts as $listingProduct) {
            if ($tempListing->getId() != $listingProduct->getListing()->getId()) {
                throw new Exception('Multiple Item Connector has received products from different listings');
            }
        }

        $this->listingsProducts = $listingsProducts;
        parent::__construct($params,$tempListing);
    }

    // ########################################

    public function process()
    {
        $result = parent::process();

        foreach ($this->messages as $message) {
            $priorityMessage = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priorityMessage = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }
            $this->addListingsLogsMessage($message, $priorityMessage);
        }

        if (isset($result['result'])) {

            foreach ($result['result'] as $tempIdProduct=>$tempResultProduct) {

                if (!isset($tempResultProduct['messages'])){
                    continue;
                }

                $listingProductInArray = NULL;
                foreach ($this->listingsProducts as $listingProduct) {
                    if ($tempIdProduct == $listingProduct->getId()) {
                        $listingProductInArray = $listingProduct;
                        break;
                    }
                }

                if (is_null($listingProductInArray)) {
                    continue;
                }

                foreach ($tempResultProduct['messages'] as $message) {
                    $priorityMessage = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
                    if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                        $priorityMessage = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                    }
                    $this->addListingsProductsLogsMessage($listingProductInArray, $message, $priorityMessage);
                }
            }
        }

        return $result;
    }

    // ########################################
}