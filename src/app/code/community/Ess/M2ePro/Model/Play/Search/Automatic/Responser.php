<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Automatic_Responser
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
    protected $currentStep = Ess_M2ePro_Model_Play_Search_Automatic_Requester::STEP_GENERAL_ID;

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $listingProduct = NULL;

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

        $this->listingProduct = Mage::helper('M2ePro/Component_Play')
                ->getObject('Listing_Product',$this->params['listing_product_id']);
    }

    // ########################################

    public function processSucceededResponseData($receivedItems)
    {
        if ($this->currentStep == Ess_M2ePro_Model_Play_Search_Automatic_Requester::STEP_MAGENTO_TITLE) {
            $tempReceivedItems = $this->filterReceivedItemsFullTitleMatch($receivedItems);
            count($tempReceivedItems) == 1 && $receivedItems = $tempReceivedItems;
        }

        $childListingProduct = $this->listingProduct->getChildObject();

        if (count($receivedItems) <= 0) {

            $temp = Ess_M2ePro_Model_Play_Search_Automatic_Requester::STEP_MAGENTO_TITLE;
            if ($this->currentStep >= $temp) {

                $childListingProduct->setData('general_id_search_status',
                                              Ess_M2ePro_Model_Play_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE);

                $message = Mage::helper('M2ePro')->__('The Product(s) was not found on Play.com.');
                $childListingProduct->setData('general_id_search_suggest_data',
                                              json_encode(array('message'=>$message)));
                $childListingProduct->save();

                return;
            }

            $params = array(
                'listing_product' => $this->listingProduct,
                'step' => $this->currentStep + 1
            );

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Play_Dispatcher');
            $dispatcherObject->processConnector('search', 'automatic' ,'requester',
                                                $params,
                                                $this->getMarketplace(),
                                                $this->getAccount(),
                                                'Ess_M2ePro_Model_Play');
            return;
        }

        if (count($receivedItems) == 1) {

            if (!isset($receivedItems[0]['variations'])) {

                $childListingProduct->setData('general_id',$receivedItems[0]['general_id']);
                $childListingProduct->setData('general_id_type',Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_GENERAL_ID);
                $childListingProduct->setData('general_id_search_status',
                                      Ess_M2ePro_Model_Play_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC);
                $childListingProduct->setData('general_id_search_suggest_data',NULL);
                $childListingProduct->save();

                return;
            }

            if (count($receivedItems[0]['variations']['play_ids']) == 1) {

                reset($receivedItems[0]['variations']['play_ids']);
                $childListingProduct->setData('general_id',key($receivedItems[0]['variations']['play_ids']));
                $childListingProduct->setData('general_id_type',Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_GENERAL_ID);
                $childListingProduct->setData('general_id_search_status',
                                      Ess_M2ePro_Model_Play_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC);
                $childListingProduct->setData('general_id_search_suggest_data',NULL);
                $childListingProduct->save();

                return;
            }
        }

        $childListingProduct->setData('general_id_search_status',
                                      Ess_M2ePro_Model_Play_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE);
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