<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Item_Abstract
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing
     */
    protected $listing = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Locker
     */
    protected $locker = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     */
    protected $logger = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected $configurator = NULL;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing $listing)
    {
        $defaultParams = array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );
        $params = array_merge($defaultParams, $params);

        $this->listing = $listing;

        parent::__construct($params,$this->listing->getMarketplace(),
                            $this->listing->getAccount(),NULL);
    }

    // ########################################

    public function process()
    {
        $this->getLogger()->setStatus(
            Ess_M2ePro_Helper_Data::STATUS_SUCCESS
        );

        if (!$this->isNeedSendRequest()) {
            return array();
        }

        $this->eventBeforeProcess();

        try {
            $result = parent::process();
        } catch (Exception $exception) {
            $this->eventAfterProcess();
            throw $exception;
        }

        $this->eventAfterProcess();

        return $result;
    }

    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    // ----------------------------------------

    protected function eventBeforeProcess()
    {
        $this->getLocker()->update();
    }

    protected function eventAfterProcess()
    {
        $this->getLocker()->remove();
    }

    // ########################################

    abstract protected function getLogAction();

    abstract protected function getActionType();

    // ----------------------------------------

    abstract protected function isNeedSendRequest();

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function isImagesUploadFailed($messages)
    {
        foreach ($messages as $message) {
            if ((int)$message[parent::MESSAGE_CODE_KEY] == 32704531) {
                return true;
            }
        }
        return false;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Locker
     */
    protected function getLocker()
    {
        if (is_null($this->locker)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Locker $locker */

            $locker = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Locker');
            $locker->setListingId($this->listing->getId());

            $this->locker = $locker;
        }

        return $this->locker;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     */
    protected function getLogger()
    {
        if (is_null($this->logger)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger $logger */

            $logger = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Logger');

            if (isset($this->params['logs_action_id'])) {
                $logger->setActionId((int)$this->params['logs_action_id']);
            } else {
                $logger->setActionId(
                    Mage::getModel('M2ePro/Listing_Log')->getNextActionId()
                );
            }

            $logger->setAction($this->getLogAction());

            switch ($this->params['status_changer']) {
                case Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN:
                    $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
                    break;
                case Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER:
                    $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
                    break;
                default:
                    $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
                    break;
            }

            $logger->setInitiator($initiator);
            $logger->setListingId($this->listing->getId());

            $this->logger = $logger;
        }

        return $this->logger;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        if (is_null($this->configurator)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator */

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setParams($this->params);

            $this->configurator = $configurator;
        }

        return $this->configurator;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function makeRequestObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request $request */

        $request = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Request');

        $request->setParams($this->params);
        $request->setListingProduct($listingProduct);
        $request->setConfigurator($this->getConfigurator());

        return $request;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function makeResponseObject(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response $response */

        $response = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Response');

        $response->setParams($this->params);
        $response->setListingProduct($listingProduct);
        $response->setConfigurator($this->getConfigurator());
        $response->setRequestData($requestData);

        return $response;
    }

    // ----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function makeRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct, array $data)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData */

        $requestData = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_RequestData');

        $requestData->setData($data);
        $requestData->setListingProduct($listingProduct);

        return $requestData;
    }

    // ########################################

    private function getOrmActionType()
    {
        switch ($this->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return 'List';
            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                return 'Relist';
            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                return 'Revise';
            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                return 'Stop';
        }

        throw new Exception('Wrong action type');
    }

    // ########################################
}