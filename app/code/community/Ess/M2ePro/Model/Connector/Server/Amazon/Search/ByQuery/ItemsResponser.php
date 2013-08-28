<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Search_ByQuery_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Responser
{
    // ########################################

    protected function unsetLocks($fail = false, $message = NULL) {}

    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['items'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        $products = array();

        foreach ($response['items'] as $item) {

            $product = array(
                'general_id' => $item['product_id'],
                'brand' => isset($item['brand']) ? $item['brand'] : '',
                'title' => $item['title'],
                'image_url' => $item['image_url'],
            );

            if (!empty($item['list_price'])) {
                $product['list_price'] = array(
                    'amount' => $item['list_price']['amount'],
                    'currency' => $item['list_price']['currency'],
                );
            }

            $products[] = $product;
        }

        return $products;
    }

    // ########################################
}