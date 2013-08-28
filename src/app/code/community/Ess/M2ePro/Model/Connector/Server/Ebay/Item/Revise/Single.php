<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_Revise_Single
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','revise');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->listingProduct->isRevisable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        if (Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater')
                    ->isAddedNewVariationsAttributes($this->listingProduct)) {

            $message = array(
                // ->__('Variation attributes were changed. Please stop and list product.');
                parent::MESSAGE_TEXT_KEY => 'Variation attributes were changed. Please stop and list product.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        return $this->nativeRequestData = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                                                ->getReviseRequestData($this->listingProduct,$this->params);
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType != parent::MESSAGE_TYPE_ERROR) {

            $tempParams = array(
                'start_date_raw' => $response['ebay_start_date_raw'],
                'end_date_raw' => $response['ebay_end_date_raw']
            );

            if ($response['already_stop']) {

                $tempParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
                Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                        ->updateAfterStopAction($this->listingProduct, $this->nativeRequestData,
                                                array_merge($this->params,$tempParams));

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was already stopped on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item was already stopped on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            } else {

                Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                            ->updateAfterReviseAction($this->listingProduct, $this->nativeRequestData,
                                                      array_merge($this->params,$tempParams));

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
                    parent::MESSAGE_TEXT_KEY => $this->getSuccessfullyMessage(),
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            }
        }

        return $response;
    }

    // ########################################

    protected function getSuccessfullyMessage()
    {
        // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
        $defaultMessage = 'Item was successfully revised';

        if (isset($this->params['all_data']) || !isset($this->params['only_data'])) {
            return $defaultMessage;
        }

        $tempOnlyString = '';

        if (isset($this->params['only_data']['variations']) &&
            isset($this->nativeRequestData['is_variation_item']) &&
            $this->nativeRequestData['is_variation_item']) {

            // Parser hack -> Mage::helper('M2ePro')->__('variations');
            $tempStr = 'variations';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['qty']) &&
            (!isset($this->nativeRequestData['is_variation_item']) ||
             !$this->nativeRequestData['is_variation_item'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('qty');
            $tempStr = 'qty';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['price']) &&
            (!isset($this->nativeRequestData['is_variation_item']) ||
             !$this->nativeRequestData['is_variation_item'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('price');
            $tempStr = 'price';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['title'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('title');
            $tempStr = 'title';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['subtitle'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('subtitle');
            $tempStr = 'subtitle';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['description'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('description');
            $tempStr = 'description';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if ($tempOnlyString != '') {
            // Parser hack -> Mage::helper('M2ePro')->__('was successfully revised');
            return $tempOnlyString.' was successfully revised';
        }

        return $defaultMessage;
    }

    // ########################################
}