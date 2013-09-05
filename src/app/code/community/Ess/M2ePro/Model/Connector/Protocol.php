<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
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
    abstract protected function getRequestInfo();

    /**
     * @abstract
     * @return array
     */
    abstract protected function getRequestData();

    // ########################################

    public function getRequestDataPackage()
    {
        return array(
            'info' => $this->getRequestInfo(),
            'data' => $this->getRequestData()
        );
    }

    // ########################################
}