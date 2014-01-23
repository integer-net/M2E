<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings_Grid_Abstract
{
    /** @var Mage_Eav_Model_Entity_Attribute_Abstract */
    private $motorsSpecificsAttribute = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewSettingsGrid'.$this->getListing()->getId());
        //------------------------------

        if ($this->isMotorsSpecificsAvailable()) {
            $attributeCode = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/ebay/motor/', 'motors_specifics_attribute'
            );
            $this->motorsSpecificsAttribute = Mage::getModel('catalog/product')->getResource()
                                                                               ->getAttribute($attributeCode);
        }
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    // ####################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    // ####################################

    protected function getGridHandlerJs()
    {
        return 'EbayListingSettingsGridHandler';
    }

    // ####################################

    protected function getListingProductCollection()
    {
        $collection = $this->getData('listing_product_collection');

        if (is_null($collection)) {

            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

            $select = clone $this->getCollection()->getSelect();
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->columns('lp.id');

            $listingProductIds = $connRead->fetchCol($select);

            /* @var $collection Ess_M2ePro_Model_Mysql4_Listing_Product_Collection */
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
                ->addFieldToFilter('id', array('in' => $listingProductIds));

            $this->setData('listing_product_collection',$collection);
        }

        return $collection;
    }

    // ####################################

    protected function _prepareCollection()
    {
        //--------------------------------
        // Get collection
        //----------------------------
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        //--------------------------------

        // Join listing product tables
        //----------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'status' => 'status',
            ),
            '{{table}}.listing_id='.(int)$this->getListing()->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id',

                'template_category_id'  => 'template_category_id',
                'template_other_category_id'  => 'template_other_category_id',

                'template_payment_mode'  => 'template_payment_mode',
                'template_shipping_mode' => 'template_shipping_mode',
                'template_return_mode'   => 'template_return_mode',

                'template_description_mode'     => 'template_description_mode',
                'template_selling_format_mode'  => 'template_selling_format_mode',
                'template_synchronization_mode' => 'template_synchronization_mode',

                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(online_qty - online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_category'       => 'online_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_buyitnow_price' => 'online_buyitnow_price',
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            NULL,
            'left'
        );
        $collection->joinTable(
            array('etc' => 'M2ePro/Ebay_Template_Category'),
            'id=template_category_id',
            array(
                'category_main_mode'      => 'category_main_mode',
                'category_main_id'        => 'category_main_id',
                'category_main_path'      => 'category_main_path',
                'category_main_attribute' => 'category_main_attribute',
            ),
            NULL,
            'left'
        );
        $collection->joinTable(
            array('etoc' => 'M2ePro/Ebay_Template_OtherCategory'),
            'id=template_other_category_id',
            array(
                'category_secondary_mode'      => 'category_secondary_mode',
                'category_secondary_id'        => 'category_secondary_id',
                'category_secondary_path'      => 'category_secondary_path',
                'category_secondary_attribute' => 'category_secondary_attribute',

                'store_category_main_mode'      => 'store_category_main_mode',
                'store_category_main_id'        => 'store_category_main_id',
                'store_category_main_path'      => 'store_category_main_path',
                'store_category_main_attribute' => 'store_category_main_attribute',

                'store_category_secondary_mode'      => 'store_category_secondary_mode',
                'store_category_secondary_id'        => 'store_category_secondary_id',
                'store_category_secondary_path'      => 'store_category_secondary_path',
                'store_category_secondary_attribute' => 'store_category_secondary_attribute',
            ),
            NULL,
            'left'
        );

        if ($this->motorsSpecificsAttribute) {
            $collection->addAttributeToSelect($this->motorsSpecificsAttribute->getAttributeCode());

            $collection->joinTable(
                array('eea' => Mage::getSingleton('core/resource')->getTableName('eav_entity_attribute')),
                'attribute_set_id=attribute_set_id',
                array(
                    'is_motors_attribute_in_product_attribute_set' => 'entity_attribute_id',
                ),
                '{{table}}.attribute_id = ' . $this->motorsSpecificsAttribute->getAttributeId(),
                'left'
            );
        }
        //----------------------------
//        exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumns();

        $this->addColumnAfter('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / SKU / eBay Category'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => 'M2ePro/adminhtml_ebay_listing_view_settings_grid_column_filter_titleSkuCategory',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ), 'product_id');

        if ($this->isMotorsSpecificsAvailable() && $this->motorsSpecificsAttribute) {
            $this->addColumnAfter('motors_specifics_attribute_value', array(
                'header'    => Mage::helper('M2ePro')->__('Compatibility'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'options',
                'index'     => $this->motorsSpecificsAttribute->getAttributeCode(),
                'sortable'  => false,
                'options'   => array(
                    1 => Mage::helper('M2ePro')->__('Filled'),
                    0 => Mage::helper('M2ePro')->__('Empty')
                ),
                'frame_callback' => array($this, 'callbackColumnMotorsSpecificsAttribute'),
                'filter_condition_callback' => array($this, 'callbackFilterMotorsSpecificsAttribute'),
            ), 'name');
        }

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _prepareMassaction()
    {
        // Set mass-action
        //--------------------------------

        $this->getMassactionBlock()->addItem('editPrimaryCategorySettings', array(
             'label'    => Mage::helper('M2ePro')->__('Edit eBay Primary Categories Settings'),
             'url'      => '',
        ));

        if ($this->getListing()->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $this->getMassactionBlock()->addItem('editStorePrimaryCategorySettings', array(
                 'label'    => Mage::helper('M2ePro')->__('Edit eBay Store Primary Categories Settings'),
                 'url'      => '',
            ));
        }

        $this->getMassactionBlock()->addItem('editCategorySettings', array(
             'label'    => Mage::helper('M2ePro')->__('Edit eBay Categories Settings'),
             'url'      => '',
        ));

        parent::_prepareMassaction();

        if ($this->isMotorsSpecificsAvailable()) {
            $this->getMassactionBlock()->addItem('editMotorsSpecifics', array(
                'label' => Mage::helper('M2ePro')->__('Add Compatible Vehicles'),
                'url'   => ''
            ));
        }
        //------------------------------

        return $this;
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        $value = parent::callbackColumnTitle($value, $row, $column, $isExport);

        $value .= '<br><br>';

        if ($row->getData('category_main_mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            $value .= $this->getCategoryInfoHtml(
                $helper->__('eBay Primary Category'),
                '<span style="color: red">'.$helper->__('Not Set').'</span>'
            );
        } else {
            $value .= $this->getEbayCategoryInfoHtml($row,'category_main',$helper->__('eBay Primary Category'));
        }

        $value .= $this->getEbayCategoryInfoHtml($row,'category_secondary',$helper->__('eBay Secondary Category'));

        $value .= $this->getStoreCategoryInfoHtml($row,'category_main',$helper->__('eBay Store Primary Category'));
        $value .= $this->getStoreCategoryInfoHtml($row,'category_secondary',$helper->__('eBay Store Secondary Category'));
        $value .= '<br>';

        return $value;
    }

    public function callbackColumnMotorsSpecificsAttribute($value, $row, $column, $isExport)
    {
        if (!$this->motorsSpecificsAttribute) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_motors_attribute_in_product_attribute_set')) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $attributeCode = $this->motorsSpecificsAttribute->getAttributeCode();
        $attributeValue = $row->getData($attributeCode);
        $explodedAttributeValue = (explode(',', $attributeValue));

        (!isset($explodedAttributeValue[0]) || empty($explodedAttributeValue[0]))
            ? $countOfValues = 0
            : $countOfValues = count($explodedAttributeValue);

        $html = <<<HTML
    <div style="padding: 4px; color: #666666">
        <span style="text-decoration: underline; font-weight: bold">ePIDs:</span>&nbsp;
        <span>{$countOfValues}</span><br/>
HTML;
        if ($countOfValues) {

            $label = Mage::helper('M2ePro')->__('Show');
            $popupContent = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
                $this->getMotorsSpecificsEpidsHtml($row, $attributeCode, $explodedAttributeValue)
            ));

            $html .= <<<HTML
        [<a href="javascript:void(0);"
            onclick="EbayListingSettingsGridHandlerObj.showEpidsDetails('{$popupContent}');">{$label}</a>]
HTML;
        }
        return $html.'</div>';
    }

    // ####################################

    public function callbackFilterTitle($collection, $column)
    {
        if (!is_null($inputValue = $column->getFilter()->getValue('input'))) {

            $fieldsToFilter = array(
                array('attribute'=>'sku','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'name','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'category_main_path','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'category_secondary_path','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'store_category_main_path','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'store_category_secondary_path','like'=>'%'.$inputValue.'%'),
            );

            if (is_numeric($inputValue)) {
                $fieldsToFilter[] = array('attribute'=>'category_main_id','eq'=>$inputValue);
                $fieldsToFilter[] = array('attribute'=>'category_secondary_id','eq'=>$inputValue);
                $fieldsToFilter[] = array('attribute'=>'store_category_main_id','eq'=>$inputValue);
                $fieldsToFilter[] = array('attribute'=>'store_category_secondary_id','eq'=>$inputValue);
            }

            $collection->addFieldToFilter($fieldsToFilter);
        }

        if (!is_null($selectValue = $column->getFilter()->getValue('select'))) {
            $collection->addFieldToFilter('template_category_id',array(($selectValue ? 'notnull' : 'null') => true));
        }
    }

    public function callbackFilterMotorsSpecificsAttribute(Varien_Data_Collection_Db $collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if (!$this->motorsSpecificsAttribute) {
            return;
        }

        $attributeCode = $this->motorsSpecificsAttribute->getAttributeCode();

        if ($value == 1) {
            $collection->addFieldToFilter($attributeCode,array('notnull'=>true));
            $collection->addFieldToFilter($attributeCode,array('neq'=>'\'\''));
            $collection->addFieldToFilter('is_motors_attribute_in_product_attribute_set',array('notnull'=>true));
        } else {
            $collection->addFieldToFilter(array(
                array('attribute'=>$attributeCode,'null'=>true),
                array('attribute'=>$attributeCode,'eq'=>'\'\''),
                array('attribute'=>'is_motors_attribute_in_product_attribute_set','null'=>true)
            ));
        }
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    private function getEbayCategoryInfoHtml($row, $modeNick, $modeTitle)
    {
        $helper = Mage::helper('M2ePro');
        $mode = $row->getData($modeNick.'_mode');

        if (is_null($mode) || $mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return '';
        }

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {

            $category = $helper->__('Magento Attribute'). ' -> ';
            $category.= $helper->escapeHtml(
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $row->getData($modeNick.'_attribute'),
                    $this->getListing()->getStoreId()
                )
            );

        } else {
            $category = $helper->escapeHtml($row->getData($modeNick.'_path')).' ('.$row->getData($modeNick.'_id').')';
        }

        return $this->getCategoryInfoHtml($modeTitle, $category);
    }

    private function getStoreCategoryInfoHtml($row, $modeNick, $modeTitle)
    {
        $helper = Mage::helper('M2ePro');
        $mode = $row->getData('store_'.$modeNick.'_mode');

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return '';
        }

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {

            $category = $helper->__('Magento Attribute'). ': ';
            $category.= $helper->escapeHtml(
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $row->getData('store_'.$modeNick.'_attribute'),
                    $this->getListing()->getStoreId()
                )
            );

        } else {
            $category = $helper->escapeHtml($row->getData('store_'.$modeNick.'_path')).
                        ' ('.$row->getData('store_'.$modeNick.'_id').')';
        }

        return $this->getCategoryInfoHtml($modeTitle, $category);
    }

    private function getCategoryInfoHtml($modeTitle, $category)
    {
        return <<<HTML
    <div>
        <span style="text-decoration: underline">{$modeTitle}:</span>
        <p style="padding: 2px 0 0 10px">{$category}</p>
    </div>
HTML;
    }

    // ------------------------------------

    private function getMotorsSpecificsEpidsHtml($row, $attributeCode, $explodedAttributeValue)
    {
        $helper = Mage::helper('M2ePro');

        $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode);
        $attributeValue = implode(', ',$explodedAttributeValue);

        return <<<HTML
    <div style="padding-top: 10px;">
        <div style="margin: 2px 0;">
            <span style="font-weight: bold;">{$helper->__('Product')}:</span>&nbsp;
            <span style="color: grey;">{$row->getData('name')}</span>
        </div>
        <div style="margin: 2px 0 6px 0;">
            <span style="font-weight: bold;">{$helper->__('Attribute')}:</span>&nbsp;
            <span style="color: grey;">{$attributeLabel}</span><br/>
        </div>
        <div style="margin: 2px 0; height: 300px; overflow-x: auto;">
            <span style="text-decoration: underline; font-weight: bold">{$helper->__('ePIDs')}:</span>
            <p style="margin-top: 2px;">{$attributeValue}</p>
        </div>
    </div>
    <div style="float: right; margin-top: 10px;">
        <a href="javascript:void(0);" onclick="Windows.getFocusedWindow().close()">{$helper->__('Close')}<a/>
    </div>
HTML;
    }

    // ####################################

    protected function getActionColumnOptions()
    {
        $options = parent::getActionColumnOptions();

        array_unshift($options,array(
            'label' => $this->__('Edit eBay Categories Settings'),
            'value' => 'editCategorySettings'
        ));

        if ($this->getListing()->getAccount()->getChildObject()->getEbayStoreCategories()) {
            array_unshift($options,array(
                'label' => $this->__('Edit eBay Store Primary Categories Settings'),
                'value' => 'editStorePrimaryCategorySettings'
            ));
        }

        array_unshift($options,array(
            'label' => $this->__('Edit eBay Primary Categories Settings'),
            'value' => 'editPrimaryCategorySettings'
        ));

        if ($this->isMotorsSpecificsAvailable()) {
            $options[] =  array(
                'label' => Mage::helper('M2ePro')->__('Add Compatible Vehicles'),
                'value'   => 'editMotorsSpecifics'
            );
        }

        return $options;
    }

    // ####################################

    protected function _toHtml()
    {
        //------------------------------
        $urls = Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_listing',array('_current' => true));

        $path = 'adminhtml_ebay_listing/getCategoryChooserHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_listing/getCategorySpecificHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_listing/saveCategoryTemplate';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $urls = json_encode($urls);
        //------------------------------

        //------------------------------
        $translations = json_encode(array(
            'eBay Categories' => $this->__('eBay Categories'),
            'Specifics' => $this->__('Specifics'),
            'Compatibility Attribute ePIDs' => $this->__('Compatibility Attribute ePIDs')
        ));
        //------------------------------

        //------------------------------
        $constants = Mage::helper('M2ePro')->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');
        //------------------------------
        $html = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants},'Ess_M2ePro_Helper_Component_Ebay_Category');

</script>
HTML;

        //------------------------------
        if ($this->getRequest()->getParam('auto_actions')) {
            $html .= <<<HTML
<script type="text/javascript">
    Event.observe(window, 'load', function() {
        EbayListingAutoActionHandlerObj.loadAutoActionHtml();
    });
</script>
HTML;
        }
        //------------------------------

        //------------------------------
        if ($this->isMotorsSpecificsAvailable()) {
            $motorsSpecificsBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_motor_specific_generateAttributeValue', '', array(
                    'products_grid_id' => $this->getId()
                )
            );

            $html .= $motorsSpecificsBlock->toHtml();
        }
        //------------------------------

        return parent::_toHtml() . $html;
    }

    // ####################################

    private function isMotorsSpecificsAvailable()
    {
        if ($this->getListing()->getMarketplaceId() != Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            return false;
        }

        return true;
    }

    // ####################################

    /**
     * @inheritdoc
     **/
    protected function getListing()
    {
        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Listing', $this->getRequest()->getParam('id')
            );
        }

        return $this->listing;
    }

    // ####################################
}