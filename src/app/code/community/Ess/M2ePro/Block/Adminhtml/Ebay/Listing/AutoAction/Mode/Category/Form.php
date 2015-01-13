<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Category_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingAutoActionModeCategoryForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode/category/form.phtml');
    }

    // ####################################

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

    // ####################################

    public function hasFormData()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        return $listing->getData('auto_mode') == Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY;
    }

    public function getFormData()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        if (empty($groupId)) {
            return array();
        }

        $group = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')->load($groupId);
        $autoCategory = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
            ->getCollection()
                ->addFieldToFilter('listing_id', $this->getRequest()->getParam('listing_id'))
                ->addFieldToFilter('group_id', $groupId)
                ->getFirstItem();

        $data = $autoCategory->getData();
        $data['group_id'] = $group->getId();
        $data['group_title'] = $group->getTitle();

        return $data;
    }

    public function getDefault()
    {
        return array(
            'group_id' => NULL,
            'group_title' => NULL,
            'category_id' => NULL,
            'adding_mode' => Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE,
            'adding_template_category_id' => NULL,
            'deleting_mode' => Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_NONE
        );
    }

    // ####################################

    public function getCategoriesFromOtherGroups()
    {
        $categories = Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category_Group')
            ->getCategoriesFromOtherGroups(
                $this->getRequest()->getParam('listing_id'),
                $this->getRequest()->getParam('group_id')
            );

        foreach ($categories as &$groupTitle) {
            $groupTitle = Mage::helper('M2ePro')->escapeHtml($groupTitle);
        }

        return $categories;
    }

    // ####################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $selectedCategories = array();
        if ($this->getRequest()->getParam('group_id')) {
            $selectedCategories = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
                ->getCollection()
                    ->addFieldToFilter('group_id', $this->getRequest()->getParam('group_id'))
                    ->addFieldToFilter('category_id', array('neq' => 0))
                    ->getColumnValues('category_id');
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Category_Tree $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_category_tree');
        $block->setCallback('EbayListingAutoActionHandlerObj.magentoCategorySelectCallback');
        $block->setSelectedCategories($selectedCategories);
        $this->setChild('category_tree', $block);
        //------------------------------

        //------------------------------
        $this->setChild('confirm', $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm'));
        //------------------------------
    }

    // ####################################
}
