<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Automatic_ServerResponser extends
                                                 Ess_M2ePro_Model_Connector_Server_Buy_Search_ItemsResponser
{
    /**
     * @var Ess_M2ePro_Model_Buy_Search_Automatic_Responser
     */
    private $responserModel = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Search_Automatic_Responser
     */
    public function getResponserModel()
    {
        if (!is_null($this->responserModel)) {
            return $this->responserModel;
        }

        /** @var $tempModel Ess_M2ePro_Model_Buy_Search_Automatic_Responser */
        $this->responserModel = Mage::getModel('M2ePro/Buy_Search_Automatic_Responser');
        $this->responserModel->initialize($this->params,$this->getMarketplace(),$this->getAccount());

        return $this->responserModel;
    }

    // ########################################

    protected function processResponseData($response)
    {
        $receivedItems = parent::processResponseData($response);
        $this->getResponserModel()->processSucceededResponseData($receivedItems);
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