<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Order extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Order', 'id');
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->getOrigData('magento_order_id') != $object->getData('magento_order_id')
            && !is_null($object->getData('magento_order_id'))
        ) {
            $this->_getWriteAdapter()->update(
                Mage::getResourceModel('M2ePro/Order_Item')->getMainTable(),
                array('state' => Ess_M2ePro_Model_Order_Item::STATE_NORMAL),
                array('order_id = ?' => $object->getId())
            );
        }

        return parent::_afterSave($object);
    }
}