<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Price
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        if (!$this->getConfigurator()->isPrice()) {
            return array();
        }

        if (!isset($this->validatorsData['price'])) {
            $this->validatorsData['price'] = $this->getAmazonListingProduct()->getPrice();
        }

        if (!isset($this->validatorsData['sale_price_info'])) {
            $this->validatorsData['sale_price_info'] = $this->getAmazonListingProduct()->getSalePriceInfo();
        }

        $data = array(
            'price' => $this->validatorsData['price'],
            'sale_price' => $this->validatorsData['sale_price_info']['price']
        );

        if (is_null($data['sale_price'])) {
            unset($data['sale_price']);
        } else if ($data['sale_price'] > 0) {
            $data['sale_price_start_date'] = $this->validatorsData['sale_price_info']['start_date'];
            $data['sale_price_end_date'] = $this->validatorsData['sale_price_info']['end_date'];
        }

        return $data;
    }

    // ########################################
}