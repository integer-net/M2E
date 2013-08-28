<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_OtherItem_Relist_Single
    extends Ess_M2ePro_Model_Connector_Server_Ebay_OtherItem_Abstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','relist');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Other_Log::ACTION_RELIST_PRODUCT;
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->otherListing->isRelistable()) {

            $message = array(
                // ->__('The item either is listed or not available');
                parent::MESSAGE_TEXT_KEY => 'The item either is listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addProductsLogsMessage($this->otherListing,$message,
                                          Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        return $this->nativeRequestData = Mage::getModel('M2ePro/Connector_Server_Ebay_OtherItem_Helper')
                                                ->getRelistRequestData($this->otherListing,$this->params);
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
                'ebay_item_id' => $response['ebay_item_id'],
                'start_date_raw' => $response['ebay_start_date_raw'],
                'end_date_raw' => $response['ebay_end_date_raw']
            );

            if ($response['already_active']) {

                $tempParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
                Mage::getModel('M2ePro/Connector_Server_Ebay_OtherItem_Helper')
                            ->updateAfterRelistAction($this->otherListing, $this->nativeRequestData,
                                                    array_merge($this->params,$tempParams));

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item already was started on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item already was started on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addProductsLogsMessage($this->otherListing, $message,
                                              Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            } else {

                Mage::getModel('M2ePro/Connector_Server_Ebay_OtherItem_Helper')
                            ->updateAfterRelistAction($this->otherListing, $this->nativeRequestData,
                                                      array_merge($this->params,$tempParams));

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully relisted');
                    parent::MESSAGE_TEXT_KEY => 'Item was successfully relisted',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );

                $this->addProductsLogsMessage($this->otherListing, $message,
                                              Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            }
        }

        return $response;
    }

    // ########################################
}