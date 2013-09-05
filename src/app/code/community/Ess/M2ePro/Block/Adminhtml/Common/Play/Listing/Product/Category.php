<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Product_Category extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingProductCategory');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_play_listing';
        //------------------------------

        $this->setTemplate('widget/view/container.phtml');

        // Set header text
        //------------------------------
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
        } else {
            $componentName = '';
        }

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $this->_headerText = Mage::helper('M2ePro')->__(
            'Add Products To%s Listing "%s" From Categories',
            $componentName, $this->escapeHtml($listingData['title']));
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!is_null($this->getRequest()->getParam('back'))) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_common_listing/index',
                array(
                    'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_PLAY
                )
            );
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'PlayListingCategoryHandlerObj.back_click(\'' . $url . '\')',
                'class'     => 'back'
            ));
            //------------------------------
        } else {
            //------------------------------
            $url = $this->getUrl('*/adminhtml_common_play_listing/view', array('id' => $listingData['id']));
            $this->_addButton('view_listing', array(
                'label'     => Mage::helper('M2ePro')->__('View Listing'),
                'onclick'   => 'setLocation(\''.$url.'\')',
                'class'     => 'button_link'
            ));
        }

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'PlayListingCategoryHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_play_listing/categoryProduct',
            array(
                'id' => $listingData['id'],
                'add_products' => 'yes',
                'next' => 'yes'
            )
        );
        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next'),
            'onclick'   => 'PlayListingCategoryHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'next save_and_next_button'
        ));
        //------------------------------

        //$url = $this->getUrl('*/adminhtml_common_play_listing/product',array(
        //    'id' => $listingData['id'],
        //    'back' => Mage::helper('M2ePro')->getBackUrlParam('*/adminhtml_common_listing/index')
        //));
        //$this->_addButton('save_and_list', array(
        //    'label'     => Mage::helper('M2ePro')->__('Save And List'),
        //    'onclick'   => 'PlayListingCategoryHandlerObj.save_and_list_click(\''.$url.'\')',
        //    'class'     => 'save save_and_list_button'
        //));

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_play_listing/add',array('add_products'=>'yes'));
        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'PlayListingCategoryHandlerObj.save_click(\'' . $url .'\')',
            'class'     => 'save save_and_go_to_listings_view_button'
        ));
        //------------------------------
    }

    protected function _toHtml()
    {
        // todo next
        $categoryTreeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_listing_category_tree',
            '',
            array(
                'component'=>Ess_M2ePro_Helper_Component_Play::NICK,
                'tree_settings' => array(
                    'show_products_amount' => true,
                    'hide_products_this_listing' => true
                )
            )
        );
        $categoryBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_product_category_edit');
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_product_category_help');

        return '<div id="add_products_progress_bar"></div>'.
            '<div id="add_products_container">'.
            parent::_toHtml() . $helpBlock->toHtml() . $categoryTreeBlock->toHtml() . $categoryBlock->toHtml().
            '</div>';
    }
}
