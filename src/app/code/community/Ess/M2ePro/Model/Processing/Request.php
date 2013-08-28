<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Processing_Request extends Ess_M2ePro_Model_Abstract
{
    const PERFORM_TYPE_SINGLE  = 1;
    const PERFORM_TYPE_PARTIAL = 2;

    const STATUS_NOT_FOUND = 'not_found';
    const STATUS_COMPLETE = 'completed';
    const STATUS_PROCESSING = 'processing';

    const MAX_LIFE_TIME_INTERVAL = 86400; // 1 day

    /** @var Ess_M2ePro_Model_Connector_Server_Responser */
    private $responseObject = NULL;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Processing_Request');
    }

    //####################################

    public function getComponent()
    {
        return $this->getData('component');
    }

    public function getPerformType()
    {
        return (int)$this->getData('perform_type');
    }

    public function getNextPart()
    {
        return (int)$this->getData('next_part');
    }

    //------------------------------------

    public function getHash()
    {
        return $this->getData('hash');
    }

    public function getProcessingHash()
    {
        return $this->getData('processing_hash');
    }

    //------------------------------------

    public function getRequestBody()
    {
        return $this->getData('request_body');
    }

    public function getResponserModel()
    {
        return $this->getData('responser_model');
    }

    public function getResponserParams()
    {
        return $this->getData('responser_params');
    }

    //------------------------------------

    public function isPerformTypeSingle()
    {
        return $this->getPerformType() == self::PERFORM_TYPE_SINGLE;
    }

    public function isPerformTypePartial()
    {
        return $this->getPerformType() == self::PERFORM_TYPE_PARTIAL;
    }

    //####################################

    public function getDecodedRequestBody()
    {
        return @json_decode($this->getRequestBody(),true);
    }

    public function getDecodedResponserParams()
    {
        return @json_decode($this->getResponserParams(),true);
    }

    //####################################

    public function execute(array $data, array $messages = array())
    {
        return $this->getResponserObject()->process($data,$messages);
    }

    public function executeAsCompleted(array $data, array $messages = array())
    {
        $this->getResponserObject()->processCompleted($data,$messages);
    }

    public function executeAsFailed($message = NULL)
    {
        is_null($message) && $message = Mage::helper('M2ePro')->__('Request failed.');
        $this->getResponserObject()->processFailed($message);
    }

    //------------------------------------

    public function getResponserObject()
    {
        if (!is_null($this->responseObject)) {
            return $this->responseObject;
        }

        $modelName = $this->getResponserModel();
        $className = Mage::getConfig()->getModelClassName($modelName);

        return $this->responseObject = new $className($this);
    }

    //####################################
}