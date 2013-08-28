<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingSearchGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------

        $this->attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                    ->load()->toOptionHash();
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection products in listing
        //--------------------------------
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->distinct();
        $collection->getSelect()->join(array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                                       '(`l`.`id` = `main_table`.`listing_id`)',
                                       array('listing_title'=>'title','store_id','template_selling_format_id'));
        //--------------------------------

        // Communicate with magento product table
        //--------------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                                     ->select()
                                     ->from(Mage::getSingleton('core/resource')
                                                ->getTableName('catalog_product_entity_varchar'),
                                            new Zend_Db_Expr('MAX(`store_id`)'))
                                     ->where("`entity_id` = `main_table`.`product_id`")
                                     ->where("`attribute_id` = `ea`.`attribute_id`")
                                     ->where("`store_id` = 0 OR `store_id` = `l`.`store_id`");

        $collection->getSelect()
                   //->join(array('csi'=>Mage::getSingleton('core/resource')
                   //                     ->getTableName('cataloginventory_stock_item')),
                   //       '(csi.product_id = `main_table`.product_id)',array('qty'))
                   ->join(array('cpe'=>Mage::getSingleton('core/resource')
                                        ->getTableName('catalog_product_entity')),
                          '(cpe.entity_id = `main_table`.product_id)',array('sku'))
                   ->join(array('cisi'=>Mage::getSingleton('core/resource')
                                        ->getTableName('cataloginventory_stock_item')),
                          '(cisi.product_id = `main_table`.product_id AND cisi.stock_id = 1)',array('is_in_stock'))
                   ->join(array('cpev'=>Mage::getSingleton('core/resource')
                                        ->getTableName('catalog_product_entity_varchar')),
                          "( `cpev`.`entity_id` = `main_table`.product_id
                             AND cpev.store_id = (".$dbSelect->__toString()."))", array('value'))
                   ->join(array('ea'=>Mage::getSingleton('core/resource')
                                        ->getTableName('eav_attribute')),
                          '(`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\')',array())
                   ->joinLeft(array('ebit'=>Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
                              '(`ebit`.`id` = `second_table`.`ebay_item_id`)',array('item_id'));
        //--------------------------------

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'product_id',
            'filter_index' => 'main_table.product_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Listing / SKU'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'value',
            'filter_index' => 'cpev.value',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('stock_availability',
            array(
                'header'=> Mage::helper('M2ePro')->__('Stock Availability'),
                'width' => '100px',
                'index' => 'is_in_stock',
                'filter_index' => 'cisi.is_in_stock',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    1 => Mage::helper('M2ePro')->__('In Stock'),
                    0 => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnStockAvailability')
        ));

        $this->addColumn('ebay_item_id', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Item ID'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'text',
            'index'     => 'item_id',
            'filter_index' => 'ebit.item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('online_available_qty', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Available QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_qty_sold',
            'filter'    => false,
            'sortable'  => false,
            'filter_index' => 'second_table.online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty')
        ));

        $this->addColumn('online_qty_sold', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Sold QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_qty_sold',
            'filter_index' => 'second_table.online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
        ));

        $this->addColumn('online_buyitnow_price', array(
            'header'    => Mage::helper('M2ePro')->__('"Buy It Now" Price'),
            'align'     =>'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_buyitnow_price',
            'filter_index' => 'second_table.online_buyitnow_price',
            'frame_callback' => array($this, 'callbackColumnOnlineBuyItNowPrice')
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('M2ePro')->__('Status'),
                'width' => '100px',
                'index' => 'status',
                'filter_index' => 'main_table.status',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN    => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_SOLD       => Mage::helper('M2ePro')->__('Sold'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Stopped'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED   => Mage::helper('M2ePro')->__('Finished')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $this->addColumn('goto_listing_item', array(
            'header'    => Mage::helper('M2ePro')->__('Manage'),
            'align'     => 'center',
            'width'     => '50px',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $productId = (int)$row->getData('product_id');
        $storeId = (int)$row->getData('store_id');

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
        $withoutImageHtml = '<a href="'.$url.'" target="_blank">'.$productId.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')
                                                ->getConfig()->getGroupValue('/products/settings/','show_thumbnails');
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $imageUrlResized = $magentoProduct->getThumbnailImageLink();
        if (is_null($imageUrlResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $productId.'<hr/><img src="'.$imageUrlResized.'" />';
        $withImageHtml = str_replace('>'.$productId.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 60) {
            $value = substr($value, 0, 60) . '...';
        }

        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        $urlParams = array();
        $urlParams['id'] = $row->getData('listing_id');
        $urlParams['back'] = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing/search');

        $listingUrl = $this->getUrl('*/adminhtml_ebay_listing/view',$urlParams);
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));

        if (strlen($listingTitle) > 50) {
            $listingTitle = substr($listingTitle, 0, 50) . '...';
        }

        $value .= '<br/><hr style="border:none; border-top:1px solid silver; margin: 2px 0px;"/>';
        $value .= '<strong>'.Mage::helper('M2ePro')->__('Listing')
                 .': </strong> <a href="'.$listingUrl.'">'.$listingTitle.'</a>';

        $tempSku = $row->getData('sku');
        is_null($tempSku) && $tempSku = Mage::getModel('M2ePro/Magento_Product')
                                                ->setProductId($row->getData('product_id'))->getSku();

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU').':</strong> '
                 .Mage::helper('M2ePro')->escapeHtml($tempSku);

        return $value;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $value = '<a href="'.$this->getUrl('*/adminhtml_ebay_listing/gotoEbay/', array('item_id' => $value))
                .'" target="_blank">'.$value.'</a>';

        return $value;
    }

    public function callbackColumnOnlineAvailableQty($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $value = $row->getData('online_qty') - $row->getData('online_qty_sold');

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnOnlineQtySold($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnOnlineBuyItNowPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $currency = Mage::helper('M2ePro/Component_Ebay')
                                ->getCachedObject('Template_SellingFormat',
                                                  $row->getData('template_selling_format_id'), NULL,
                                                  array('template'))
                                ->getChildObject()
                                ->getCurrency();

        return Mage::app()->getLocale()->currency($currency)->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $value = '<span style="color: brown;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $value = '<span style="color: blue;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to listing'));
        $iconSrc = $this->getSkinUrl('M2ePro').'/images/goto_listing.png';
        $url = $this->getUrl('*/adminhtml_ebay_listing/view/', array(
            'id' => $row->getData('listing_id'),
            'filter' => base64_encode(
                'product_id[from]='.(int)$row->getData('product_id')
                .'&product_id[to]='.(int)$row->getData('product_id')
            )
        ));

        $html = <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$url}"><img src="{$iconSrc}" /></a>
</div>
HTML;

        return $html;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('cpev.value LIKE ? OR cpe.sku LIKE ? OR l.title LIKE ?', '%'.$value.'%');
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}