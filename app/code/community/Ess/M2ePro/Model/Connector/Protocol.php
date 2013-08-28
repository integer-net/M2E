<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Protocol
{
    // ########################################

    protected $requestExtraData = array();

    // ########################################

    /**
     * @abstract
     * @return array
     */
    abstract protected function getRequestData();

    // ########################################
}