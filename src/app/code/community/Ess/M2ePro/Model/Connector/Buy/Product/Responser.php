<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Product_Responser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    protected $listingsProducts = array();

    protected $failedListingsProducts = array();
    protected $succeededListingsProducts = array();

    // ########################################

    public function __construct(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::__construct($processingRequest);

        foreach ($this->params['products'] as $listingProductData) {
            if (!isset($listingProductData['id'])) {
                continue;
            }
            try {
                $this->listingsProducts[] = Mage::helper('M2ePro/Component_Buy')
                                                    ->getObject('Listing_Product',
                                                                (int)$listingProductData['id']);
            } catch (Exception $exception) {}
        }
    }

    protected function unsetLocks($fail = false, $message = NULL)
    {
        $actionIdentifier = $this->getActionIdentifier();

        $tempListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->deleteObjectLocks(NULL,$this->hash);
            $listingProduct->deleteObjectLocks('in_action',$this->hash);
            $listingProduct->deleteObjectLocks($actionIdentifier.'_action',$this->hash);

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->deleteObjectLocks(NULL,$this->hash);
            $listingProduct->getListing()->deleteObjectLocks('products_in_action',$this->hash);
            $listingProduct->getListing()->deleteObjectLocks('products_'.$actionIdentifier.'_action',$this->hash);

            $tempListings[$listingProduct->getListingId()] = true;
        }

        $this->getAccount()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getAccount()->deleteObjectLocks('products_'.$actionIdentifier.'_action',$this->hash);

        $this->getMarketplace()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getMarketplace()->deleteObjectLocks('products_'.$actionIdentifier.'_action',$this->hash);

        if ($fail) {

            $tempListings = array();
            foreach ($this->listingsProducts as $listingProduct) {

                /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                if (isset($tempListings[$listingProduct->getListingId()])) {
                    continue;
                }

                $this->addListingsLogsMessage($listingProduct,$message,
                                              Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                              Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);

                $tempListings[$listingProduct->getListingId()] = true;
            }
        }

        $this->inspectProducts();
    }

    protected function inspectProducts()
    {
        /** @var $inspector Ess_M2ePro_Model_Buy_Synchronization_Templates_Inspector */
        $inspector = Mage::getModel('M2ePro/Buy_Synchronization_Templates_Inspector');
        $inspector->processProducts($this->succeededListingsProducts);
    }

    // ########################################

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                      $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                      $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage($listingProduct,$text,$type,$priority,false);
    }

    protected function addListingsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                              $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                              $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $this->addBaseListingsLogsMessage($listingProduct,$text,$type,$priority,true);
    }

    protected function addBaseListingsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                  $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                  $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM,
                                                  $isListingMode = true)
    {
        $action = $this->getListingsLogsCurrentAction();
        is_null($action) && $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        if ($isListingMode) {
            $logModel->addListingMessage($listingProduct->getListingId() ,
                                         $initiator ,
                                         $this->getLogsActionId() ,
                                         $action , $text, $type , $priority);
        } else {
            $logModel->addProductMessage($listingProduct->getListingId() ,
                                         $listingProduct->getProductId() ,
                                         $listingProduct->getId() ,
                                         $initiator ,
                                         $this->getLogsActionId() ,
                                         $action , $text, $type , $priority);
        }
    }

    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['messages']) || !is_array($response['messages'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        $succeededListingsProducts = array();
        $failedListingsProductsIds = array();

        // Check global messages
        //----------------------
        $globalMessages = $this->messages;
        if (isset($response['messages']['0-id']) && is_array($response['messages']['0-id'])) {
            $globalMessages = array_merge($globalMessages,$response['messages']['0-id']);
        }
        foreach ($this->listingsProducts as $listingProduct) {

            $hasError = false;
            foreach ($globalMessages as $message) {

                $type = $this->getTypeByServerMessage($message);
                $priority = $this->getPriorityByServerMessage($message);
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                !$hasError && $hasError = $this->getHasErrorByServerMessage($message);

                $this->addListingsProductsLogsMessage($listingProduct,$text,$type,$priority);
            }

            if ($hasError && !in_array($listingProduct->getId(),$failedListingsProductsIds)) {
                $this->failedListingsProducts[] = $listingProduct;
                $failedListingsProductsIds[] = $listingProduct->getId();
            }
        }
        //----------------------

        // Check separate messages
        //----------------------
        foreach ($response['messages'] as $listingProductId => $messages) {

            $listingProductId = (int)$listingProductId;

            if ($listingProductId <= 0) {
                continue;
            }

            $findedListingProduct = NULL;
            foreach ($this->listingsProducts as $listingProduct) {
                if ($listingProduct->getId() == $listingProductId) {
                    $findedListingProduct = $listingProduct;
                    break;
                }
            }
            if (is_null($findedListingProduct)) {
                continue;
            }

            $hasError = false;
            foreach ($messages as $message) {

                $type = $this->getTypeByServerMessage($message);
                $priority = $this->getPriorityByServerMessage($message);
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                !$hasError && $hasError = $this->getHasErrorByServerMessage($message);

                $this->addListingsProductsLogsMessage($findedListingProduct,$text,$type,$priority);
            }

            if ($hasError && !in_array($findedListingProduct->getId(),$failedListingsProductsIds)) {
                $this->failedListingsProducts[] = $findedListingProduct;
                $failedListingsProductsIds[] = $findedListingProduct->getId();
            }
        }
        //----------------------

        foreach ($this->listingsProducts as $listingProduct) {
            if (in_array($listingProduct->getId(),$failedListingsProductsIds)) {
                continue;
            }
            $succeededListingsProducts[] = $listingProduct;
        }

        $this->succeededListingsProducts = $succeededListingsProducts;
        $this->processSucceededListingsProducts($succeededListingsProducts);
    }

    //----------------------------------------

    protected abstract function processSucceededListingsProducts(array $listingsProducts = array());

    // ########################################

    protected function getHasErrorByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                    return false;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                   return true;
        }
    }

    protected function getTypeByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
                    return Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                    return Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                    return Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                    return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
        }
    }

    protected function getPriorityByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                    return Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                    return Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                    return Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                break;
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return Mage::helper('M2ePro/Component_Buy')->getMarketplace();
    }

    //---------------------------------------

    protected function getActionIdentifier()
    {
        return $this->params['action_identifier'];
    }

    protected function getStatusChanger()
    {
        return (int)$this->params['status_changer'];
    }

    protected function getLogsActionId()
    {
        return (int)$this->params['logs_action_id'];
    }

    protected function getListingsLogsCurrentAction()
    {
        return $this->params['listing_log_action'];
    }

    //---------------------------------------

    protected function getListingProductRequestNativeData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return (array)$this->params['products'][$listingProduct->getId()]['request']['native_data'];
    }

    protected function getListingProductSendedNativeData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return (array)$this->params['products'][$listingProduct->getId()]['request']['sended_data'];
    }

    // ########################################
}