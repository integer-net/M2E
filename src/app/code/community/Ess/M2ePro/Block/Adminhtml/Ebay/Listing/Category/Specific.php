<?php

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    protected $_categoryMode = null;

    protected $_categoryValue = null;

    protected $_attributes = array();

    protected $_divId = null;

    protected $_marketplaceId = null;

    protected $_selectedSpecifics = array();

    protected $_categoryData = array();

    protected $_internalData = array();

    protected $_uniqueId = '';

    protected $_isCompactMode = false;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategorySpecific');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/specific.phtml');

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    protected function _beforeToHtml()
    {
        $uniqueId = $this->getUniqueId();

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => '',
                'onclick' => 'EbayListingCategorySpecificHandler'.$uniqueId.'Obj.removeSpecific(this);',
                'class' => 'scalable delete remove_custom_specific_button'
            ) );
        $this->setChild('remove_custom_specific_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Add Custom Specific'),
                'onclick' => 'EbayListingCategorySpecificHandler'.$uniqueId.'Obj.addRow();',
                'class' => 'add add_custom_specific_button'
            ) );
        $this->setChild('add_custom_specific_button',$buttonBlock);
        //------------------------------
    }

    // ########################################

    public function getDivId()
    {
        if (is_null($this->_divId)) {
            $this->_divId = Mage::helper('core/data')->uniqHash('category_specific_');
        }

        return $this->_divId;
    }

    public function setDivId($divId)
    {
        $this->_divId = $divId;
        return $this;
    }

    public function getMarketplaceId()
    {
        return $this->_marketplaceId;
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->_marketplaceId = $marketplaceId;
        return $this;
    }

    public function getAttributes()
    {
        return Mage::helper('M2ePro/Magento_Attribute')->getAll();
    }

    public function getCategoryMode()
    {
        return $this->_categoryMode;
    }

    public function setCategoryMode($categoryMode)
    {
        $this->_categoryMode = $categoryMode;
        return $this;
    }

    public function getCategoryValue()
    {
        return $this->_categoryValue;
    }

    public function setCategoryValue($categoryValue)
    {
        $this->_categoryValue = $categoryValue;
        return $this;
    }

    public function getCategoryData()
    {
        if ($this->getCategoryMode() == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return array();
        }

        return Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getData(
            $this->getCategoryValue(), $this->getMarketplaceId()
        );
    }

    public function getSelectedSpecifics()
    {
        return $this->_selectedSpecifics;
    }

    public function setSelectedSpecifics(array $specifics)
    {
        foreach ($specifics as $specific) {

            if ($specific['mode'] == Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS) {
                $this->_selectedSpecifics[] = $specific;
                continue;
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = json_decode($specific['value_ebay_recommended'],true);
            }
            unset($specific['value_ebay_recommended']);

            if ($specific['value_mode'] == Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE) {
                $specific['value_data'] = $specific['value_custom_value'];
            }
            unset($specific['value_custom_value']);

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }
            unset($specific['value_custom_attribute']);

            unset($specific['id']);
            unset($specific['template_category_id']);
            unset($specific['update_date']);
            unset($specific['create_date']);

            $this->_selectedSpecifics[] = $specific;
        }

        return $this;
    }

    public function getCustomSpecifics()
    {
        if (count($this->getSelectedSpecifics()) == 0) {
            return array(
                'mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS,
                'mode_relation_id' => 0,
                'specifics' => array()
            );
        }

        $customSpecifics = array();
        foreach ($this->getSelectedSpecifics() as $selectedSpecific) {
            if ($selectedSpecific['mode'] !=
                    Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS) {

                continue;
            }

            $customSpecifics[] = $selectedSpecific;
        }

        return array(
            'mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS,
            'mode_relation_id' => 0,
            'specifics' => $customSpecifics
        );
    }

    public function setInternalData(array $data)
    {
        $this->_internalData = $data;
        return $this;
    }

    public function getInternalData()
    {
        return $this->_internalData;
    }

    public function setUniqueId($id)
    {
        $this->_uniqueId = $id;
        return $this;
    }

    public function getUniqueId()
    {
        return $this->_uniqueId;
    }

    public function setCompactMode($isMode = true)
    {
        $this->_isCompactMode = $isMode;
        return $this;
    }

    public function isCompactMode()
    {
        return $this->_isCompactMode;
    }

    // ########################################
}