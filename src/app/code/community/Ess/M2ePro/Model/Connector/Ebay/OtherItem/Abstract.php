<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Abstract
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    protected $otherListing = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Locker
     */
    protected $locker = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger
     */
    protected $logger = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator
     */
    protected $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
     */
    protected $requestObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
     */
    protected $responseObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected $requestDataObject = NULL;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $defaultParams = array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );
        $params = array_merge($defaultParams, $params);

        $this->otherListing = $otherListing;

        parent::__construct($params,$this->otherListing->getMarketplace(),
                            $this->otherListing->getAccount(),NULL);
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

        foreach ($this->messages as $message) {

            $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }

            $this->getLogger()->logListingOtherMessage($this->otherListing, $message, $priority);
        }

        return $result;
    }

    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    // ----------------------------------------

    protected function processResponseInfo($responseInfo)
    {
        try {
            parent::processResponseInfo($responseInfo);
        } catch (Exception $exception) {

            $message = array(
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR,
                parent::MESSAGE_TEXT_KEY => $exception->getMessage()
            );

           $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);

            throw $exception;
        }
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

    // ########################################

    protected function logRequestMessages()
    {
        foreach ($this->getRequestObject()->getWarningMessages() as $message) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Locker
     */
    protected function getLocker()
    {
        if (is_null($this->locker)) {
            $this->locker = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Locker');
        }

        return $this->locker;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger
     */
    protected function getLogger()
    {
        if (is_null($this->logger)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger $logger */
            $logger = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Logger');

            if (isset($this->params['logs_action_id'])) {
                $logger->setActionId((int)$this->params['logs_action_id']);
            } else {
                $logger->setActionId(
                    Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId()
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

            $this->logger = $logger;
        }

        return $this->logger;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator
     */
    protected function getConfigurator()
    {
        if (is_null($this->configurator)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator $configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
            $configurator->setParams($this->params);

            $this->configurator = $configurator;
        }

        return $this->configurator;
    }

    // ########################################

    private function getOrmActionType()
    {
        switch ($this->getActionType()) {
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

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (is_null($this->requestObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request $request */
            $request = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Type_'.$this->getOrmActionType().'_Request');

            $request->setParams($this->params);
            $request->setListingOther($this->otherListing);
            $request->setConfigurator($this->getConfigurator());

            $this->requestObject = $request;
        }
        return $this->requestObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
     */
    protected function getResponseObject()
    {
        if (is_null($this->responseObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response $response */
            $response = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Type_'.$this->getOrmActionType().'_Response');

            $response->setParams($this->params);
            $response->setListingOther($this->otherListing);
            $response->setConfigurator($this->getConfigurator());
            $response->setRequestData($this->getRequestDataObject());

            $this->responseObject = $response;
        }
        return $this->responseObject;
    }

    // ----------------------------------------

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected function buildRequestDataObject(array $data)
    {
        if (is_null($this->requestDataObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData $requestData */
            $requestData = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_RequestData');

            $requestData->setData($data);
            $requestData->setListingOther($this->otherListing);

            $this->requestDataObject = $requestData;
        }
        return $this->requestDataObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->requestDataObject;
    }

    // ########################################
}