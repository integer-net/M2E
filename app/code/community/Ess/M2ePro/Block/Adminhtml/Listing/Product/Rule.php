<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Listing_Product_Rule extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingProductRule');
        //------------------------------

        $this->setTemplate('M2ePro/listing/product/rule.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'rule_form',
            'action'  => '',
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $ruleModel = Mage::helper('M2ePro')->getGlobalValue('rule_model');
        $ruleBlock = $this->getLayout()
                          ->createBlock('M2ePro/adminhtml_magento_product_rule')
                          ->setData(array('rule_model' => $ruleModel));
        $this->setChild('rule_block', $ruleBlock);

        return parent::_beforeToHtml();
    }
}