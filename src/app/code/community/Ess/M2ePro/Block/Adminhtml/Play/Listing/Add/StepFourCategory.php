<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Play_Listing_Add_StepFourCategory extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('PlayListingAddStepFourCategory');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_play_listing';
        //------------------------------

        $this->setTemplate('widget/view/container.phtml');

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
        } else {
            $componentName = '';
        }

        $this->_headerText = Mage::helper('M2ePro')->__("Add%s Listing [Select Categories]", $componentName);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'PlayListingCategoryHandlerObj.back_click(\''
                .$this->getUrl('*/adminhtml_play_listing/add',
                    array('step'=>'3'))
                .'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'PlayListingCategoryHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next'),
            'onclick'   => 'PlayListingCategoryHandlerObj.save_click(\''
                .$this->getUrl('*/adminhtml_play_listing/add',
                    array('step'=>'4',
                        'remember_categories'=>'yes'))
                .'\')',
            'class'     => 'next save_and_next_button'
        ));

        $this->_addButton('save_and_go_to_listings_list', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'PlayListingCategoryHandlerObj.save_click(\''
                .$this->getUrl('*/adminhtml_play_listing/add',
                    array('step'=>'4',
                        'save'=>'yes',
                        'back'=>'list'))
                .'\')',
            'class'     => 'save save_and_go_to_listings_list_button'
        ));

        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Save And View Listing'),
            'onclick'   => 'PlayListingCategoryHandlerObj.save_click(\''
                .$this->getUrl('*/adminhtml_play_listing/add',
                    array('step'=>'4',
                        'save'=>'yes',
                        'back'=>'view'))
                .'\')',
            'class'     => 'save save_and_go_to_listing_view_button'
        ));
        //------------------------------
    }

    protected function _toHtml()
    {
        $treeSettings = array(
            'show_products_amount' => true,
            'hide_products_this_listing' => false
        );
        $categoryTreeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_category_tree',
            '',
            array('component'=>Ess_M2ePro_Helper_Component_Play::NICK,
                'tree_settings' => $treeSettings));
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_category_help');
        $categoryBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_category');

        return '<div id="add_products_progress_bar"></div>'.
            '<div id="add_products_container">'.
            parent::_toHtml() . $helpBlock->toHtml() . $categoryTreeBlock->toHtml() . $categoryBlock->toHtml().
            '</div>';
    }
}
