<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingSearchGrid');
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
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $collection->getSelect()->distinct();
        $collection->getSelect()
            ->join(array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                   '(`l`.`id` = `main_table`.`listing_id`)',
                   array('listing_title'=>'title','store_id'))
            ->join(array('pl'=>Mage::getResourceModel('M2ePro/Play_Listing')->getMainTable()),
                   '(`pl`.`listing_id` = `l`.`id`)',
                   array('template_selling_format_id'));
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
        //->join(array('csi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
//                             '(csi.product_id = `main_table`.product_id)',array('qty'))
            ->join(array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                '(cpe.entity_id = `main_table`.product_id)',
                array('magento_sku'=>'sku'))
            ->join(array('cisi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
                '(cisi.product_id = `main_table`.product_id AND cisi.stock_id = 1)',
                array('is_in_stock'))
            ->join(array('cpev'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')),
                "(`cpev`.`entity_id` = `main_table`.product_id)",
                array('value'))
            ->join(array('ea'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')),
                '(`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\')',
                array())
            ->where('`cpev`.`store_id` = ('.$dbSelect->__toString().')');
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

        $this->addColumn('stock_availability', array(
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

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('Reference Code'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'second_table.sku',
            'frame_callback' => array($this, 'callbackColumnSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('Identifier'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'second_table.general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('online_qty', array(
            'header' => Mage::helper('M2ePro')->__('Play.com QTY'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'second_table.online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty')
        ));

        $this->addColumn('online_price_gbr', array(
            'header' => Mage::helper('M2ePro')->__('Play.com Price GBP'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_price_gbr',
            'filter_index' => 'second_table.online_price_gbr',
            'frame_callback' => array($this, 'callbackColumnPriceGbr')
        ));

        $this->addColumn('online_price_euro', array(
            'header' => Mage::helper('M2ePro')->__('Play.com Price EUR'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_price_euro',
            'filter_index' => 'second_table.online_price_euro',
            'frame_callback' => array($this, 'callbackColumnPriceEuro')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '125px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive')
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

        $withoutImageHtml = '<a href="'
            .$this->getUrl('adminhtml/catalog_product/edit',
                array('id' => $productId))
            .'" target="_blank">'
            .$productId
            .'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/','show_products_thumbnails');
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
        $urlParams['back'] = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_play_listing/search');

        $listingUrl = $this->getUrl('*/adminhtml_common_play_listing/view',$urlParams);
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));

        if (strlen($listingTitle) > 50) {
            $listingTitle = substr($listingTitle, 0, 50) . '...';
        }

        $value .= '<br/><hr style="border:none; border-top:1px solid silver; margin: 2px 0px;"/>';
        $value .= '<strong>'
            .Mage::helper('M2ePro')->__('Listing')
            .': </strong> <a href="'
            .$listingUrl
            .'">'
            .$listingTitle
            .'</a>';

        $tempSku = $row->getData('magento_sku');
        is_null($tempSku)
            && $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('product_id'))->getSku();

        $value .= '<br/><strong>'
            .Mage::helper('M2ePro')->__('SKU')
            .':</strong> '
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

    public function callbackColumnSku($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }
        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            if (is_null($value) || $value === '') {
                return Mage::helper('M2ePro')->__('N/A');
            }
        } else {
            if (is_null($value) || $value === '') {
                return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
            }
        }

        $generalIdType = $row->getData('general_id_type');

        if (is_null($row->getData('link_info'))) {
            return '<strong>'.Mage::helper('M2ePro')->__($generalIdType).':</strong><p>'.$value.'</p>';
        }

        $linkInfo = json_decode($row->getData('link_info'),true);
        $url = Mage::helper('M2ePro/Component_Play')->getItemUrl($linkInfo['play_id'], $linkInfo['category_code']);

        return '<strong>'.$generalIdType.': </strong><br/><a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            if (is_null($value) || $value === '') {
                return Mage::helper('M2ePro')->__('N/A');
            }
        } else {
            if (is_null($value) || $value === '') {
                return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
            }
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPriceGbr($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            (is_null($value) || $value === '')) {

            return '<i style="color:gray;">receiving...</i>';
        }

        if ((float)$value <= 0) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return Mage::app()->getLocale()->currency('GBP')->toCurrency($value);
    }

    public function callbackColumnPriceEuro($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            (is_null($value) || $value === '')) {

            return '<i style="color:gray;">receiving...</i>';
        }

        if ((float)$value <= 0) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return Mage::app()->getLocale()->currency('EUR')->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $value .= $this->getViewLogIconHtml($row->getId(),
            $row->getData('listing_id'),
            $row->getData('product_id'));

        $tempLocks = $row->getObjectLocks();

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'new_sku_action':
                    $title = Mage::helper('M2ePro')->__('New SKU In Progress...');
                    $value .= '<br><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'list_action':
                    $title = Mage::helper('M2ePro')->__('List In Progress...');
                    $value .= '<br><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'relist_action':
                    $title = Mage::helper('M2ePro')->__('Relist In Progress...');
                    $value .= '<br><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'revise_action':
                    $title = Mage::helper('M2ePro')->__('Revise In Progress...');
                    $value .= '<br><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'stop_action':
                    $title = Mage::helper('M2ePro')->__('Stop In Progress...');
                    $value .= '<br><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'stop_and_remove_action':
                    $title = Mage::helper('M2ePro')->__('Stop And Remove In Progress...');
                    $value .= '<br><span style="color: #605fff">['.$title.']</span>';
                    break;

                default:
                    break;

            }
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to listing'));
        $iconSrc = $this->getSkinUrl('M2ePro').'/images/goto_listing.png';
        $url = $this->getUrl('*/adminhtml_common_play_listing/view/', array(
            'id'=>$row->getData('listing_id'),
            'filter'=>base64_encode(
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
        return $this->getUrl('*/adminhtml_common_play_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}