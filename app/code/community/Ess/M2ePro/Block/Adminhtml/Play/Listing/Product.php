<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Play_Listing_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_play_listing_product';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
        } else {
            $componentName = '';
        }

        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');
        $this->_headerText = Mage::helper('M2ePro')->__('Add Products To%s Listing "%s" ',
                                                        $componentName,
                                                        $this->escapeHtml($listingData['title']));

        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (is_null($this->getRequest()->getParam('back'))) {
            $backUrl = $this->getUrl('*/adminhtml_play_listing/categoryProduct',array(
                'id' => $listingData['id'],
                'save_categories' => $this->getRequest()->getParam('save_categories',1),
                'selected_categories' => $this->getRequest()->getParam('selected_categories')
            ));
        } else {
            $backUrl = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_listing/index',
                array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
            );
        }

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ProductGridHandlerObj.back_click(\''.$backUrl.'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ProductGridHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        //$tempUrl = $this->getUrl('*/adminhtml_play_listing/product',array(
        //    'id' => $listingData['id'],
        //    'back' => Mage::helper('M2ePro')->getBackUrlParam('*/adminhtml_listing/index')
        //));
        //$this->_addButton('save_and_list', array(
        //    'label'     => Mage::helper('M2ePro')->__('Save And List'),
        //    'onclick'   => 'ProductGridHandlerObj.save_and_list_click(\''.$tempUrl.'\')',
        //    'class'     => 'save'
        //));

        $tempUrl = $this->getUrl('*/adminhtml_play_listing/product',array(
            'id' => $listingData['id'],
            'back' => Mage::helper('M2ePro')->getBackUrlParam('*/adminhtml_listing/index',array(
                    'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
            )
        ));
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ProductGridHandlerObj.save_click(\''.$tempUrl.'\')',
            'class'     => 'save'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_product_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>'.
            '<div id="add_products_container">'.
            parent::_toHtml().
            '</div>';
    }
}
