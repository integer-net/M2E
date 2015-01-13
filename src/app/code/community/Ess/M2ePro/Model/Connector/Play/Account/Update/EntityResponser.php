<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Account_Update_EntityResponser
    extends Ess_M2ePro_Model_Connector_Play_Responser
{
    // ########################################

    protected function unsetLocks($isFailed = false, $message = NULL)
    {
        $this->getAccount()->deleteObjectLocks(NULL,$this->hash);
        $this->getAccount()->deleteObjectLocks('server_synchronize',$this->hash);
        $this->getAccount()->deleteObjectLocks('adding_to_server',$this->hash);
    }

    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['info'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        /** @var $playAccount Ess_M2ePro_Model_Play_Account */
        $playAccount = $this->getAccount()->getChildObject();

        $dataForUpdate = array(
            'info' => json_encode($response['info'])
        );

        $playAccount->addData($dataForUpdate)->save();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    // ########################################
}