<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Amazon_Synchronization_Tasks extends Ess_M2ePro_Model_Synchronization_Tasks
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Synchronization_RunnerActions|
     *      Ess_M2ePro_Model_Amazon_Listing_Other_RunnerActions
     */
    protected $_runnerActions = NULL;

    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $this->_runnerActions = Mage::helper('M2ePro')->getGlobalValue('synchRunnerActions');

        parent::__construct($params);
    }

    //####################################
}