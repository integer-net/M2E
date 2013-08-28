<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Play_Search_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Server_Play_Responser
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
                    'category_code' => $item['category_code'],
                    'variations' => $item['variations']
                );

                if (!empty($item['price_gbr'])) {
                    $product['price_gbr'] = $item['price_gbr'];
                }

                $products[] = $product;
                continue;
            }

            $product = array(
                'general_id' => $item['general_id'],
                'title' => $item['title'],
                'image_url' => $item['image_url'],
                'category_code' => $item['category_code'],
            );

            if (!empty($item['price_gbr'])) {
                $product['price_gbr'] = $item['price_gbr'];
            }

            $products[] = $product;
        }

        return $products;
    }

    // ########################################
}