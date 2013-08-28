<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Config_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configEditForm');
        //------------------------------

        $this->setTemplate('M2ePro/config.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'ConfigHandlerObj.cancelEdit();',
                                'class' => 'cancel_edit'
                            ) );
        $this->setChild('cancel_edit',$buttonBlock);

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Save'),
                                'onclick' => 'ConfigHandlerObj.saveConfig();',
                                'class' => 'save_form'
                            ) );
        $this->setChild('save_form',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}