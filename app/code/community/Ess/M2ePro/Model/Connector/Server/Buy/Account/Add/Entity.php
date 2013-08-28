<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Account_Add_Entity
    extends Ess_M2ePro_Model_Connector_Server_Buy_Requester
{
    // ########################################

    protected function getCommand()
    {
        return array('account','add','entity');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Buy_Account_Add_EntityResponser';
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
            'web_login' => $this->params['web_login'],
            'web_password' => $this->params['web_password'],
            'ftp_login' => $this->params['ftp_login'],
            'ftp_password' => $this->params['ftp_password'],
            'ftp_inventory_access' => $this->params['ftp_inventory_access'],
            'ftp_orders_access' => $this->params['ftp_orders_access'],
            'ftp_new_sku_access' => $this->params['ftp_new_sku_access']
        );
    }

    // ########################################
}