<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_ProductSearch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $productId;

    public function __construct()
    {
        parent::__construct();

        $this->productId = Mage::helper('M2ePro')->getGlobalValue('product_id');

        // Initialization block
        //------------------------------
        $this->setId('amazonProductSearchGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $data = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $results = new Varien_Data_Collection();
        foreach ($data as $index => $item) {
            $temp = array(
                'id' => $index,
                'general_id' => $item['general_id'],
                'brand' => $item['brand'],
                'title' => $item['title'],
                'image_url' => $item['image_url'],
                'price' => isset($item['list_price']['amount']) ? $item['list_price']['amount'] : null,
            );

            $results->addItem(new Varien_Object($temp));
        }

        $this->setCollection($results);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('image', array(
            'header'       => Mage::helper('M2ePro')->__('Image'),
            'align'        => 'center',
            'type'         => 'text',
            'width'        => '80px',
            'index'        => 'image_url',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnImage')
        ));

        $this->addColumn('general_id', array(
            'header'       => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'align'        => 'center',
            'type'         => 'text',
            'width'        => '75px',
            'index'        => 'general_id',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Product Title'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '375px',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle'),
        ));

        $this->addColumn('price',array(
            'header'       => Mage::helper('catalog')->__('Price'),
            'width'        => '60px',
            'align'        => 'right',
            'index'        => 'price',
            'filter'       => false,
            'sortable'     => false,
            'type'         => 'number',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('actions', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '78px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));

    }

    // ####################################

    public function callbackColumnImage($value, $product, $column, $isExport)
    {
        return '<img src="'.$value.'" />';
    }

    public function callbackColumnGeneralId($value, $product, $column, $isExport)
    {
        $url = Mage::helper('M2ePro/Component_Amazon')
                                         ->getItemUrl($value, Mage::helper('M2ePro')->getGlobalValue('marketplace_id'));

        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 60) {
            $value = substr($value, 0, 60) . '...';
        }

        $value = '<div style="margin-left: 3px">'.Mage::helper('M2ePro')->escapeHtml($value)."</div>";

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        return '<div style="margin-right: 5px;">'.$value.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        return '<a href="javascript:void(0);" onclick="AmazonListingProductSearchHandlerObj.mapToGeneralId('
               .$this->productId
               .', \''
               .$row->getData('general_id')
               .'\');">Assign ASIN/ISBN</a>';
    }

    // ####################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#amazonProductSearchGrid div.grid th').each(function(el){
        el.style.padding = '2px 2px';
    });

    $$('#amazonProductSearchGrid div.grid td').each(function(el){
        el.style.padding = '2px 2px';
    });

</script>
JAVASCRIPT;

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'productSearch_cleanSuggest_button',
            'label'   => Mage::helper('M2ePro')->__('Clear Search Result'),
            'class' => 'productSearch_cleanSuggest_button'
        ));

        $buttonBlockHtml = Mage::helper('M2ePro')->getGlobalValue('is_suggestion') ?
                            $buttonBlock->toHtml() :
                            '';

        return $buttonBlockHtml . parent::_toHtml() . $javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_listing/getSuggestedAsinGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}