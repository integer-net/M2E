<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
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
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection products in listing
        //--------------------------------
        $nameAttribute = Mage::getResourceModel('catalog/product')->getAttribute('name');
        $nameAttributeId = $nameAttribute ? (int)$nameAttribute->getId() : 0;

        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->distinct();

        $listingProductCollection->getSelect()->join(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '`l`.`id` = `main_table`.`listing_id`'
        );

        $listingProductCollection->getSelect()->join(
            array('em' => Mage::getResourceModel('M2ePro/Ebay_Marketplace')->getMainTable()),
            '`em`.`marketplace_id` = `l`.`marketplace_id`'
        );

        $listingProductCollection->getSelect()->join(
            array('cpe' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
            'cpe.entity_id = `main_table`.product_id'
        );

        $listingProductCollection->getSelect()->joinLeft(
            array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')),
            '`cpev`.`entity_id` = `main_table`.`product_id`'
                . ' AND `cpev`.`attribute_id` = ' . $nameAttributeId
                . ' AND `cpev`.`store_id` = 0'
        );

        $listingProductCollection->getSelect()->joinLeft(
            array('ebit' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
            '(`ebit`.`id` = `second_table`.`ebay_item_id`)',
            array('item_id')
        );
        //------------------------------

        // add stock availability, status & visibility to select
        //------------------------------
        $listingProductCollection->getSelect()->joinLeft(
            array('cisi' => Mage::getResourceModel('cataloginventory/stock_item')->getMainTable()),
            '(`cisi`.`product_id` = `main_table`.`product_id` AND `cisi`.`stock_id` = 1)',
            array('is_in_stock')
        );
        //------------------------------

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns(
            array(
                'account_id'            => 'l.account_id',
                'marketplace_id'        => 'l.marketplace_id',
                'product_id'            => 'main_table.product_id',
                'product_name'          => 'cpev.value',
                'product_sku'           => 'cpe.sku',
                'currency'              => 'em.currency',
                'ebay_item_id'          => 'ebit.item_id',
                'status'                => 'main_table.status',
                'online_sku'            => 'second_table.online_sku',
                'online_title'          => 'second_table.online_title',
                'online_qty'            => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'       => 'second_table.online_qty_sold',
                'online_buyitnow_price' => 'second_table.online_buyitnow_price',
                'listing_id'            => 'l.id',
                'listing_title'         => 'l.title',
                'is_m2epro_listing'     => new Zend_Db_Expr(1),
                'is_in_stock'           => 'cisi.is_in_stock',
            )
        );
        //------------------------------

        //------------------------------
        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->getSelect()->distinct();

        // add stock availability, type id, status & visibility to select
        //------------------------------
        $listingOtherCollection->getSelect()->joinLeft(
            array('cisi' => Mage::getResourceModel('cataloginventory/stock_item')->getMainTable()),
            '(`cisi`.`product_id` = `main_table`.`product_id` AND cisi.stock_id = 1)',
            array('is_in_stock')
        );
        //------------------------------

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
                'account_id'            => 'main_table.account_id',
                'marketplace_id'        => 'main_table.marketplace_id',
                'product_id'            => 'main_table.product_id',
                'product_name'          => 'second_table.title',
                'product_sku'           => 'second_table.sku',
                'currency'              => 'second_table.currency',
                'ebay_item_id'          => 'second_table.item_id',
                'status'                => 'main_table.status',
                'online_sku'            => new Zend_Db_Expr('NULL'),
                'online_title'          => new Zend_Db_Expr('NULL'),
                'online_qty'            => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'       => 'second_table.online_qty_sold',
                'online_buyitnow_price' => 'second_table.online_price',
                'listing_id'            => new Zend_Db_Expr('NULL'),
                'listing_title'         => new Zend_Db_Expr('NULL'),
                'is_m2epro_listing'     => new Zend_Db_Expr(0),
                'is_in_stock'           => 'cisi.is_in_stock'
            )
        );
        //------------------------------

        //------------------------------
        $selects = array($listingProductCollection->getSelect());
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $selects[] = $listingOtherCollection->getSelect();
        }

        $unionSelect = Mage::getResourceModel('core/config')->getReadConnection()->select();
        $unionSelect->union($selects);

        $resultCollection = new Varien_Data_Collection_Db(Mage::getResourceModel('core/config')->getReadConnection());
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array(
                'account_id',
                'marketplace_id',
                'product_id',
                'product_name',
                'product_sku',
                'currency',
                'ebay_item_id',
                'status',
                'online_sku',
                'online_title',
                'online_qty',
                'online_qty_sold',
                'online_buyitnow_price',
                'listing_id',
                'listing_title',
                'is_m2epro_listing',
                'is_in_stock'
            )
        );
        //------------------------------

        $this->setCollection($resultCollection);
//        exit($resultCollection->getSelect().'');

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('M2ePro');

        $this->addColumn('product_id', array(
            'header'    => $helper->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'product_id',
            'filter_index' => 'main_table.product_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('product_name', array(
            'header'    => $helper->__('Product Title / Listing / SKU'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'product_name',
            'filter_index' => 'product_name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('is_in_stock', array(
            'header'    => $helper->__('Stock Availability'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => array(
                '1' => $helper->__('In Stock'),
                '0' => $helper->__('Out of Stock')
            ),
            'frame_callback' => array($this, 'callbackColumnIsInStock')
        ));

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $this->addColumn('is_m2epro_listing', array(
                'header'    => $helper->__('Listing Type'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'options',
                'index'     => 'is_m2epro_listing',
                'options'   => array(
                    1 => $helper->__('M2E Pro'),
                    0 => $helper->__('3rd Party')
                )
            ));
        }

        $this->addColumn('ebay_item_id', array(
            'header'    => $helper->__('eBay Item ID'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'text',
            'index'     => 'ebay_item_id',
            'filter_index' => 'ebay_item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('online_qty', array(
            'header'    => $helper->__('eBay Available QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty')
        ));

        $this->addColumn('online_qty_sold', array(
            'header'    => $helper->__('eBay Sold QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_qty_sold',
            'filter_index' => 'online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
        ));

        $this->addColumn('online_buyitnow_price', array(
            'header'    => $helper->__('"Buy It Now" Price'),
            'align'     =>'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_buyitnow_price',
            'filter_index' => 'online_buyitnow_price',
            'frame_callback' => array($this, 'callbackColumnOnlineBuyItNowPrice')
        ));

        $this->addColumn('status',
            array(
                'header'=> $helper->__('Status'),
                'width' => '100px',
                'index' => 'status',
                'filter_index' => 'status',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => $helper->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => $helper->__('Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => $helper->__('Listed (Hidden)'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_SOLD       => $helper->__('Sold'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => $helper->__('Stopped'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED   => $helper->__('Finished'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => $helper->__('Pending')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $this->addColumn('goto_listing_item', array(
            'header'    => $helper->__('Manage'),
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
        if (is_null($row->getData('product_id'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $productId = (int)$row->getData('product_id');
        $storeId = (int)$row->getData('store_id');

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
        $withoutImageHtml = '<a href="'.$url.'" target="_blank">'.$productId.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')
                                                ->getConfig()->getGroupValue('/view/','show_products_thumbnails');
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
        $onlineTitle = $row->getData('online_title');
        !empty($onlineTitle) && $value = $onlineTitle;

        $html = $value;

        if (strlen($html) > 60) {
            $html = substr($html, 0, 60) . '...';
        }

        $html = '<span>' . Mage::helper('M2ePro')->escapeHtml($html) . '</span>';

        $additional = $this->getListingHtml($row);
        $additional .= $this->getSkuHtml($row);

        if ($additional) {
            $html .= '<br/><hr style="border: none; border-top: 1px solid silver; margin: 2px 0px;"/>'
                . $additional;
        }

        return $html;
    }

    private function getListingHtml($row)
    {
        if (is_null($row->getData('listing_id'))) {
            return '';
        }

        $listingUrl = $this->getUrl('*/adminhtml_ebay_listing/view', array('id' => $row->getData('listing_id')));
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));

        if (strlen($listingTitle) > 50) {
            $listingTitle = substr($listingTitle, 0, 50) . '...';
        }

        return '<strong>' . Mage::helper('M2ePro')->__('Listing') . ':</strong>'
            . '&nbsp;<a href="'.$listingUrl.'" target="_blank">'.$listingTitle.'</a><br/>';
    }

    private function getSkuHtml($row)
    {
        $sku = $row->getData('product_sku');
        if (is_null($sku) && !is_null($row->getData('product_id'))) {
            $sku = Mage::getModel('M2ePro/Magento_Product')
                ->setProductId($row->getData('product_id'))
                ->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        if (!$sku && $row->getData('is_m2epro_listing')) {
            return '';
        }

        if (!$row->getData('is_m2epro_listing') && is_null($sku)) {
            $sku = '<i style="color:gray;">' . Mage::helper('M2ePro')->__('receiving') . '...</i>';
        } else if (!$row->getData('is_m2epro_listing') && !$sku) {
            $sku = '<i style="color:gray;">' . Mage::helper('M2ePro')->__('none') . '</i>';
        } else {
            $sku = Mage::helper('M2ePro')->escapeHtml($sku);
        }

        return '<strong>'. Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;' . $sku;
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('is_in_stock'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

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

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/gotoEbay/',
            array(
                'item_id' => $row->getData('ebay_item_id'),
                'account_id' => $row->getData('account_id'),
                'marketplace_id' => $row->getData('marketplace_id'),
            )
        );

        return '<a href="'. $url . '" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnOnlineAvailableQty($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

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

        $currency = $row->getCurrency();

        if (strpos($currency, ',') !== false) {
            $currency = Mage::helper('M2ePro/Component_Ebay')
                        ->getCachedObject('Marketplace',$row->getMarketplaceId())
                        ->getChildObject()->getCurrency();
        }

        return Mage::app()->getLocale()->currency($currency)->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $value = '<span style="color: red;">'.$value.'</span>';
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

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange;">'.$value.'</span>';
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

        if ($row->getData('is_m2epro_listing')) {
            $url = $this->getUrl('*/adminhtml_ebay_listing/view/', array(
                'view_mode' => Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View::VIEW_MODE_EBAY,
                'id' => $row->getData('listing_id'),
                'filter' => base64_encode(
                    'product_id[from]='.(int)$row->getData('product_id')
                    .'&product_id[to]='.(int)$row->getData('product_id')
                )
            ));
        } else {
            $url = $this->getUrl('*/adminhtml_ebay_listing_other/view/', array(
                'account' => $row->getData('account_id'),
                'marketplace' => $row->getData('marketplace_id'),
                'filter' => base64_encode(
                    'item_id='.$row->getData('ebay_item_id')
                )
            ));
        }

        return <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$url}"><img src="{$iconSrc}" /></a>
</div>
HTML;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'product_name LIKE ? OR product_sku LIKE ? OR listing_title LIKE ?'.
            ' OR online_sku LIKE ? OR online_title LIKE ?', '%'.$value.'%'
        );
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