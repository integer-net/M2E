<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Search_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Server_Buy_Responser
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

            if (isset($item['variations'])) {
                $product = array(
                    'title' => $item['title'],
                    'image_url' => $item['image_url'],
                    'variations' => $item['variations']
                );

                $products[] = $product;
                continue;
            }

            $product = array(
                'general_id' => $item['product_id'],
                'title' => $item['title'],
                'image_url' => $item['image_url']
            );

            if (!empty($item['price'])) {
                $product['price'] = $item['price'];
            }

            $products[] = $product;
        }

        return $products;
    }

    // ########################################
}