<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Translation_Account_Add_EntityResponser
    extends Ess_M2ePro_Model_Connector_Translation_Responser
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
        if (empty($response['hash']) || !isset($response['info'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        /** @var $ebayAccount Ess_M2ePro_Model_Ebay_Account */
        $ebayAccount = $this->getAccount()->getChildObject();

        $dataForUpdate = array(
            'translation_hash' => $response['hash']
        );

        $ebayAccount->addData($dataForUpdate)->save();
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