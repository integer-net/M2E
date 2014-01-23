<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_Category
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_Category', 'id');
    }

    // ########################################

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'title',
            'create_date', 'update_date'
        );

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        !isset($newData['specifics']) && $newData['specifics'] = array();
        !isset($oldData['specifics']) && $oldData['specifics'] = array();

        foreach ($newData['specifics'] as $key => $newSpecific) {
            unset($newData['specifics'][$key]['id'], $newData['specifics'][$key]['template_category_id']);
        }
        foreach ($oldData['specifics'] as $key => $oldSpecific) {
            unset($oldData['specifics'][$key]['id'], $oldData['specifics'][$key]['template_category_id']);
        }

        ksort($newData);
        ksort($oldData);
        array_walk($newData['specifics'],'ksort');
        array_walk($oldData['specifics'],'ksort');

        return md5(json_encode($newData)) !== md5(json_encode($oldData));
    }

    // ########################################
}