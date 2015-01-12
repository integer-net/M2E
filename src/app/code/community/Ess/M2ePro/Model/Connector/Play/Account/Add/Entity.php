<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Account_Add_Entity
    extends Ess_M2ePro_Model_Connector_Play_Requester
{
    // ########################################

    protected function getCommand()
    {
        return array('account','add','entity');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Play_Account_Add_EntityResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId()
        );
    }

    // ########################################

    protected function setLocks($hash)
    {
        $this->account->addObjectLock(NULL,$hash);
        $this->account->addObjectLock('server_synchronize',$hash);
        $this->account->addObjectLock('adding_to_server',$hash);
    }

    // ########################################

    protected function getRequestData()
    {
        return array(
            'title' => $this->account->getTitle(),
            'login' => $this->params['login'],
            'password' => $this->params['password'],
            'panel_mode' => $this->params['panel_mode']
        );
    }

    // ########################################
}