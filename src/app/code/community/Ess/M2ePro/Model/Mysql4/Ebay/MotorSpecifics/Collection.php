<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_MotorSpecifics_Collection
    extends Varien_Data_Collection_Db
{
    // ########################################

    public function __construct($idFieldName = NULL)
    {
        $connRead = Mage::getResourceModel('core/config')->getReadConnection();

        parent::__construct($connRead);

        if (!is_null($idFieldName)) {
            $this->_idFieldName = $idFieldName;
        }

        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_specific');

        $this->getSelect()->reset()->from(
            array('main_table' => $table)
        );
    }

    // ########################################
}