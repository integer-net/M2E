<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_ProductSearch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $productId;
    private $currency;

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct  */
    private $listingProduct;
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $matcherAttributes */
    private $matcherAttributes;
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option $matcherOptions */
    private $matcherOptions;

    public function __construct()
    {
        parent::__construct();

        $this->productId = Mage::helper('M2ePro/Data_Global')->getValue('product_id');
        $this->listingProduct = Mage::getModel('M2ePro/Listing_Product')->load($this->productId);

        $this->matcherAttributes = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
        $this->matcherAttributes->setMarketplaceId(Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id'));

        $this->matcherOptions = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Option');
        $this->matcherOptions->setMarketplaceId(Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id'));

        $this->currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace', Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id'))
            ->getChildObject()
            ->getDefaultCurrency();

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
        foreach ($data['data'] as $index => $item) {
            $temp = array(
                'id' => $index,
                'general_id' => $item['general_id'],
                'brand' => $item['brand'],
                'title' => $item['title'],
                'image_url' => $item['image_url'],
                'price' => isset($item['list_price']['amount']) ? $item['list_price']['amount'] : null,
                'is_variation_product' => $item['is_variation_product']
            );

            if ($temp['is_variation_product']) {
                if(!$item['bad_parent']) {
                    $temp += array(
                        'parentage' => $item['parentage'],
                        'variations' => $item['variations'],
                        'bad_parent' => $item['bad_parent']
                    );
                } else {
                    $temp['bad_parent'] = $item['bad_parent'];
                }

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

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
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

        $this->addColumn('actions', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'center',
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
            ->getItemUrl($value, Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id'));

        $parentAsinText = Mage::helper('M2ePro')->__('parent ASIN/ISBN');

        return <<<HTML
<a id="asin_link_{$product->getData('id')}" href="{$url}" target="_blank">{$value}</a>
<div id="parent_asin_text_{$product->getData('id')}" style="font-size: 9px; color: grey; display: none">
    {$parentAsinText}
</div>
HTML;

    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px; margin-bottom: 3px;">'.
                        Mage::helper('M2ePro')->escapeHtml($value)."</div>";

        $id = $row->getId();
        $generalId = $row->getData('general_id');
        $categoryLinkTitle = Mage::helper('M2ePro')->escapeHtml('Show Categories');
        $notFoundText = Mage::helper('M2ePro')->__('Categories Not Found');

        $value .= <<<HTML
<div style="margin-left: 3px; margin-bottom: 10px; font-size:10px; line-height: 1.1em">
    <a href="javascript:void(0)"
        onclick="ListingGridHandlerObj.productSearchHandler.showAsinCategories(
            this, {$id}, '{$generalId}', {$this->productId})">
        {$categoryLinkTitle}
    </a>
    <div id="asin_categories_{$id}"></div>
    <div id="asin_categories_not_found_{$id}" style="display: none; font-style: italic">{$notFoundText}</div>
</div>
HTML;

        if(!$this->listingProduct->getChildObject()->getVariationManager()->isVariationProduct()
            || $this->listingProduct->getChildObject()->getVariationManager()->isIndividualType()) {
            if(!$row->getData('is_variation_product')) {
                return $value;
            }
        } else {
            if(!$row->getData('is_variation_product')) {
                return $value;
            }
        }

        if($row->getData('is_variation_product') && $row->getData('bad_parent')) {
            return $value;
        }

        $variations = $row->getData('variations');

        if($this->listingProduct->getChildObject()->getVariationManager()->isRelationParentType()) {

            $magentoProductAttributesHtml = '';
            $magentoProductAttributesJs = '';

            $destinationAttributes = array_keys($variations['set']);

            $this->matcherAttributes->setMagentoProduct($this->listingProduct->getMagentoProduct());
            $this->matcherAttributes->setDestinationAttributes($destinationAttributes);

            if($this->matcherAttributes->isAmountEqual()) {
                $magentoProductAttributesJs .= '<script type="text/javascript">';
                $magentoProductAttributesHtml .= '<div><span style="margin-left: 10px;
                                        font-size: 11px;
                                        font-weight: bold;
                                        color: #808080;
                                        display: inline-block;
                                        width: 170px;">' .
                    Mage::helper('M2ePro')->__('Magento Attributes') .
                    '</span><span style="margin-left: 10px;
                                        font-size: 11px;
                                        font-weight: bold;
                                        color: #808080;
                                        display: inline-block;">' .
                    Mage::helper('M2ePro')->__('Amazon Attributes') .
                    '</span></div>';

                $matchedAttributes = $this->matcherAttributes->getMatchedAttributes();
                $attributeId = 0;
                foreach($matchedAttributes as $magentoAttr => $amazonAttr){

                    $magentoProductAttributesHtml .= '<span style="margin-left: 10px;
                                            font-size: 11px;
                                            color: #808080;
                                            display: inline-block;
                                            width: 170px;">'.
                        ucfirst(strtolower($magentoAttr)).
                        '</span>';
                    $magentoProductAttributesHtml .= '<input type="hidden" value="' .
                                       Mage::helper('M2ePro')->escapeHtml($magentoAttr) . '"
                                       id="magento_product_attribute_'.$attributeId.'_'.$id.'">';
                    $magentoProductAttributesHtml .= '<select class="amazon_product_attribute_'.$id.'"
                                       onchange="ListingGridHandlerObj.productSearchHandler.attributesChange(this)"
                                       style="width: 170px; margin-left: 5px; margin-right: 5px;
                                              margin-bottom: 7px; font-size: 10px;"
                                       id="amazon_product_attribute_'.$attributeId.'_'.$id.'">';

                    if (!array_key_exists($amazonAttr,$variations['set']))
                    {
                        $magentoProductAttributesHtml .= '<option class="empty" value=""></option>';
                    }

                    foreach ($variations['set'] as $attrKey => $attrData) {

                        $selected = '';
                        if ($attrKey == $amazonAttr) {
                            $selected = 'selected';
                            $magentoProductAttributesJs .= <<<JS
ListingGridHandlerObj.productSearchHandler.attributesChange({id:"magento_product_attribute_{$magentoAttr}_{$id}"});
JS;
                        }

                        $attrKey = Mage::helper('M2ePro')->escapeHtml($attrKey);
                        $magentoProductAttributesHtml .= '<option value="'.$attrKey.'" '.$selected.'>'
                            .$attrKey.'</option>';
                    }
                    $magentoProductAttributesHtml .= '</select><br/>';
                    $attributeId++;
                }

                $magentoProductAttributesJs .= '</script>';

                $magentoProductAttributesHtml .= '<div id="variations_'.$id.'" style="display: none;">'.
                    json_encode($variations).
                    '</div>';
            } else {
                $value .= '<div style="font-size:11px;font-weight: bold;color: grey;margin-left: 7px;margin-top: 5px">';
                $value .= implode(', ', $destinationAttributes);
                $value .= '</div>';
            }

            return $value . $magentoProductAttributesHtml . $magentoProductAttributesJs;
        }

        $specificsHtml = '';
        $specificsJs = '<script type="text/javascript">';

        //match options for individual
        if ($this->listingProduct->getChildObject()->getVariationManager()->isIndividualType() &&
            $this->listingProduct->getChildObject()->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {
            $channelVariations = array();
            foreach($variations['asins'] as $asin => $asinAttributes) {
                $channelVariations[$asin] = $asinAttributes['specifics'];
            }

            $this->matcherAttributes->setMagentoProduct($this->listingProduct->getMagentoProduct());
            $this->matcherAttributes->setDestinationAttributes(array_keys($variations['set']));

            if ($this->matcherAttributes->isAmountEqual() && $this->matcherAttributes->isFullyMatched()) {
                $matchedAttributes = $this->matcherAttributes->getMatchedAttributes();

                $this->matcherOptions->setMagentoProduct($this->listingProduct->getMagentoProduct());
                $this->matcherOptions->setDestinationOptions($channelVariations);
                $this->matcherOptions->setMatchedAttributes($matchedAttributes);

                $productOptions = $this->listingProduct->getChildObject()->getVariationManager()
                    ->getTypeModel()->getProductOptions();

                $requestedChildAsin = $this->matcherOptions->getMatchedOptionGeneralId($productOptions);
            }
        }

        if (empty($requestedChildAsin)) {
            $requestedChildAsin = $row->getData('requested_child_id');
        }

        $selectedOptions = array();
        if ($requestedChildAsin) {
            $selectedOptions = $variations['asins'][$requestedChildAsin]['specifics'];
        }

        $specificsHtml .= '<form action="javascript:void(0);">';

        $attributesNames = '<span style="margin-left: 10px;
                                min-width: 100px;
                                max-width: 170px;
                                font-size: 11px;
                                color: #808080;
                                display: inline-block;">';
        $attributeValues = '<span style="margin-left: 5px; display: inline-block;">';
        foreach ($variations['set'] as $specificName => $specific) {
            $attributesNames .= '<span style="margin-bottom: 5px; display: inline-block;">'.
                                    ucfirst(strtolower($specificName)).
                              '</span><br/>';
            $attributeValues .= '<input type="hidden" value="' . Mage::helper('M2ePro')->escapeHtml($specificName) .
                                '" class="specifics_name_'.$id.'">';
            $attributeValues .= '<select class="specifics_'.$id.'"
                                       onchange="ListingGridHandlerObj.productSearchHandler.specificsChange(this)"
                                       style="width: 170px; margin-bottom: 5px; font-size: 10px;"
                                       id="specific_'.$specificName.'_'.$id.'">';
            $attributeValues .= '<option class="empty" value=""></option>';

            if (!empty($requestedChildAsin)) {
                foreach ($specific as $option) {

                    $selected = '';
                    if ($selectedOptions[$specificName] == $option) {
                        $selected = 'selected';
                    }

                    $option = Mage::helper('M2ePro')->escapeHtml($option);
                    $attributeValues .= '<option value="'.$option.'" '.$selected.'>'.$option.'</option>';
                }
            }

            $attributeValues .= '</select><br/>';

            $specificsJs .= <<<JS
ListingGridHandlerObj.productSearchHandler.specificsChange({id:"specific_{$specificName}_{$id}"});
JS;
        }

        $specificsHtml .= $attributesNames . '</span>';
        $specificsHtml .= $attributeValues . '</span>';
        $specificsHtml .= '</form>';

        $specificsJs .= '</script>';

        $variationAsins = json_encode($variations['asins']);
        $variationTree = json_encode($this->getChannelVariationsTree($variations));

        $specificsJsonContainer = <<<HTML
<div id="parent_asin_{$id}" style="display: none">{$generalId}</div>
<div id="asins_{$id}" style="display: none;">{$variationAsins}</div>
<div id="channel_variations_tree_{$id}" style="display: none;">{$variationTree}</div>
HTML;

        return $value . $specificsHtml . $specificsJsonContainer . $specificsJs;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if (empty($value) || $row->getData('is_variation_product')) {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else {
            $value = Mage::app()->getLocale()->currency($this->currency)->toCurrency($value);
        }

        return '<div style="margin-right: 5px;">'.$value.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign');
        $iconWarningPath = $this->getSkinUrl('M2ePro/images/warning.png');
        $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_notice.gif');

        if(!$this->listingProduct->getChildObject()->getVariationManager()->isVariationProduct()
            || $this->listingProduct->getChildObject()->getVariationManager()->isIndividualType()) {
            if (!$row->getData('is_variation_product')) {

                return <<<HTML
<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId(
    {$this->productId}, '{$row->getData('general_id')}');">{$assignText}</a>
HTML;
            }

            if(!$row->getData('bad_parent')) {

                $msg = Mage::helper('M2ePro')->__(
                    'Please select necessary Options for this Amazon Product to be able to assign ASIN/ISBN.'
                );

                return <<<HTML
<span>
    <span id="map_link_{$row->getId()}"><span style="color: #808080">{$assignText}</span></span>&nbsp;
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle; display: none;"
         src="{$iconWarningPath}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$iconHelpPath}">
        <span>{$msg}</span>
    </span>
</span>
<div id="template_map_link_{$row->getId()}" style="display: none;">
<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId(
    {$this->productId}, '%general_id%', '%options_data%'
);">{$assignText}</a>
</div>
HTML;
            }
        }

        if($row->getData('is_variation_product') && !$row->getData('bad_parent')) {

            $msg = Mage::helper('M2ePro')->__(
                'Please map Amazon and Magento Attributes for this Amazon Product to be able to assign ASIN/ISBN.'
            );

            $variations = $row->getData('variations');
            $this->matcherAttributes->setMagentoProduct($this->listingProduct->getMagentoProduct());
            $this->matcherAttributes->setDestinationAttributes(array_keys($variations['set']));

            if(!$this->matcherAttributes->isAmountEqual()) {
                $msg = Mage::helper('M2ePro')->__(
                    'This ASIN/ISBN cannot be assigned to selected Magento Product. <br/>
                     The number of Magento Attributes is different from Amazon Attributes.'
                );
            }

            return <<<HTML
<span>
    <span id="map_link_{$row->getId()}"><span style="color: #808080">{$assignText}</span></span>&nbsp;
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$iconWarningPath}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$iconHelpPath}">
        <span>{$msg}</span>
    </span>
</span>
<div id="template_map_link_{$row->getId()}" style="display: none;">
<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId(
    {$this->productId}, '{$row->getData('general_id')}', '%options_data%'
);">{$assignText}</a>
</div>
HTML;

        }

        $msg = Mage::helper('M2ePro')->__(
            'This ASIN/ISBN cannot be assigned to selected Magento Product. <br/>
             This Amazon Product has no Variations. <br/>
             Only Amazon Parent/Child Products can be assigned in "All Variations" Mode.'
        );

        if ($row->getData('is_variation_product') && $row->getData('bad_parent')) {
            $msg =  Mage::helper('M2ePro')->__(
                'This ASIN/ISBN cannot be assigned to selected Magento Product. <br/>
                 Amazon Service (API) does not return all required information about this Amazon Product.'
            );
        }

        return <<<HTML
<span>
    <span id="map_link_{$row->getId()}"><span style="color: #808080">{$assignText}</span></span>&nbsp;
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$iconWarningPath}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$iconHelpPath}">
        <span>{$msg}</span>
    </span>
</span>
HTML;
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

        $searchData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $searchParamsHtml = <<<HTML
        <input id="amazon_asin_search_type" type="hidden" value="{$searchData['type']}">
        <input id="amazon_asin_search_value" type="hidden" value="{$searchData['value']}">
HTML;

        return parent::_toHtml() . $javascriptsMain . $searchParamsHtml;
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

    private function getChannelVariationsTree($variations)
    {
        $channelVariations = array();
        foreach($variations['asins'] as $asin => $asinAttributes) {
            $channelVariations[$asin] = $asinAttributes['specifics'];
        }

        if (empty($channelVariations)) {
            return new stdClass();
        }

        $firstAttribute = key($variations['set']);

        return $this->prepareVariations(
            $firstAttribute, $channelVariations, $variations['set']
        );
    }

    private function prepareVariations($currentAttribute, $variations, $variationsSets,$filters = array())
    {
        $return = false;

        $temp = array_flip(array_keys($variationsSets));

        $lastAttributePosition = count($variationsSets) - 1;
        $currentAttributePosition = $temp[$currentAttribute];

        if ($currentAttributePosition != $lastAttributePosition) {

            $temp = array_keys($variationsSets);
            $nextAttribute = $temp[$currentAttributePosition + 1];

            foreach ($variationsSets[$currentAttribute] as $option) {

                $filters[$currentAttribute] = $option;

                $result = $this->prepareVariations(
                    $nextAttribute,$variations,$variationsSets,$filters
                );

                if (!$result) {
                    continue;
                }

                $return[$currentAttribute][$option] = $result;
            }

            ksort($return[$currentAttribute]);

            return $return;
        }

        $return = false;
        foreach ($variations as $key => $magentoVariation) {
            foreach ($magentoVariation as $attribute => $option) {

                if ($attribute == $currentAttribute) {

                    if (count($variationsSets) != 1) {
                        continue;
                    }

                    $values = array_flip($variationsSets[$currentAttribute]);
                    $return = array($currentAttribute => $values);

                    foreach ($return[$currentAttribute] as &$option) {
                        $option = true;
                    }

                    return $return;
                }

                if ($option != $filters[$attribute]) {
                    unset($variations[$key]);
                    continue;
                }

                foreach ($magentoVariation as $tempAttribute => $tempOption) {
                    if ($tempAttribute == $currentAttribute) {
                        $option = $tempOption;
                        $return[$currentAttribute][$option] = true;
                    }
                }
            }
        }

        if (count($variations) < 1) {
            return false;
        }

        ksort($return[$currentAttribute]);

        return $return;
    }

    // ####################################
}