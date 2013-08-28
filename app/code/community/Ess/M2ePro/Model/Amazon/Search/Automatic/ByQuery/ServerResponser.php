<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_ServerResponser extends
                                                 Ess_M2ePro_Model_Connector_Server_Amazon_Search_ByQuery_ItemsResponser
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Responser
     */
    private $responserModel = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Responser
     */
    public function getResponserModel()
    {
        if (!is_null($this->responserModel)) {
            return $this->responserModel;
        }

        /** @var $tempModel Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Responser */
        $this->responserModel = Mage::getModel('M2ePro/Amazon_Search_Automatic_ByQuery_Responser');
        $this->responserModel->initialize($this->params,$this->getMarketplace(),$this->getAccount());

        return $this->responserModel;
    }

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        $this->getResponserModel()->unsetLocks($this->hash, $fail, $message);
    }

    protected function processResponseData($response)
    {
        $receivedItems = parent::processResponseData($response);
        $this->getResponserModel()->processSucceededResponseData($receivedItems,$this->hash);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getObjectByParam('Marketplace','marketplace_id');
    }

    // ########################################
}