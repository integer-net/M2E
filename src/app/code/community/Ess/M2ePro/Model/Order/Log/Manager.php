<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Log_Manager
{
    private $initiator = null;

    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function createLogRecord($componentMode, $orderId, $message, $type)
    {
        $initiator = $this->initiator ? $this->initiator : Ess_M2ePro_Model_Order_Log::INITIATOR_EXTENSION;

        Mage::getModel('M2ePro/Order_Log')->add($componentMode, $orderId, $message, $type, $initiator);
    }
}