<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser_Edit extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    protected $_categoryType = null;

    protected $_selectedCategory = array();

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryChooserEdit');
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

        //------------------------------
        $this->setTemplate('M2ePro/ebay/listing/category/chooser/edit.phtml');
        //------------------------------
    }

    // ########################################

    protected function _toHtml()
    {
        //------------------------------
        $tabsContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_tabs');
        $tabsContainer->setDestElementId('chooser_tabs_container');
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'category_edit_done_button',
            'class'   => '',
            'label'   => Mage::helper('M2ePro')->__('Done'),
            'onclick' => 'EbayListingCategoryChooserHandlerObj.doneCategory();',
        );
        $doneButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        //------------------------------

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');

        $buttonsContainer = <<< HTML

<div id="chooser_buttons_container">
    <a href="javascript:void(0)" onclick="EbayListingCategoryChooserHandlerObj.cancelPopUp()">{$cancelWord}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    {$doneButton->toHtml()}
    <script type="text/javascript">ebayListingCategoryChooserTabsJsTabs.moveTabContentInDest();</script>
</div>

HTML;

        return parent::_toHtml() .
               $tabsContainer->toHtml() .
               '<div id="chooser_tabs_container"></div>' .
               $buttonsContainer;
    }

    // ########################################

    public function getCategoryType()
    {
        if (is_null($this->_categoryType)) {
            throw new LogicException('Category type is not set.');
        }

        return $this->_categoryType;
    }

    public function setCategoryType($categoryType)
    {
        $this->_categoryType = $categoryType;
        return $this;
    }

    public function getCategoryTitle()
    {
        $titles = self::getCategoryTitles();

        return isset($titles[$this->_categoryType]) ? $titles[$this->_categoryType] : '';
    }

    public static function getCategoryTitles()
    {
        $titles = array();

        $type = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN;
        $titles[$type] = Mage::helper('M2ePro')->__('eBay Primary Category');

        $type = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY;
        $titles[$type] = Mage::helper('M2ePro')->__('eBay Secondary Category');

        $type = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN;
        $titles[$type] = Mage::helper('M2ePro')->__('Store Primary Category');

        $type = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY;
        $titles[$type] = Mage::helper('M2ePro')->__('Store Secondary Category');

        return $titles;
    }

    public function getSelectedCategory()
    {
        return $this->_selectedCategory;
    }

    public function setSelectedCategory(array $selectedCategory)
    {
        $this->_selectedCategory = $selectedCategory;
        return $this;
    }

    // ########################################
}
