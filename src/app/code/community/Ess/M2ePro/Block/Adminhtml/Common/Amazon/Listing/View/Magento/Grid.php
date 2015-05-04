<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View_Magento_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('amazonListingViewMagentoGrid'.$listingData['id']);
        //------------------------------

        $this->hideMassactionColumn = true;
        $this->hideMassactionDropDown = true;
        $this->showAdvancedFilterProductsOption = false;
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_view_modeSwitcher'
        );
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection
        //----------------------------
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource());
        $collection->getSelect()->group('e.entity_id');
        $collection
            ->addAttributeToSelect('name')
            ->joinTable(
                array('cisi' => 'cataloginventory/stock_item'),
                'product_id=entity_id',
                array('qty' => 'qty',
                    'is_in_stock' => 'is_in_stock'),
                '{{table}}.stock_id=1',
                'left'
            );

        //----------------------------

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'amazon_status' => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id']
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'        => 'general_id',
                'amazon_sku'        => 'sku',
                'online_qty'        => 'online_qty',
                'online_price'      => 'online_price',
                'online_sale_price' => 'online_sale_price',
                'is_afn_channel'    => 'is_afn_channel'
            ),
            NULL,
            'left'
        );
        //----------------------------

        // Set filter store
        //----------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'price', 'catalog_product/price', 'entity_id', NULL, 'left', $store->getId()
            );
            $collection->joinAttribute(
                'status', 'catalog_product/status', 'entity_id', NULL, 'inner',$store->getId()
            );
            $collection->joinAttribute(
                'visibility', 'catalog_product/visibility', 'entity_id', NULL, 'inner',$store->getId()
            );
            $collection->joinAttribute(
                'thumbnail', 'catalog_product/thumbnail', 'entity_id', NULL, 'left',$store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }
        //----------------------------

//        exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        parent::_prepareCollection();

        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Title'),
            'align'     => 'left',
            //'width'     => '100px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $tempTypes = Mage::getSingleton('catalog/product_type')->getOptionArray();
        unset($tempTypes['virtual']);

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options' => $tempTypes
        ));

        $this->addColumn('is_in_stock', array(
            'header'    => Mage::helper('M2ePro')->__('Stock Availability'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => array(
                '1' => Mage::helper('M2ePro')->__('In Stock'),
                '0' => Mage::helper('M2ePro')->__('Out of Stock')
            ),
            'frame_callback' => array($this, 'callbackColumnIsInStock')
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('M2ePro')->__('SKU'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'text',
            'index'     => 'sku',
            'filter_index' => 'sku'
        ));

        $store = $this->_getStore();

        $this->addColumn('price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'price',
            'filter_index' => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('M2ePro')->__('QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'qty',
            'filter_index' => 'qty',
            'frame_callback' => array($this, 'callbackColumnQty')
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('M2ePro')->__('Visibility'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'visibility',
            'filter_index' => 'visibility',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray()
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('M2ePro')->__('Status'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'status',
            'filter_index' => 'status',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        if (!Mage::app()->isSingleStoreMode()) {

            $this->addColumn('websites', array(
                'header'    => Mage::helper('M2ePro')->__('Websites'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'websites',
                'filter_index' => 'websites',
                'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
            ));
        }

        return parent::_prepareColumns();
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    // ####################################

    protected function _getStore()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get store filter
        //----------------------------
        $storeId = $listing['store_id'];
        //----------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridHandlerObj.afterInitPage();
        }, 350);
    });

</script>
JAVASCRIPT;

        return parent::_toHtml().$javascriptsMain;
    }

    // ####################################

    protected function isShowRuleBlock()
    {
        /** @var $ruleModel Ess_M2ePro_Model_Magento_Product_Rule */
        $ruleModel = Mage::helper('M2ePro/Data_Global')->getValue('rule_model');
        if ($ruleModel->isEmpty()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    // ####################################

}