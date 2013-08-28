<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_ServerRequester extends
                                                          Ess_M2ePro_Model_Connector_Server_Amazon_Search_ByQuery_Items
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester
     */
    private $requesterModel = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester
     */
    public function getRequesterModel()
    {
        if (!is_null($this->requesterModel)) {
            return $this->requesterModel;
        }

        /** @var $tempModel Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester */
        $this->requesterModel = Mage::getModel('M2ePro/Amazon_Search_Automatic_ByQuery_Requester');
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
        return 'M2ePro/Amazon_Search_Automatic_ByQuery_ServerResponser';
    }

    // ########################################

    protected function setLocks($hash)
    {
        $this->getRequesterModel()->setLocks($hash);
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