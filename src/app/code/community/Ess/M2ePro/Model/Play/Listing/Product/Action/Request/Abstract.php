<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Abstract
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    protected $validatorsData = array();

    // ########################################

    public function setValidatorsData(array $data)
    {
        $this->validatorsData = $data;
    }

    // ########################################
}