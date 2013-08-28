<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Automatic_ServerRequester extends
                                                          Ess_M2ePro_Model_Connector_Server_Buy_Search_Items
{
    /**
     * @var Ess_M2ePro_Model_Buy_Search_Automatic_Requester
     */
    private $requesterModel = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Search_Automatic_Requester
     */
    public function getRequesterModel()
    {
        if (!is_null($this->requesterModel)) {
            return $this->requesterModel;
        }

        /** @var $tempModel Ess_M2ePro_Model_Buy_Search_Automatic_Requester */
        $this->requesterModel = Mage::getModel('M2ePro/Buy_Search_Automatic_Requester');
        $this->requesterModel->initialize($this->params,$this->marketplace,$this->account);

        return $this->requesterModel;
    }

    // ########################################

    public function process()
    {
        if (!$this->getRequesterModel()->isPossibleToSearch()) {
            return;
        }

        parent::process();
    }

    protected function makeResponserModel()
    {
        return 'M2ePro/Buy_Search_Automatic_ServerResponser';
    }

    // ########################################

    public function getCommand()
    {
        return $this->getRequesterModel()->getCommand();
    }

    protected function getResponserParams()
    {
        return array_merge(parent::getResponserParams(),$this->getRequesterModel()->getResponserParams());
    }

    protected function getQueryString()
    {
        return $this->getRequesterModel()->getQueryString();
    }

    // ########################################
}