<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Translation_Account_Add_Entity extends Ess_M2ePro_Model_Connector_Translation_Requester
{
    // ########################################

    protected function getCommand()
    {
        return array('account','add','entity');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Translation_Account_Add_EntityResponser';
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
            'email'       => $this->params['email'],
            'first_name'  => $this->params['first_name'],
            'last_name'   => $this->params['last_name'],
            'company'     => $this->params['company'],
            'additional'  => array(
                'country' => $this->params['country'],
                'ebay'    => array('user_id' => $this->params['user_id'])
            ),
        );
    }

    // ########################################
}