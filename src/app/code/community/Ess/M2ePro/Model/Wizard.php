<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard extends Ess_M2ePro_Model_Abstract
{
    // ########################################

    protected $steps = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Wizard');
    }

    // ########################################

    public function isActive()
    {
        return true;
    }

    // ########################################

    public function getSteps()
    {
        return $this->steps;
    }

    public function getFirstStep()
    {
        return reset($this->steps);
    }

    // ########################################
}