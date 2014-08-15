<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_ProductSearch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $productId;

    public function __construct()
    {
        parent::__construct();

        $this->productId = Mage::helper('M2ePro/Data_Global')->getValue('product_id');

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
        $data = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $results = new Varien_Data_Collection();
        foreach ($data as $index => $item) {
            $temp = array(
                'id' => $index,
                'general_id' => $item['general_id'],
                'brand' => $item['brand'],
                'title' => $item['title'],
                'image_url' => $item['image_url'],
                'price' => isset($item['list_price']['amount']) ? $item['list_price']['amount'] : null,
                'is_variation_product' => $item['is_variation_product'],
            );

            if ($temp['is_variation_product']) {
                $temp += array(
                    'parentage' => $item['parentage'],
                    'variations' => $item['variations']
                );

                if (!empty($item['requested_child_id'])) {
                    $temp['requested_child_id'] = $item['requested_child_id'];
                }
            }

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
            'string_limit' => 10000,
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
        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $value, Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id')
        );

        return '<a id="asin_link_'.$product->getData('id').'" href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px; margin-bottom: 10px;">'.
                        Mage::helper('M2ePro')->escapeHtml($value)."</div>";

        $variations = $row->getData('variations');
        if (is_null($variations)) {
            return $value;
        }

        $specificsJs = '<script type="text/javascript">';
        $specificsHtml = '';
        $id = $row->getId();
        $requestedChildAsin = $row->getData('requested_child_id');

        $selectedOptions = array();
        if ($requestedChildAsin) {
            $selectedOptions = $variations['asins'][$requestedChildAsin]['specifics'];
        }

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

                $selected = '';
                if ($requestedChildAsin && $selectedOptions[$specificName] == $option) {
                    $selected = 'selected';
                    $specificsJs .= <<<JS
ListingGridHandlerObj.productSearchHandler.specificsChange({id:"specific_{$specificName}_{$id}"});
JS;

                }

                $option = Mage::helper('M2ePro')->escapeHtml($option);
                $specificsHtml .= '<option value="'.$option.'" '.$selected.'>'.$option.'</option>';
            }
            $specificsHtml .= '</select><br />';
        }

        $specificsJs .= '</script>';

        $specificsJsonContainer = '<div id="parent_asin_'.$row->getId().'" style="display: none">'.
                                  $row->getData('general_id').
                                  '</div>'.
                                  '<div id="asins_'.$id.'" style="display: none;">'.
                                  json_encode($variations['asins']).
                                  '</div>';

        return $value . $specificsHtml . $specificsJsonContainer . $specificsJs;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if (empty($value) || $row->getData('is_variation_product')) {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        return '<div style="margin-right: 5px;">'.$value.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign ASIN/ISBN');

        if (!is_null($row->getData('variations'))) {
            $templateMapHtml =
                '<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId('
                .$this->productId
                .', \'%general_id%\');">'.$assignText.'</a>';

            return '<span id="map_link_'.$row->getId().'"><span style="color: #808080">'.$assignText.'</span></span>
                    <div id="template_map_link_'.$row->getId().'" style="display: none;">'.$templateMapHtml.'</div>';
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

    $$('#amazonProductSearchGrid div.grid th').each(function(el){
        el.style.padding = '2px 2px';
    });

    $$('#amazonProductSearchGrid div.grid td').each(function(el){
        el.style.padding = '2px 2px';
    });

</script>
JAVASCRIPT;

        //------------------------------
        $data = array(
            'id'    => 'productSearch_cleanSuggest_button',
            'label' => Mage::helper('M2ePro')->__('Clear Search Result'),
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
        return $this->getUrl('*/adminhtml_common_amazon_listing/getSuggestedAsinGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}