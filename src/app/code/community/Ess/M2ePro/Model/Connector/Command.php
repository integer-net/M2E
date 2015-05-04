<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Command extends Ess_M2ePro_Model_Connector_Protocol
{
    protected $params = array();
    private $parsedResponseData = array();

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

        if (!$this->validateResponseData($responseData)) {
            throw new Exception('Validation Failed. The Server response data is not valid.');
        }

        $parsedResponseData = $this->prepareResponseData($responseData);

        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            $this->parsedResponseData = $parsedResponseData;
        }

        return $parsedResponseData;
    }

    //----------------------------------------

    abstract protected function validateResponseData($response);

    abstract protected function prepareResponseData($response);

    // ########################################

    public function printDebugData()
    {
        if (!Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return;
        }

        parent::printDebugData();

        if (count($this->parsedResponseData) > 0) {
            echo '<h1>Parsed Response:</h1>',
                 '<pre>';
            var_dump($this->parsedResponseData);
            echo '</pre>';
        }
    }

    // ########################################
}