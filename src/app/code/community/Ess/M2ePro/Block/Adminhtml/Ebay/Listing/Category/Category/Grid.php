<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Category_Grid extends Ess_M2ePro_Block_Adminhtml_Category_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect('name');

        $collection->addFieldToFilter(array(
            array('attribute' => 'entity_id', 'in' => $this->getData('categories_ids'))
        ));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ####################################

    protected function _prepareColumns()
    {
        $this->addColumn('magento_category', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Category'),
            'align'     => 'left',
            'width'     => '500px',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnMagentoCategory')
        ));

        $this->addColumn('ebay_categories', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Categories'),
            'align'     => 'left',
            'width'     => '*',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('M2ePro')->__('Primary eBay Category Selected'),
                0 => Mage::helper('M2ePro')->__('Primary eBay Category Not Selected')
            ),
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnEbayCategories'),
            'filter_condition_callback' => array($this, 'callbackFilterEbayCategories')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'center',
            'width'     => '100px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
            'actions'   => array(
                array(
                    'label' => Mage::helper('catalog')->__('Edit Primary Category'),
                    'value'   => 'editPrimaryCategories'
                ),
                array(
                    'label' => Mage::helper('catalog')->__('Edit Categories'),
                    'value'   => 'editCategories'
                ),
            ),
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('editPrimaryCategories', array(
            'label' => Mage::helper('M2ePro')->__('Edit Primary Categories'),
            'url'   => '',
        ));

        $this->getMassactionBlock()->addItem('editCategories', array(
            'label'    => Mage::helper('M2ePro')->__('Edit Categories'),
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    // ####################################

    public function callbackColumnEbayCategories($value, $row, $column, $isExport)
    {
        $sessionKey = 'ebay_listing_category_settings';
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($sessionKey);

        $html = '';

        $html .= $this->renderEbayCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Primary Category'),
            $sessionData['mode_category'][$row->getId()],
            'category_main'
        );

        $html .= $this->renderEbayCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Secondary Category'),
            $sessionData['mode_category'][$row->getId()],
            'category_secondary'
        );
        $html .= $this->renderStoreCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Store Primary Category'),
            $sessionData['mode_category'][$row->getId()],
            'store_category_main'
        );

        $html .= $this->renderStoreCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Store Secondary Category'),
            $sessionData['mode_category'][$row->getId()],
            'store_category_secondary'
        );

        $html .= $this->renderTaxCategoryInfo($sessionData['mode_category'][$row->getId()]);

        if (empty($html)) {

            $helper = Mage::helper('M2ePro');

            $iconSrc = $this->getSkinUrl('M2ePro').'/images/warning.png';
            $html .= <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="font-style: italic; color: gray">{$helper->__('Not Selected')}</span>
HTML;

        }

        return $html;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $actions = $column->getActions();
        $id = (int)$row->getId();

        if (count($actions) == 1) {
            $action = reset($actions);
            $onclick = 'EbayListingCategoryCategoryGridHandlerObj.actions[\''.$action['value'].'Action\']('.$id.');';
            return '<a href="javascript: void(0);" onclick="this.value && ' . $onclick . '">'.$action['label'].'</a>';
        }

        $optionsHtml = '<option></option>';

        foreach ($actions as $option) {
            $optionsHtml .= <<<HTML
            <option value="{$option['value']}">{$option['label']}</option>
HTML;
        }

        return <<<HTML
<div style="padding: 5px;">
    <select
        style="width: 100px;"
        onchange="this.value && EbayListingCategoryCategoryGridHandlerObj.actions[this.value + 'Action']({$id});">
        {$optionsHtml}
    </select>
</div>
HTML;
    }

    // ####################################

    protected function callbackFilterEbayCategories($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $sessionKey = 'ebay_listing_category_settings';
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($sessionKey);

        $primaryCategory = array('selected' => array(), 'blank' => array());

        foreach ($sessionData['mode_category'] as $categoryId => $templateData) {
            if ($templateData['category_main_mode'] != Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
                $primaryCategory['selected'][] = $categoryId;
                continue;
            }

            $primaryCategory['blank'][] = $categoryId;
        }

        if ($value == 0) {
            $collection->addFieldToFilter('entity_id', array('in' => $primaryCategory['blank']));
        } else {
            $collection->addFieldToFilter('entity_id', array('in' => $primaryCategory['selected']));
        }
    }

    // ####################################

    protected function renderEbayCategoryInfo($title, $data, $key)
    {
        $info = '';

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',
                                                                          $this->getRequest()->getParam('listing_id'));

        if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $info = $data[$key.'_path'];
            $info.= '&nbsp;('.$data[$key.'_id'].')';
        } elseif ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $info = Mage::helper('M2ePro')->__(
                'Magento Attribute -> %s',
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'],
                    $listing->getStoreId()
                )
            );
        }

        return $this->renderCategoryInfo($title,$info);
    }

    protected function renderStoreCategoryInfo($title, $data, $key)
    {
        $info = '';

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',
                                                                          $this->getRequest()->getParam('listing_id'));

        if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY) {
            $info = $data[$key.'_path'];
            $info.= '&nbsp;('.$data[$key.'_id'].')';
        } elseif ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_ATTRIBUTE) {
            $info = Mage::helper('M2ePro')->__(
                'Magento Attribute -> %s',
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'],
                    $listing->getStoreId()
                )
            );
        }

        return $this->renderCategoryInfo($title,$info);
    }

    protected function renderTaxCategoryInfo($data)
    {
        $info = '';

        if (!Ess_M2ePro_Model_Ebay_Template_Category::isTaxCategoryShow()) {
            return '';
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',
                                                                          $this->getRequest()->getParam('listing_id'));

        if ($data['tax_category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_VALUE) {
            $info = $data['tax_category_value'];
        } elseif ($data['tax_category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_ATTRIBUTE) {
            $info = Mage::helper('M2ePro')->__(
                'Magento Attribute -> %s',
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data['tax_category_attribute'],
                    $listing->getStoreId()
                )
            );
        }

        return $this->renderCategoryInfo(
            Mage::helper('M2ePro')->__('Tax Category'),
            $info
        );
    }

    protected function renderCategoryInfo($title, $info)
    {
        if (!$info) {
            return '';
        }

        return <<<HTML
<div>
    <span style="text-decoration: underline">{$title}:</span>
    <p style="padding: 2px 0 0 10px;">
        {$info}
    </p>
</div>
HTML;

    }

    // ####################################

    protected function _toHtml()
    {
        //------------------------------
        $urls = array();

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'step' => 3,
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing_categorySettings/getChooserBlockHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing_categorySettings/stepTwoSaveToSession';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $path = 'adminhtml_ebay_listing_categorySettings/stepTwoModeCategoryValidate';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            '_current' => true
        ));

        $urls = json_encode($urls);
        //------------------------------

        //------------------------------
        $translations = array();

        // ->__('Done')
        $text = 'Done';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        // ->__('Cancel')
        $text = 'Cancel';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        // ->__('Set eBay Categories')
        $text = 'Set eBay Categories';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        $translations = json_encode($translations);
        //------------------------------

        $showTaxCategory = json_encode(Mage::getModel('M2ePro/Ebay_Template_Category')->isTaxCategoryShow());

        $commonJs = <<<HTML
<script type="text/javascript">

    M2ePro.translator.add({$translations});

    EbayListingCategoryCategoryGridHandlerObj.afterInitPage();
    EbayListingCategoryCategoryGridHandlerObj.showTaxCategory = {$showTaxCategory};

</script>
HTML;

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    EbayListingCategoryCategoryGridHandlerObj = new EbayListingCategoryCategoryGridHandler('{$this->getId()}');

</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    // ####################################
}