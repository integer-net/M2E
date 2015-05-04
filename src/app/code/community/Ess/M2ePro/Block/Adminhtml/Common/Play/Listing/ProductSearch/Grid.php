<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_ProductSearch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $productId;

    public function __construct()
    {
        parent::__construct();

        $this->productId = Mage::helper('M2ePro/Data_Global')->getValue('product_id');

        // Initialization block
        //------------------------------
        $this->setId('playProductSearchGrid');
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
        $data = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $results = new Varien_Data_Collection();
        foreach ($data as $index => $item) {
            $temp = array(
                'id' => $index,
                'general_id' => isset($item['general_id']) ? $item['general_id'] : null,
                'category_code' => isset($item['category_code']) ? $item['category_code'] : null,
                'product_url' => isset($item['product_url']) ? $item['product_url'] : null,
                'title' => isset($item['title']) ? $item['title'] : null,
                'image_url' => isset($item['image_url']) ? $item['image_url'] : null,
                'price_gbr' => isset($item['price_gbr']) ? $item['price_gbr'] : null,
                'variations' => isset($item['variations']) ? $item['variations'] : null
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
            'header'       => Mage::helper('M2ePro')->__('Play ID'),
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
            'string_limit' => 10000,
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle'),
        ));

        $this->addColumn('price_gbr',array(
            'header'       => Mage::helper('catalog')->__('Price'),
            'width'        => '60px',
            'align'        => 'right',
            'index'        => 'price_gbr',
            'filter'       => false,
            'sortable'     => false,
            'type'         => 'text',
            'frame_callback' => array($this, 'callbackColumnPriceGbr')
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
        return '<img width="75px" src="'.$value.'" />';
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        $templateHtml = '<a href="%play_link%" target="_blank">%general_id%</a>';

        if (empty($value)) {
            return '<span id="play_link_'.$row->getId().'">' . Mage::helper('M2ePro')->__('N/A') . '</span>' .
                   '<div id="template_play_link_'.$row->getId().'" style="display: none;">'.$templateHtml.'</div>';
        }

        $templateHtml = str_replace('%general_id%',$value,$templateHtml);
        return str_replace('%play_link%',$row->getData('product_url'),$templateHtml);
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px; margin-bottom: 10px;">'.
                        Mage::helper('M2ePro')->escapeHtml($value)."</div>";

        $variations = $row->getData('variations');
        if (is_null($variations)) {
            return $value;
        }

        $specificsHtml = '';
        $id = $row->getId();
        foreach ($variations['set'] as $specificName => $specific) {
            $specificsHtml .= '<span style="margin-left: 10px;
                                            font-size: 11px;
                                            color: #808080;
                                            display: inline-block;
                                            width: 100px;">'.
                                    ucfirst(strtolower($specificName)).
                              ':</span>';
            $specificsHtml .= '<select class="specifics_'.$id.'"
                                       onchange="ListingGridHandlerObj.productSearchHandler.specificsChange(this)"
                                       style="width: 150px; margin-left: 5px; margin-bottom: 5px; font-size: 10px;"
                                       id="specific_'.$specificName.'_'.$id.'">';
            $specificsHtml .= '<option value=""></option>';
            foreach ($specific as $option) {
                $specificsHtml .= '<option value="'.$option.'">'.$option.'</option>';
            }
            $specificsHtml .= '</select><br/>';
        }

        $specificsJsonContainer = '<div id="skus_'.$id.'" style="display: none;">'.
                                    json_encode($variations['play_ids']).'</div>';

        $linksJsonContainer = '<div id="links_'.$id.'" style="display: none;">'.
                                json_encode($variations['urls']).'</div>';

        return $value . $specificsHtml . $specificsJsonContainer . $linksJsonContainer;
    }

    public function callbackColumnPriceGbr($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        return '<div id="price_'.$row->getId().'" style="margin-right: 5px;">'.$value.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign Play ID');
        //->__('There is no such variation on Play ID. Please, choose another variation.');
        $naMessage = 'There is no such Variation on Play ID. Please, choose another Variation.';
        $naMessage = Mage::helper('M2ePro')->__($naMessage);

        if (!is_null($row->getData('variations'))) {
            $templateMapHtml =
                '<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId('
                .$this->productId
                .', \'%general_id%\');">'.$assignText.'</a>';

            $templateNaHtml = '<a href="javascript:void(0);" onclick="alert(\''.$naMessage.'\')">'.$assignText.'</a>';

            return '<span id="map_link_'.$row->getId().'"><span style="color: #808080">'.$assignText.'</span></span>
                    <div id="template_map_link_'.$row->getId().'" style="display: none;">'.$templateMapHtml.'</div>
                    <div id="template_na_link_'.$row->getId().'" style="display: none;">'.$templateNaHtml.'</div>';
        }

        return '<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId('
            .$this->productId
            .', \''
            .$row->getData('general_id')
            .'\');">'.$assignText.'</a>';
    }

    // ####################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#playProductSearchGrid div.grid th').each(function(el){
        el.style.padding = '2px 2px';
    });

    $$('#playProductSearchGrid div.grid td').each(function(el){
        el.style.padding = '2px 2px';
    });

</script>
JAVASCRIPT;

        //------------------------------
        $data = array(
            'id'    => 'productSearch_cleanSuggest_button',
            'label' => Mage::helper('M2ePro')->__('Clear Search Results'),
            'class' => 'productSearch_cleanSuggest_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        //------------------------------

        $buttonBlockHtml = Mage::helper('M2ePro/Data_Global')->getValue('is_suggestion') ? $buttonBlock->toHtml() : '';

        return $buttonBlockHtml . parent::_toHtml() . $javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_play_listing/getSuggestedPlayIDGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}