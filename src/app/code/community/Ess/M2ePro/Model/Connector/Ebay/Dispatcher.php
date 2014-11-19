<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Dispatcher
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
     * @param null|int $mode
     * @param null|string $ormPrefixToConnector
     * @return Ess_M2ePro_Model_Connector_Ebay_Abstract
     */
    public function getConnector($entity, $type, $name,
                                 array $params = array(),
                                 Ess_M2ePro_Model_Marketplace $marketplaceModel = NULL,
                                 Ess_M2ePro_Model_Account $accountModel = NULL,
                                 $mode = NULL,
                                 $ormPrefixToConnector = NULL)
    {
        $className = empty($ormPrefixToConnector) ? 'Ess_M2ePro_Model_Connector_Ebay' : $ormPrefixToConnector;

        $entity = uc_words(trim($entity));
        $type = uc_words(trim($type));
        $name = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type != '' && $className .= '_'.$type;

        if ($name != '') {
            $className .= '_'.$name;
        }

        $object = new $className($params, $marketplaceModel, $accountModel, $mode);

        return $object;
    }

    //####################################

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|int $mode
     * @param null|string $ormPrefixToConnector
     * @return mixed
     */
    public function processConnector($entity, $type, $name,
                                     array $params = array(),
                                     $marketplace = NULL,
                                     $account = NULL,
                                     $mode = NULL,
                                     $ormPrefixToConnector = NULL)
    {
        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace',(int)$marketplace);
        }

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account',(int)$account);
        }

        $object = $this->getConnector(
            $entity , $type, $name, $params, $marketplace, $account, $mode, $ormPrefixToConnector
        );

        return $object->process();
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|int $mode
     * @param array|null $requestInfo
     * @return mixed
     */
    public function processVirtual($entity, $type, $name,
                                   array $requestData = array(),
                                   $responseDataKey = NULL,
                                   $marketplace = NULL,
                                   $account = NULL,
                                   $mode = NULL,
                                   $requestInfo = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_info__'] = $requestInfo;
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;
        return $this->processConnector('virtual','','',$params,$marketplace,$account,$mode);
    }

    //####################################
}