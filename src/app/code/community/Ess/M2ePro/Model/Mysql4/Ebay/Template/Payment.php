<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_Payment
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_Payment', 'id');
    }

    // ########################################

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'title', 'is_custom_template',
            'create_date', 'update_date'
        );

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        !isset($newData['services']) && $newData['services'] = array();
        !isset($oldData['services']) && $oldData['services'] = array();

        foreach ($newData['services'] as $key => $newService) {
            unset($newData['services'][$key]['id'], $newData['services'][$key]['template_payment_id']);
        }
        foreach ($oldData['services'] as $key => $oldService) {
            unset($oldData['services'][$key]['id'], $oldData['services'][$key]['template_payment_id']);
        }

        ksort($newData);
        ksort($oldData);
        array_walk($newData['services'],'ksort');
        array_walk($oldData['services'],'ksort');

        return md5(json_encode($newData)) !== md5(json_encode($oldData));
    }

    // ########################################
}