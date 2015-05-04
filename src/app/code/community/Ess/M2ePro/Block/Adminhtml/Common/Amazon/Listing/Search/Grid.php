<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingSearchGrid');
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
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->distinct();
        $collection->getSelect()
                   ->join(array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                                '(`l`.`id` = `main_table`.`listing_id`)',
                                array('listing_title'=>'title','store_id','marketplace_id'))
                   ->join(array('al'=>Mage::getResourceModel('M2ePro/Amazon_Listing')->getMainTable()),
                                '(`al`.`listing_id` = `l`.`id`)',
                                array('template_selling_format_id'));
        //--------------------------------

        // only parents and individuals
        $collection->getSelect()->where('second_table.variation_parent_id IS NULL');

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
                   ->join(array('cisi'=>Mage::getSingleton('core/resource')
                                                ->getTableName('cataloginventory_stock_item')),
                                '(cisi.product_id = `main_table`.product_id AND cisi.stock_id = 1)',
                                array('is_in_stock'))
                   ->join(array('cpev'=>Mage::getSingleton('core/resource')
                                                ->getTableName('catalog_product_entity_varchar')),
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
            'header'    => Mage::helper('M2ePro')->__('Product Title / Listing / Product SKU'),
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

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'second_table.sku',
            'frame_callback' => array($this, 'callbackColumnAmazonSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'second_table.general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('online_qty', array(
            'header' => Mage::helper('M2ePro')->__('QTY'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'second_table.online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty')
        ));

        $this->addColumn('online_price', array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'second_table.online_price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('is_afn_channel', array(
            'header' => Mage::helper('M2ePro')->__('Fulfillment'),
            'width' => '90px',
            'index' => 'is_afn_channel',
            'filter_index' => 'second_table.is_afn_channel',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                0 => Mage::helper('M2ePro')->__('Merchant'),
                1 => Mage::helper('M2ePro')->__('Amazon')
            ),
            'frame_callback' => array($this, 'callbackColumnAfnChannel')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '125px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Inactive (Blocked)')
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
                                                                          ->getGroupValue('/view/',
                                                                                          'show_products_thumbnails');
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
        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        $urlParams = array();
        $urlParams['id'] = $row->getData('listing_id');
        $urlParams['back'] = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_amazon_listing/search');

        $listingUrl = $this->getUrl('*/adminhtml_common_amazon_listing/view',$urlParams);
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

        if ($row->getChildObject()->getVariationManager()->isVariationParent()) {
            $productOptions = $row->getChildObject()->getVariationManager()
                ->getTypeModel()->getProductAttributes();

            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey;"><br/>';
            $value .= implode(', ', $productOptions);
            $value .= '</div>';
        }

        if ($row->getChildObject()->getVariationManager()->isIndividualType() &&
            $row->getChildObject()->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {
            $productOptions = $row->getChildObject()->getVariationManager()->getTypeModel()->getProductOptions();

            $value .= '<br/>';
            $value .= '<div style="font-size: 11px; color: grey;"><br/>';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }
            $value .= '</div>';
            $value .= '<br/>';
        }

        return $value;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
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
                return '<i style="color:gray;">receiving...</i>';
            }
        }

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($value, $row->getData('marketplace_id'));

        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            if (is_null($value) || $value === '') {
                return Mage::helper('M2ePro')->__('N/A');
            }
        } else {
            if (is_null($value) || $value === '') {
                return '<i style="color:gray;">receiving...</i>';
            }
        }

        if ((bool)$row->getData('is_afn_channel')) {
            return '--';
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            if (is_null($value) || $value === '') {
                return Mage::helper('M2ePro')->__('N/A');
            }
        } else {
            if (is_null($value) || $value === '') {
                return '<i style="color:gray;">receiving...</i>';
            }
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $marketplaceId = Mage::helper('M2ePro/Component_Amazon')
                                ->getCachedObject('Listing',
                                                  $row->getData('listing_id'), NULL,
                                                  array('listing'))
                                ->getMarketplaceId();

        $currency = Mage::helper('M2ePro/Component_Amazon')
                                ->getCachedObject('Marketplace',$marketplaceId)
                                ->getChildObject()
                                ->getDefaultCurrency();

        $value = Mage::app()->getLocale()->currency($currency)->toCurrency($value);

        $salePriceValue = $row->getData('online_sale_price');
        if (!is_null($salePriceValue) &&
            (float)$salePriceValue > 0) {
            $salePriceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($salePriceValue);
            $value .= '<br/><span style="color:gray;">'.$salePriceValue.'</span>';
        }

        return $value;
    }

    public function callbackColumnAfnChannel($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        switch ($row->getData('is_afn_channel')) {
            case Ess_M2ePro_Model_Amazon_Listing_Product::IS_ISBN_GENERAL_ID_YES:
                $value = '<span style="font-weight: bold;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $tempLocks = $row->getObjectLocks();

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'list_action':
                    $value .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $value .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $value .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $value .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                default:
                    break;

            }
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc = $this->getSkinUrl('M2ePro/images/goto_listing.png');
        $url = $this->getUrl('*/adminhtml_common_amazon_listing/view/',array(
            'id' => $row->getData('listing_id'),
            'filter' => base64_encode(
                'product_id[from]='.(int)$row->getData('product_id')
                .'&product_id[to]='.(int)$row->getData('product_id')
            )
        ));

        $html = <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$url}"><img src="{$iconSrc}" alt="{$altTitle}" /></a>
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
        return $this->getUrl('*/adminhtml_common_amazon_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}