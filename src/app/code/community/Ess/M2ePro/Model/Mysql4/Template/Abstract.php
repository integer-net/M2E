<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Mysql4_Template_Abstract extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    // ########################################

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);

        $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate();

        if (is_null($object->getOrigData())) {
            $object->setData('synch_date',$currentTimestamp);
        }

        if ($object->getOrigData('synch_date') != $object->getData('synch_date') &&
            $object->getData('synch_date') == $object->getOrigData('update_date')) {
            $object->setData('synch_date',$object->getData('update_date'));
        }

        return $this;
    }

    // ########################################
}