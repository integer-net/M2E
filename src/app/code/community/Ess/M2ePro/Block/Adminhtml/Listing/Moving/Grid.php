<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Moving_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingMovingGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(100);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $componentMode = Mage::helper('M2ePro/Data_Global')->getValue('componentMode');
        $ignoreListings = (array)Mage::helper('M2ePro/Data_Global')->getValue('ignoreListings');

        // Update statistic table values
        Mage::getResourceModel('M2ePro/Listing')->updateStatisticColumns();
        Mage::getResourceModel('M2ePro/'.ucfirst($componentMode).'_Listing')->updateStatisticColumns();

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($componentMode, 'Listing')
            ->getCollection();

        foreach ($ignoreListings as $listingId) {
            $collection->addFieldToFilter('`main_table`.`id`', array('neq'=>$listingId));
        }

        $this->addAttributeSetFilter($collection);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('listing_id', array(
            'header'       => Mage::helper('M2ePro')->__('ID'),
            'align'        => 'right',
            'type'         => 'number',
            'width'        => '75px',
            'index'        => 'listing_id',
            'filter_index' => 'listing_id',
            'frame_callback' => array($this, 'callbackColumnId')
        ));

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '200px',
            'index'        => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('products_total_count', array(
            'header'        => Mage::helper('M2ePro')->__('Total Items'),
            'align'        => 'right',
            'type'         => 'number',
            'width'        => '100px',
            'index'        => 'products_total_count',
            'filter_index' => 'products_total_count',
            'frame_callback' => array($this, 'callbackColumnSourceTotalItems')
        ));

        $this->addColumn('source_products', array(
            'header'        => Mage::helper('M2ePro')->__('Source'),
            'align'        => 'left',
            'type'         => 'options',
            'width'        => '100px',
            'index'        => 'source_products',
            'options'      => array(
                Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CUSTOM=>Mage::helper('M2ePro')->__('Products List'),
                Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CATEGORIES=>Mage::helper('M2ePro')->__('Categories')
            ),
            'filter_index' => 'source_products',
            'frame_callback' => array($this, 'callbackColumnSource')
        ));

        $this->addColumn('store_name', array(
            'header'        => Mage::helper('M2ePro')->__('Store View'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '100px',
            'index'        => 'store_id',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnStore')
        ));

        $this->addColumn('actions', array(
            'header'       => Mage::helper('M2ePro')->__('Actions'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '125px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));
    }

    // ####################################

    public function callbackColumnId($value, $row, $column, $isExport)
    {
        return $value.'&nbsp;';
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $url = Mage::helper('M2ePro/View')->getUrl(
            $row, 'listing', 'view', array('id' => $row->getData('id'))
        );
        return '&nbsp;<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnStore($value, $row, $column, $isExport)
    {
        $storeModel = Mage::getModel('core/store')->load($value);
        $websiteName = $storeModel->getWebsite()->getName();

        if (strtolower($websiteName) != 'admin') {
            $storeName = $storeModel->getName();
        } else {
            $storeName = $storeModel->getGroup()->getName();
        }

        return '&nbsp;'.$storeName;
    }

    public function callbackColumnSource($value, $row, $column, $isExport)
    {
        return '&nbsp;'.$value;
    }

    public function callbackColumnSourceTotalItems($value, $row, $column, $isExport)
    {
        return $value.'&nbsp;';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $confirmMessage = Mage::helper('M2ePro')->__('Are you sure?');
        $actions = '&nbsp;<a href="javascript:void(0);" onclick="confirm(\''.$confirmMessage.'\') && ';
        $actions .= $this->getMovingHandlerJs() . '.tryToSubmit('.$row->getData('listing_id').');">';
        $actions .= 'Move To This Listing</a>';
        return $actions;
    }

    // ####################################

    protected function _toHtml()
    {
        $emptyGrid = json_encode(false);

        $warning = '';
        if ($this->getCollection()->getSize() < 1) {
            $warning = Mage::helper('M2ePro')->__(
                'Listings, which have the same Attribute Set, Marketplace and Account were not found.'
            );
            $emptyGrid = json_encode(true);
            $warning = <<<HTML
<div class="warning-msg" id="empty_grid_warning">
    <div style="margin: 10px 0 10px 35px; font-weight: bold;">$warning</div>
</div>
HTML;
            $warning = Mage::helper('M2ePro')->escapeJS($warning);
        }

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    var warning_msg_block = $('empty_grid_warning');
    warning_msg_block && warning_msg_block.remove();

    if ({$emptyGrid}) {
        $('{$this->getId()}').insert({
            before: '{$warning}'
        });
    }

    $$('#listingMovingGrid div.grid th').each(function(el){
        el.style.padding = '2px 4px';
    });

    $$('#listingMovingGrid div.grid td').each(function(el){
        el.style.padding = '2px 4px';
    });

</script>
JAVASCRIPT;

        return parent::_toHtml() . $javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function addAttributeSetFilter($collection)
    {
        $accountId = Mage::helper('M2ePro/Data_Global')->getValue('accountId');
        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplaceId');
        $attrSetId = Mage::helper('M2ePro/Data_Global')->getValue('attrSetId');

        $collection->getSelect()
            ->join(array('as'=>Mage::getResourceModel('M2ePro/AttributeSet')->getMainTable()),
                   '`main_table`.`id` = `as`.`object_id`',
                   null);

        if (is_array($attrSetId)) {
            $collection->addFieldToFilter('`as`.`attribute_set_id`', array('in'=>$attrSetId));
            $collection->getSelect()
                ->group('main_table.id')
                ->having('COUNT(`main_table`.`id`) >= ?', count($attrSetId));
        } else {
            $collection->addFieldToFilter('`as`.`attribute_set_id`', $attrSetId);
        }

        $collection->addFieldToFilter('`main_table`.`marketplace_id`', $marketplaceId);
        $collection->addFieldToFilter('`main_table`.`account_id`', $accountId);
        $collection->addFieldToFilter('`as`.`object_type`', Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_LISTING);
    }

    // ####################################
}