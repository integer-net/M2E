<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Protocol
{
    const API_VERSION = 1;

    const MESSAGE_TEXT_KEY = 'text';
    const MESSAGE_TYPE_KEY = 'type';
    const MESSAGE_SENDER_KEY = 'sender';
    const MESSAGE_CODE_KEY = 'code';

    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_NOTICE = 'notice';

    // ########################################

    protected $request = array();
    protected $requestExtraData = array();

    protected $response = array();
    protected $messages = array();
    protected $resultType = self::MESSAGE_TYPE_ERROR;

    // ########################################

    protected function sendRequest()
    {
        $requestInfo = $this->getRequestInfo();
        $requestData = $this->getRequestData();

        !is_array($requestData) && $requestData = array();
        $requestData = array_merge($requestData,$this->requestExtraData);

        $this->request = array(
            'api_version' => self::API_VERSION,
            'request' => $requestInfo,
            'data' => $requestData
        );

        $this->request['request'] = @json_encode($this->request['request']);
        $this->request['data'] = @json_encode($this->request['data']);

        $this->response = NULL;

        try {
            $this->response = Mage::helper('M2ePro/Server')
                                    ->sendRequest($this->request,
                                                  $this->getRequestHeaders(),
                                                  $this->getRequestTimeout(),
                                                  false);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Client')->updateMySqlConnection();
            throw $exception;
        }

        Mage::helper('M2ePro/Client')->updateMySqlConnection();

        $this->response = @json_decode($this->response,true);

        if (!isset($this->response['response']) || !isset($this->response['data'])) {
            throw new Exception('Please ensure that CURL library is installed on your server and it supports HTTPS
            protocol. Also ensure that outgoing connection to m2epro.com, port 443 is allowed by firewall.');
        }

        $this->processResponseInfo($this->response['response']);

        return $this->response['data'];
    }

    protected function processResponseInfo($responseInfo)
    {
        $this->resultType = $responseInfo['result']['type'];

        $internalServerErrorMessage = '';

        foreach ($responseInfo['result']['messages'] as $message) {

            $type = $message[self::MESSAGE_TYPE_KEY];
            $sender = $message[self::MESSAGE_SENDER_KEY];

            if ($type == self::MESSAGE_TYPE_ERROR && $sender == 'system') {
                $internalServerErrorMessage != '' && $internalServerErrorMessage .= ', ';
                $internalServerErrorMessage .= $message[self::MESSAGE_TEXT_KEY];
                continue;
            }

            $this->messages[] = $message;
        }

        if ($internalServerErrorMessage != '') {

            throw new Exception(Mage::helper('M2ePro')->__(
                "Internal server error(s) [%error_message%]",
                $internalServerErrorMessage
            ));
        }
    }

    // ########################################

    /**
     * @return array
     * @throws Exception
     */
    protected function getRequestHeaders()
    {
        $commandTemp = $this->getCommand();

        if (!is_array($commandTemp) || !isset($commandTemp[0]) ||
            !isset($commandTemp[1]) || !isset($commandTemp[2])) {
            throw new Exception('Requested command has invalid format.');
        }

        return array(
            'M2EPRO-API-VERSION: '.self::API_VERSION,
            'M2EPRO-API-COMPONENT: '.(string)$this->getComponent(),
            'M2EPRO-API-COMPONENT-VERSION: '.(int)$this->getComponentVersion(),
            'M2EPRO-API-COMMAND: /'.$commandTemp[0] .'/'.$commandTemp[1].'/'.$commandTemp[2].'/'
        );
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return 300;
    }

    //----------------------------------------

    /**
     * @return array
     * @throws Exception
     */
    protected function getRequestInfo()
    {
        $commandTemp = $this->getCommand();

        if (!is_array($commandTemp) || !isset($commandTemp[0]) ||
            !isset($commandTemp[1]) || !isset($commandTemp[2])) {
            throw new Exception('Requested command has invalid format.');
        }

        $request = array(
            'mode' => Mage::helper('M2ePro/Module')->isDevelopmentEnvironment() ? 'development' : 'production',
            'client' => array(
                'platform' => array(
                    'name' => Mage::helper('M2ePro/Magento')->getName().
                                ' ('.Mage::helper('M2ePro/Magento')->getEditionName().')',
                    'version' => Mage::helper('M2ePro/Magento')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Magento')->getRevision(),
                ),
                'module' => array(
                    'name' => Mage::helper('M2ePro/Module')->getName(),
                    'version' => Mage::helper('M2ePro/Module')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Module')->getRevision()
                ),
                'location' => array(
                    'domain' => Mage::helper('M2ePro/Client')->getDomain(),
                    'ip' => Mage::helper('M2ePro/Client')->getIp(),
                    'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
                ),
                'locale' => Mage::helper('M2ePro/Magento')->getLocale()
            ),
            'auth' => array(),
            'component' => array(
                'name' => (string)$this->getComponent(),
                'version' => (int)$this->getComponentVersion()
            ),
            'command' => array(
                'entity' => $commandTemp[0],
                'type' => $commandTemp[1],
                'name' => $commandTemp[2]
            )
        );

        $adminKey = Mage::helper('M2ePro/Server')->getAdminKey();
        !is_null($adminKey) && $adminKey != '' && $request['auth']['admin_key'] = $adminKey;

        $applicationKey = Mage::helper('M2ePro/Server')->getApplicationKey();
        !is_null($applicationKey) && $applicationKey != '' && $request['auth']['application_key'] = $applicationKey;

        $licenseKey = Mage::helper('M2ePro/Module_License')->getKey();
        !is_null($licenseKey) && $licenseKey != '' && $request['auth']['license_key'] = $licenseKey;

        $installationKey = Mage::helper('M2ePro/Module')->getInstallationKey();
        !is_null($installationKey) && $installationKey != '' && $request['auth']['installation_key'] = $installationKey;

        return $request;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function getRequestData();

    /**
     * @return array
     */
    public function getRequestDataPackage()
    {
        return array(
            'info' => $this->getRequestInfo(),
            'data' => $this->getRequestData()
        );
    }

    // ########################################

    /**
     * @abstract
     * @return string
     */
    abstract protected function getComponent();

    /**
     * @abstract
     * @return int
     */
    abstract protected function getComponentVersion();

    //----------------------------------------

    /**
     * @abstract
     * @return array
     */
    abstract protected function getCommand();

    // ########################################

    protected function printDebugData()
    {
        if (!Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return;
        }

        if (count($this->request) > 0) {
            echo '<h1>Request:</h1>',
            '<pre>';
            var_dump($this->request);
            echo '</pre>';
        }

        if (count($this->response) > 0) {
            echo '<h1>Response:</h1>',
            '<pre>';
            var_dump($this->response);
            echo '</pre>';
        }
    }

    // ########################################
}