<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareCollection()
    {
        // Update statistic table values
        Mage::getResourceModel('M2ePro/Listing')->updateStatisticColumns();
        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateStatisticColumns();

        // Get collection of listings
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
        $collection->getSelect()->join(array('a'=>Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                                       '(`a`.`id` = `main_table`.`account_id`)',
                                       array('account_title'=>'title'));
        $collection->getSelect()->join(array('m'=>Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                                       '(`m`.`id` = `main_table`.`marketplace_id`)',
                                       array('marketplace_title'=>'title'));

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Title / Info'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('products_total_count', array(
            'header'    => Mage::helper('M2ePro')->__('Total Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_total_count',
            'filter_index' => 'main_table.products_total_count',
            'frame_callback' => array($this, 'callbackColumnTotalProducts')
        ));

        $this->addColumn('products_active_count', array(
            'header'    => Mage::helper('M2ePro')->__('Active Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_active_count',
            'filter_index' => 'main_table.products_active_count',
            'frame_callback' => array($this, 'callbackColumnListedProducts')
        ));

        $this->addColumn('products_inactive_count', array(
            'header'    => Mage::helper('M2ePro')->__('Inactive Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_inactive_count',
            'filter_index' => 'main_table.products_inactive_count',
            'frame_callback' => array($this, 'callbackColumnInactiveProducts')
        ));

        $this->addColumn('items_sold_count', array(
            'header'    => Mage::helper('M2ePro')->__('Sold QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'items_sold_count',
            'filter_index' => 'second_table.items_sold_count',
            'frame_callback' => array($this, 'callbackColumnSoldQTY')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '50px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('`main_table`.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set clear log action
        //--------------------------------
        $this->getMassactionBlock()->addItem('clear_logs', array(
             'label'    => Mage::helper('M2ePro')->__('Clear Log(s)'),
             'url'      => $this->getUrl('*/adminhtml_listing/clearLog',array(
                 'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing/index',array(
                     'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING
                 ))
             )),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        // Set remove listings action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete_listings', array(
             'label'    => Mage::helper('M2ePro')->__('Delete Listing(s)'),
             'url'      => $this->getUrl('*/adminhtml_ebay_listing/delete'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<span id="listing_title_'.$row->getId().'">' .
                    Mage::helper('M2ePro')->escapeHtml($value) .
                 '</span>';

        /* @var $row Ess_M2ePro_Model_Listing */
        $accountTitle = $row->getData('account_title');
        $marketplaceTitle = $row->getData('marketplace_title');

        $storeModel = Mage::getModel('core/store')->load($row->getStoreId());
        $storeView = $storeModel->getWebsite()->getName();
        if (strtolower($storeView) != 'admin') {
            $storeView .= ' -> '.$storeModel->getGroup()->getName();
            $storeView .= ' -> '.$storeModel->getName();
        } else {
            $storeView = Mage::helper('M2ePro')->__('Admin (Default Values)');
        }

        $account = Mage::helper('M2ePro')->__('eBay User ID');
        $marketplace = Mage::helper('M2ePro')->__('eBay Site');
        $store = Mage::helper('M2ePro')->__('Magento Store View');

        $value .= <<<HTML
<div>
    <span style="font-weight: bold">{$account}</span>: <span style="color: #505050">{$accountTitle}</span><br>
    <span style="font-weight: bold">{$marketplace}</span>: <span style="color: #505050">{$marketplaceTitle}</span><br>
    <span style="font-weight: bold">{$store}</span>: <span style="color: #505050">{$storeView}</span>
</div>
HTML;

        return $value;
    }

    public function callbackColumnTotalProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnListedProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnSoldQTY($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnInactiveProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $id = (int)$row->getData('listing_id');

        $optionsHtml = '<option></option>';

        foreach ($this->getActionColumnOptions() as $optionId => $label) {
            $optionsHtml .= <<<HTML
            <option value="{$optionId}">{$label}</option>
HTML;
        }

        return <<<HTML
<div style="padding: 5px;">
    <select
        style="width: 100px;"
        onchange="this.value && EbayListingGridHandlerObj[this.value + 'Action']({$id}); this.value = -1;">
        {$optionsHtml}
    </select>
</div>
HTML;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/listingGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_ebay_listing/view', array(
            'id' => $row->getId()
        ));
    }

    // ####################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR a.title LIKE ? OR m.title LIKE ?',
            '%'.$value.'%'
        );
    }

    protected function getActionColumnOptions()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'manageProducts' => $helper->__('Manage Products'),
            'addProductsSourceProducts' => $helper->__('Add Products from Products List'),
            'addProductsSourceCategories' => $helper->__('Add Products from Categories'),
            'autoActions' => $helper->__('Automatic Actions'),
            'viewLogs' => $helper->__('View Logs'),
            'delete' => $helper->__('Delete Listing'),
            'editTitle' => $helper->__('Edit Listing Title'),
            'editSettings' => $helper->__('Edit Listing Settings'),
            'editPaymentAndShipping' => $helper->__('&nbsp;- Payment And Shipping'),
            'editSelling' => $helper->__('&nbsp;- Selling'),
            'editSynchronization' => $helper->__('&nbsp;- Synchronization'),
        );

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            unset($actions['autoActions']);
            unset($actions['editSynchronization']);
        }

        return $actions;
    }

    // ####################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $urls = json_encode(array_merge(
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_listing'),
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_listing_productAdd'),
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_log'),
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_template')
        ));

        $translations = json_encode(array(
            'Cancel' => Mage::helper('M2ePro')->__('Cancel'),
            'Save' => Mage::helper('M2ePro')->__('Save'),
            'Edit Listing Title' => Mage::helper('M2ePro')->__('Edit Listing Title'),
        ));

        $javascriptsMain = <<<HTML

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        M2ePro.url.add({$urls});
        M2ePro.translator.add({$translations});

        EbayListingGridHandlerObj = new EbayListingGridHandler('{$this->getId()}');
    });

</script>

HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    // ####################################
}