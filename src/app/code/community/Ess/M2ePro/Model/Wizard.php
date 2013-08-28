<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

abstract class Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array();

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