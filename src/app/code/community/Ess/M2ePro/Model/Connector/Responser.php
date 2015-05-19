<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Responser
{
    protected $params = array();

    protected $messages = array();
    protected $resultType = Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR;

    protected $parsedResponseData = array();

    // ########################################

    public function __construct(array $params = array())
    {
        $this->params = $params;
    }

    // ########################################

    public function process(array $responseBody = array(), array $messages = array())
    {
        $this->processResponseMessages($messages);

        if (!$this->validateResponseData($responseBody)) {
            throw new Exception('Validation Failed. The Server response data is not valid.');
        }

        $this->parsedResponseData = $this->prepareResponseData($responseBody);
        $this->processResponseData($this->parsedResponseData);
    }

    // ########################################

    public function getParsedResponseData()
    {
        return $this->parsedResponseData;
    }

    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest) {}

    public function eventAfterProcessing() {}

    //-----------------------------------------

    public function eventFailedExecuting($message) {}

    public function eventAfterExecuting() {}

    //-----------------------------------------

    abstract protected function validateResponseData($response);

    protected function prepareResponseData($response)
    {
        return $response;
    }

    abstract protected function processResponseData($response);

    // ########################################

    protected function processResponseMessages(array $messages = array())
    {
        $this->resultType = $this->getResultType($messages);

        $internalServerErrorMessage = '';

        foreach ($messages as $message) {

            $type = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY];
            $sender = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_SENDER_KEY];

            if ($type == Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR && $sender == 'system') {
                $internalServerErrorMessage != '' && $internalServerErrorMessage .= ', ';
                $internalServerErrorMessage .= $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];
                continue;
            }

            $this->messages[] = $message;
        }

        if ($internalServerErrorMessage != '') {
            throw new Exception(Mage::helper('M2ePro')->__(
                "Internal Server error(s) [%error_message%]",
                $internalServerErrorMessage
            ));
        }
    }

    protected function getResultType(array $messages = array())
    {
        $types = array();

        foreach ($messages as $message) {
            $types[] = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY];
        }

        if (in_array(Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR,$types)) {
            return Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR;
        }
        if (in_array(Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING,$types)) {
            return Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING;
        }

        return Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS;
    }

    // ########################################
}