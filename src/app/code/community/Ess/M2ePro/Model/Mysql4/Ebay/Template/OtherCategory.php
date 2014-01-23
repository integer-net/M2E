<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_OtherCategory
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_OtherCategory', 'id');
    }

    // ########################################

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'title', 'create_date', 'update_date'
        );

        $dataConversions = array(
            array('field' => 'store_category_main_id', 'type' => 'float'),
            array('field' => 'store_category_secondary_id', 'type' => 'float'),
        );

        foreach ($dataConversions as $data) {
            $type = $data['type'] . 'val';

            array_key_exists($data['field'],$newData) && $newData[$data['field']] = $type($newData[$data['field']]);
            array_key_exists($data['field'],$oldData) && $oldData[$data['field']] = $type($oldData[$data['field']]);
        }

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        ksort($newData);
        ksort($oldData);

        return md5(json_encode($newData)) !== md5(json_encode($oldData));
    }

    // ########################################
}