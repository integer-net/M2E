<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Account_Update_EntityResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
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
        /** @var $amazonAccount Ess_M2ePro_Model_Amazon_Account */
        $amazonAccount = $this->getAccount()->getChildObject();

        $dataForUpdate = array(
            'info' => json_encode($response['info'])
        );

        $amazonAccount->addData($dataForUpdate)->save();
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