<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Buy_Listing_Product extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Buy_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->getData('condition_note') === '') {
            $object->setData('condition_note',new Zend_Db_Expr("''"));
        }
        return $this;
    }

    // ########################################
}