<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Manual_ServerRequester extends
                                                        Ess_M2ePro_Model_Connector_Server_Amazon_Search_ByQuery_Items
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Search_Manual_Requester
     */
    private $requesterModel = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Search_Manual_Requester
     */
    public function getRequesterModel()
    {
        if (!is_null($this->requesterModel)) {
            return $this->requesterModel;
        }

        /** @var $tempModel Ess_M2ePro_Model_Amazon_Search_Manual_Requester */
        $this->requesterModel = Mage::getModel('M2ePro/Amazon_Search_Manual_Requester');
        $this->requesterModel->initialize($this->params,$this->marketplace,$this->account);

        return $this->requesterModel;
    }

    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Amazon_Search_Manual_ServerResponser';
    }

    // ########################################

    protected function getResponserParams()
    {
        return array_merge(parent::getResponserParams(),$this->getRequesterModel()->getResponserParams());
    }

    protected function getRequestData()
    {
        return array_merge(parent::getRequestData(),$this->getRequesterModel()->getRequestData());
    }

    // ########################################
}