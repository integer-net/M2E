<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Account_Delete_Entity
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Requester
{
    // ########################################

    protected function getCommand()
    {
        return array('account','delete','entity');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Amazon_Account_Delete_EntityResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
            'marketplace_id' => $this->marketplace->getId()
        );
    }

    // ########################################

    protected function setLocks($hash)
    {
        $this->account->addObjectLock(NULL,$hash);
        $this->account->addObjectLock('server_synchronize',$hash);
        $this->account->addObjectLock('deleting_from_server',$hash);
    }

    // ########################################

    protected function getRequestData()
    {
        return array();
    }

    // ########################################
}