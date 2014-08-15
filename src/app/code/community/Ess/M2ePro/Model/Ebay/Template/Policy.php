<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Policy extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Policy');
    }

    // ########################################

    public function getApiName()
    {
        return $this->getData('api_name');
    }

    public function getApiIdentifier()
    {
        return $this->getData('api_identifier');
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_policy');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_policy');
        return parent::delete();
    }

    // #######################################
}