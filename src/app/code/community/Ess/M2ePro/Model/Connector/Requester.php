<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Requester extends Ess_M2ePro_Model_Connector_Protocol
{
    protected $params = array();

    // ########################################

    public function __construct(array $params = array())
    {
        $this->params = $params;
    }

    // ########################################

    public function process()
    {
        $responseData = $this->sendRequest();

        if (!is_array($responseData)) {
            $responseData = array($responseData);
        }

        $isProcessingResponse = isset($responseData['processing_id']);

        if ($isProcessingResponse) {
            $processingId = (string)$responseData['processing_id'];
        } else {
            $processingId = $this->createNewRandomHash();
        }

        /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
        $processingRequest = $this->createProcessingRequest($processingId);
        $this->setLocks($processingRequest->getHash());

        if (!$isProcessingResponse) {

            /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
            $modelName = $processingRequest->getResponserModel();
            $className = Mage::getConfig()->getModelClassName($modelName);

            /** @var $responserObject Ess_M2ePro_Model_Connector_Responser */
            $responserObject = new $className($processingRequest);
            $responserObject->processCompleted((array)$responseData,(array)$this->messages);
        }
    }

    //-----------------------------------------

    abstract protected function setLocks($hash);

    // ########################################

    private function createNewRandomHash()
    {
        $domain = Mage::helper('M2ePro/Client')->getDomain();
        return sha1(rand(1,1000000).microtime(true).$domain);
    }

    private function createProcessingRequest($processingId)
    {
        // Create request
        //------------------
        $dataForAdd = array(
            'hash'             => $this->createNewRandomHash(),
            'processing_hash'  => $processingId,
            'component'        => strtolower($this->getComponent()),
            'perform_type'     => $this->getPerformType(),
            'request_body'     => json_encode($this->request),
            'responser_model'  => $this->makeResponserModel(),
            'responser_params' => json_encode((array)$this->getResponserParams()),
            'expiration_date'  => $this->getProcessingExpirationDate()
        );

        return Mage::getModel('M2ePro/Processing_Request')->setData($dataForAdd)->save();
    }

    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Connector_'.(string)$this->getResponserModel();
    }

    //-----------------------------------------

    protected function getProcessingExpirationDate()
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        return Mage::helper('M2ePro')->getDate($currentTimeStamp + $this->getProcessingExpirationInterval());
    }

    protected function getProcessingExpirationInterval()
    {
        return Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL;
    }

    protected function getPerformType()
    {
        return Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_SINGLE;
    }

    //-----------------------------------------

    /**
     * @abstract
     * @return string
     */
    abstract protected function getResponserModel();

    /**
     * @abstract
     * @return array
     */
    abstract protected function getResponserParams();

    // ########################################
}