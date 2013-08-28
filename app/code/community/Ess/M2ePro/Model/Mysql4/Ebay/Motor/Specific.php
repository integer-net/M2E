<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Motor_Specific extends Ess_M2ePro_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;

    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Motor_Specific', 'epid');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################
}