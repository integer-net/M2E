<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

interface Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    /**
     * @return string
     */
    public function getPublicNick();

    // ########################################

    /**
     * @return array
     */
    public function getRequestData();

    /**
     * @param array $data
     * @return null
     */
    public function processResponseData(array $data);

    // ########################################
}