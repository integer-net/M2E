<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_Stop_Single
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','end');
    }

    protected function getListingsLogsCurrentAction()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
        }
        return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->listingProduct->isStoppable()) {

            if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                    parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            } else {
                $this->listingProduct->addData(
                    array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED)
                )->save();
                $this->listingProduct->deleteInstance();
            }

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        return $this->nativeRequestData = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                                                ->getStopRequestData($this->listingProduct,$this->params);
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
                'end_date_raw' => $response['ebay_end_date_raw']
            );

            if ($response['already_stop']) {
                $tempParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
            }

            Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                        ->updateAfterStopAction($this->listingProduct, $this->nativeRequestData,
                                                array_merge($this->params,$tempParams));

            if ($response['already_stop']) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was already stopped on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item was already stopped on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            } else {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
                    parent::MESSAGE_TEXT_KEY => 'Item was successfully stopped',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            }
        }

        if (isset($this->params['remove']) && (bool)$this->params['remove']) {

            $this->listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
            $this->listingProduct->deleteInstance();
        }

        return $response;
    }

    // ########################################
}