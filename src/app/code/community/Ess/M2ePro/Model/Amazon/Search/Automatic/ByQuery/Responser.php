<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Responser
{
    protected $params = array();

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    // ########################################

    protected $currentQuery = '';
    protected $currentStep = Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester::STEP_UPC_EAN;

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $listingProduct = NULL;
    protected $unsetProcessingLock = true;

    // ########################################

    public function initialize(array $params = array(),
                               Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->params = $params;
        $this->marketplace = $marketplace;
        $this->account = $account;

        $this->currentStep = (int)$this->params['step'];
        $this->currentQuery = (string)$this->params['query'];

        $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product',$this->params['listing_product_id']);
    }

    // ########################################

    public function unsetLocks($hash, $fail = false, $message = NULL)
    {
        if (!$this->unsetProcessingLock) {
            return;
        }

        $this->listingProduct->deleteObjectLocks(NULL,$hash);
        $this->listingProduct->deleteObjectLocks('in_action',$hash);
        $this->listingProduct->deleteObjectLocks('search_action',$hash);

        $this->listingProduct->getListing()->deleteObjectLocks(NULL,$hash);
        $this->listingProduct->getListing()->deleteObjectLocks('products_in_action',$hash);
        $this->listingProduct->getListing()->deleteObjectLocks('products_search_action',$hash);

        $this->getAccount()->deleteObjectLocks('products_in_action',$hash);
        $this->getAccount()->deleteObjectLocks('products_search_action',$hash);

        $this->getMarketplace()->deleteObjectLocks('products_in_action',$hash);
        $this->getMarketplace()->deleteObjectLocks('products_search_action',$hash);

        $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
        $this->listingProduct->getChildObject()->setData('general_id_search_status',$processingStatus)->save();

        if ($fail) {

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

            $logModel->addListingMessage($this->listingProduct->getListingId() ,
                                         Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN ,
                                         NULL ,
                                         Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN ,
                                         $message,
                                         Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR ,
                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    public function processSucceededResponseData($receivedItems, $hash)
    {
        $this->unsetProcessingLock = true;
        $this->unsetLocks($hash);
        $this->unsetProcessingLock = false;

        if ($this->currentStep == Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester::STEP_MAGENTO_TITLE) {
            $tempReceivedItems = $this->filterReceivedItemsFullTitleMatch($receivedItems);
            count($tempReceivedItems) == 1 && $receivedItems = $tempReceivedItems;
        }

        $childListingProduct = $this->listingProduct->getChildObject();

        if (count($receivedItems) <= 0) {

            $temp = Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester::STEP_MAGENTO_TITLE;
            if ($this->currentStep >= $temp) {

                $childListingProduct->setData('general_id_search_status',
                                              Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE);

                $message = Mage::helper('M2ePro')->__('The Product(s) was not found on Amazon.');
                $childListingProduct->setData('general_id_search_suggest_data',
                                              json_encode(array('message'=>$message)));
                $childListingProduct->save();

                return;
            }

            $params = array(
                'listing_product' => $this->listingProduct,
                'step' => $this->currentStep + 1
            );

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher');
            $dispatcherObject->processConnector('automatic', 'byQuery' ,'requester',
                                                $params,
                                                $this->getMarketplace(),
                                                $this->getAccount(),
                                                'Ess_M2ePro_Model_Amazon_Search');
            return;
        }

        if (count($receivedItems) == 1) {

            $childListingProduct->setData('general_id',$receivedItems[0]['general_id']);
            $childListingProduct->setData('is_isbn_general_id',
                                          (int)Mage::helper('M2ePro/Component_Amazon_Validation')
                                                                            ->isISBN($receivedItems[0]['general_id']));
            $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;
            $childListingProduct->setData('general_id_search_status',
                                          $temp);
            $childListingProduct->setData('general_id_search_suggest_data',NULL);
            $childListingProduct->save();

            return;
        }

        $childListingProduct->setData('general_id_search_status',
                                      Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE);
        $childListingProduct->setData('general_id_search_suggest_data',json_encode($receivedItems));
        $childListingProduct->save();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->marketplace;
    }

    // ########################################

    protected function filterReceivedItemsFullTitleMatch($receivedItems)
    {
        $return = array();

        $magentoProductTitle = $this->listingProduct->getChildObject()->getActualMagentoProduct()->getName();
        $magentoProductTitle = trim(strtolower($magentoProductTitle));

        foreach ($receivedItems as $item) {
            $itemTitle = trim(strtolower($item['title']));
            if ($itemTitle == $magentoProductTitle) {
                $return[] = $item;
            }
        }

        return $return;
    }

    // ########################################
}