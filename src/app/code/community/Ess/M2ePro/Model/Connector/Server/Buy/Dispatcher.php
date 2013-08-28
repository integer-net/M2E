<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Dispatcher
{
    //####################################

    /**
     * @throws Exception
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|Ess_M2ePro_Model_Marketplace $marketplaceModel
     * @param null|Ess_M2ePro_Model_Account $accountModel
     * @param null|string $ormPrefixToConnector
     * @return Ess_M2ePro_Model_Connector_Server_Buy_Requester|Ess_M2ePro_Model_Connector_Server_Buy_Abstract
     */
    public function getConnector($entity, $type, $name,
                                 array $params = array(),
                                 Ess_M2ePro_Model_Marketplace $marketplaceModel = NULL,
                                 Ess_M2ePro_Model_Account $accountModel = NULL,
                                 $ormPrefixToConnector = NULL)
    {
        $className = empty($ormPrefixToConnector) ? 'Ess_M2ePro_Model_Connector_Server_Buy' : $ormPrefixToConnector;

        $entity = uc_words(trim($entity));
        $type = uc_words(trim($type));
        $name = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type != '' && $className .= '_'.$type;

        if ($name != '') {
            if (!empty($ormPrefixToConnector)) {
                $name = 'Server'.$name;
            }
            $className .= '_'.$name;
        }

        $object = new $className($params, $marketplaceModel, $accountModel);

        return $object;
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|string $ormPrefixToConnector
     * @return mixed
     */
    public function processConnector($entity, $type, $name,
                                     array $params = array(),
                                     $marketplace = NULL,
                                     $account = NULL,
                                     $ormPrefixToConnector = NULL)
    {
        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Marketplace',(int)$marketplace);
        }

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Account',(int)$account);
        }

        $object = $this->getConnector($entity, $type, $name, $params, $marketplace, $account, $ormPrefixToConnector);

        return $object->process();
    }

    //####################################

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @return mixed
     */
    public function processVirtualAbstract($entity, $type, $name,
                                           array $requestData = array(),
                                           $responseDataKey = NULL,
                                           $marketplace = NULL,
                                           $account = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;
        return $this->processConnector('virtual','','',$params,$marketplace,$account);
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param string $responserModel
     * @param array $responserParams
     * @param array $requestData
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|string $ormPrefixToConnector
     * @return mixed
     */
    public function processVirtualRequester($entity, $type, $name,
                                            $responserModel = 'Buy_VirtualResponser',
                                            array $responserParams = array(),
                                            array $requestData = array(),
                                            $marketplace = NULL,
                                            $account = NULL,
                                            $ormPrefixToConnector = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__responser_model__'] = $responserModel;
        $params['__responser_params__'] = $responserParams;
        $params['__request_data__'] = $requestData;
        return $this->processConnector('virtualRequester','','',$params,$marketplace,$account,$ormPrefixToConnector);
    }

    //####################################
}