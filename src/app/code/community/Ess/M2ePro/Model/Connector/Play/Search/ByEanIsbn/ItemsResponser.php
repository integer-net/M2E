<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Play_Search_ByEanIsbn_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Play_Responser
{
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

                if (!empty($item['category_code'])) {
                    $product['category_code'] = $item['category_code'];
                }

                if (!empty($item['price_gbr'])) {
                    $product['price_gbr'] = $item['price_gbr'];
                }

                $products[] = $product;
                continue;
            }

            $product = array(
                'general_id' => $item['general_id'],
                'title' => $item['title'],
                'image_url' => $item['image_url']
            );

            if (!empty($item['product_url'])) {
                $product['product_url'] = $item['product_url'];
            }

            if (!empty($item['category_code'])) {
                $product['category_code'] = $item['category_code'];
            }

            if (!empty($item['price_gbr'])) {
                $product['price_gbr'] = $item['price_gbr'];
            }

            $products[] = $product;
        }

        $this->processParsedResult($products);
    }

    // ########################################

    abstract protected function processParsedResult($result);

    // ########################################
}