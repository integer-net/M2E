<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Order_Repair extends Ess_M2ePro_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Order_Repair', 'id');
    }
}