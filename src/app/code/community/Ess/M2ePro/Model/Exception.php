<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Exception extends Exception
{
    protected $additionalData = array();

    // ########################################

    public function __construct($message = "", $additionalData = array(), $code = 0)
    {
        $this->additionalData = $additionalData;
        parent::__construct($message, $code, null);
    }

    // ########################################

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    // ########################################
}