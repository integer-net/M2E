<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_Policy
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_Policy', 'id');
    }

    // ########################################

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array($this->getIdFieldName());

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        return (count(array_diff_assoc($newData,$oldData)) > 0);
    }

    // ########################################
}