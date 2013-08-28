<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_General_Payment extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Template_General
     */
    private $generalTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_General_Payment');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->generalTemplateModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        if (is_null($this->generalTemplateModel)) {
            $this->generalTemplateModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Template_General', $this->getData('template_general_id'), NULL, array('template')
            );
        }

        return $this->generalTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_General $instance
     */
    public function setGeneralTemplate(Ess_M2ePro_Model_Template_General $instance)
    {
         $this->generalTemplateModel = $instance;
    }

    // ########################################
}