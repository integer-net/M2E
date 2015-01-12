<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Policies
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::POLICIES;
    }

    protected function getNick()
    {
        return NULL;
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        return $this->processTask('Policies_Receive');
    }

    //####################################
}