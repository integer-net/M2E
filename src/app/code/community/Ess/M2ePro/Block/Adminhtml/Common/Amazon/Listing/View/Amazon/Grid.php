<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View_Amazon_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $sellingFormatTemplate Ess_M2ePro_Model_Amazon_Template_SellingFormat */
    private $sellingFormatTemplate = NULL;

    private $lockedDataCache = array();

    private $hideSwitchToIndividualConfirm;
    private $hideSwitchToParentConfirm;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $listing = Mage::helper('M2ePro/Component')->getUnknownObject('Listing', $listingData['id']);

        $this->hideSwitchToIndividualConfirm =
            $listing->getSetting('additional_data', 'hide_switch_to_individual_confirm', 0);

        $this->hideSwitchToParentConfirm =
            $listing->getSetting('additional_data', 'hide_switch_to_parent_confirm', 0);

        // Initialization block
        //------------------------------
        $this->setId('amazonListingViewAmazonGrid'.$listingData['id']);
        //------------------------------

        $this->showAdvancedFilterProductsOption = false;

        $this->sellingFormatTemplate = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Template_SellingFormat', $listingData['template_selling_format_id'], NULL,
            array('template')
        );
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_view_modeSwitcher'
        );
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection
        //----------------------------
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource());
        $collection->setListingProductModeOn();
        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->joinTable(
                array('cisi' => 'cataloginventory/stock_item'),
                'product_id=entity_id',
                array('qty' => 'qty'),
                '{{table}}.stock_id=1',
                'left'
            );

        //----------------------------

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'component_mode'  => 'component_mode',
                'amazon_status'   => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id']
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'                     => 'general_id',
                'general_id_search_info'         => 'general_id_search_info',
                'search_settings_status'         => 'search_settings_status',
                'search_settings_data'           => 'search_settings_data',
                'variation_child_statuses'       => 'variation_child_statuses',
                'amazon_sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_price'                   => 'online_price',
                'online_sale_price'              => 'online_sale_price',
                'online_sale_price_start_date'   => 'online_sale_price_start_date',
                'online_sale_price_end_date'     => 'online_sale_price_end_date',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
                'defected_messages'              => 'defected_messages'
            ),
            '{{table}}.variation_parent_id is NULL'
        );
        //----------------------------
//        exit($collection->getSelect()->__toString());

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
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'amazon_sku',
            'filter_index' => 'amazon_sku',
            'frame_callback' => array($this, 'callbackColumnAmazonSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '140px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('online_qty', array(
            'header' => Mage::helper('M2ePro')->__('QTY'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty')
        ));

        $this->addColumn('online_price', array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'online_price',
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
        ));

        $this->addColumn('is_afn_channel', array(
            'header' => Mage::helper('M2ePro')->__('Fulfillment'),
            'align' => 'right',
            'width' => '90px',
            'index' => 'is_afn_channel',
            'filter_index' => 'is_afn_channel',
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
            'width' => '155px',
            'index' => 'amazon_status',
            'filter_index' => 'amazon_status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Inactive (Blocked)')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        ));

        if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
            $this->addColumn('developer_action', array(
                'header'     => Mage::helper('M2ePro')->__('Actions'),
                'align'      => 'left',
                'width'      => '100px',
                'type'       => 'text',
                'renderer'   => 'M2ePro/adminhtml_listing_view_grid_column_renderer_developerAction',
                'index'      => 'value',
                'filter'     => false,
                'sortable'   => false,
                'js_handler' => 'ListingGridHandlerObj'
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $groups = array(
            'actions'            => Mage::helper('M2ePro')->__('Actions'),
            'description_policy' => Mage::helper('M2ePro')->__('Description Policy'),
            'asin_isbn'          => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'other'              => Mage::helper('M2ePro')->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem('list', array(
            'label'    => Mage::helper('M2ePro')->__('List Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('deleteAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Remove from Channel & Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('assignTemplateDescriptionId', array(
            'label'    => Mage::helper('M2ePro')->__('Assign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'description_policy');

        $this->getMassactionBlock()->addItem('unassignTemplateDescriptionId', array(
            'label'    => Mage::helper('M2ePro')->__('Unassign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'description_policy');

        $this->getMassactionBlock()->addItem('assignGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Search Automatically'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'asin_isbn');

        $this->getMassactionBlock()->addItem('newGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Assign Settings for New ASIN/ISBN'),
            'url'      => '',
        ), 'asin_isbn');

        $this->getMassactionBlock()->addItem('unassignGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Reset Information'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'asin_isbn');

        $this->getMassactionBlock()->addItem('moving', array(
            'label'    => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');

        $this->getMassactionBlock()->addItem('duplicate', array(
            'label'    => Mage::helper('M2ePro')->__('Duplicate'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');
        is_null($tempSku)
        && $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);

        if ($listingProduct->getChildObject()->isExistDescriptionTemplate()) {
            $value .= $this->getTemplateDescriptionLinkHtml($listingProduct);
        }

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        $generalId = $row->getData('general_id');

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {

            $productAttributes = (array)$variationManager->getTypeModel()->getProductAttributes();

            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
            $value .= implode(', ', $productAttributes);

            if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner()) {
                $popupTitle = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
                    Mage::helper('M2ePro')->__('Manage Magento Product Variations'))
                );

                $linkTitle = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
                    Mage::helper('M2ePro')->__('Change "Magento Variations" Mode'))
                );

                $iconSettingsPath = $this->getSkinUrl('M2ePro/images/settings.png');

                $switchToIndividualJsMethod = <<<JS
ListingProductVariationHandlerObj
    .setListingProductId({$listingProductId})
        .showSwitchToIndividualModePopUp('{$popupTitle}');
JS;

                if ($this->hideSwitchToIndividualConfirm) {
                    $switchToIndividualJsMethod = <<<JS
ListingProductVariationHandlerObj
    .setListingProductId({$listingProductId})
        .showManagePopup('{$popupTitle}');
JS;
                }

                $value .= <<<HTML
&nbsp;
<a  href="javascript:"
    onclick="{$switchToIndividualJsMethod}"
    title="{$linkTitle}">
    <img width="12" height="12" style="height: 12px; width: 12px; vertical-align: middle;" src="{$iconSettingsPath}">
</a>
HTML;
            }

            $value .= '</div>';

            if (!empty($generalId) || $amazonListingProduct->isGeneralIdOwner()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
                $parentType = $variationManager->getTypeModel();

                $linkContent = Mage::helper('M2ePro')->__('Manage Variations');
                $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $problemStyle = '';
                $problemIcon = '';

                $linkTitle = Mage::helper('M2ePro')->__('Open Manage Variations Tool');

                if (empty($generalId) && $amazonListingProduct->isGeneralIdOwner()) {
                    if (!$parentType->hasChannelTheme() || !$parentType->hasMatchedAttributes()) {

                        $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                        $problemStyle = 'style="font-weight: bold; color: #FF0000;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                } elseif (!empty($generalId)) {
                    if (!$parentType->hasMatchedAttributes()) {

                        $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;color: #FF0000;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    } elseif ($listingProduct->getChildObject()->isGeneralIdOwner() &&
                              !$parentType->hasChannelTheme()) {

                        $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/warning.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                }

                $value .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
    <a {$problemStyle}href="javascript:"
    onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}')"
    title="{$linkTitle}">{$linkContent}</a>&nbsp;{$problemIcon}
</div>
HTML;
            }

            return $value;
        }

        $productOptions = $variationManager->getTypeModel()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }
            $value .= '</div>';
        }

        // ---------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------

        if (!$hasInActionLock) {

            $popupTitle = Mage::helper('M2ePro')->__('Manage Magento Product Variation');
            $linkTitle  = Mage::helper('M2ePro')->__('Edit Variation');
            $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/pencil.png').'">';

            $value .= <<<HTML
<div style="clear: both"></div>
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showEditPopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;
        }

        $popupTitle = Mage::helper('M2ePro')->__('Manage Magento Product Variations')   ;
        $linkTitle  = Mage::helper('M2ePro')->__('Add Another Variation(s)');
        $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/add.png').'">';

        $value.= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;

        if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner()) {
            $linkTitle = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
                Mage::helper('M2ePro')->__('Change "Magento Variations" Mode'))
            );

            $iconSettingsPath = $this->getSkinUrl('M2ePro/images/settings.png');

            $switchToParentJsMethod = <<<JS
ListingProductVariationHandlerObj
    .setListingProductId({$listingProductId})
        .showSwitchToParentModePopUp('{$popupTitle}');
JS;

            if ($this->hideSwitchToParentConfirm) {
                $switchToParentJsMethod = <<<JS
ListingProductVariationHandlerObj
    .setListingProductId({$listingProductId})
        .resetListingProductVariation();
JS;
            }

            $value .= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a href="javascript:"
        onclick="{$switchToParentJsMethod}"
        title="{$linkTitle}">
    <img width="12" height="12" src="{$iconSettingsPath}">
</a>
</div>
HTML;
        }

        return $value;
    }

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_variation_parent') && $row->getData('defected_messages')) {
            $defectedMessages = json_decode($row->getData('defected_messages'), true);

            $msg = '';
            foreach ($defectedMessages as $message) {
                $msg .= '<p>'.$message['message'] . '&nbsp;';
                if (!empty($message['value'])) {
                    $msg .= Mage::helper('M2ePro')->__('Current Value') . ': "' . $message['value'] . '"';
                }
                $msg .= '</p>';
            }

            $value .= <<<HTML
<span style="float:right;">
    <img id="map_link_defected_message_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$msg}</span>
    </span>
</span>
HTML;
        }

        return $value;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            if($row->getData('is_general_id_owner') == 1){
                return $this->getGeneralIdColumnValueGeneralIdOwner($row);
            }
            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
            $row->getData('is_variation_parent')
        ) {
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
        if (is_null($value) || $value === '') {
            if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
                $row->getData('is_variation_parent')
            ) {
                return Mage::helper('M2ePro')->__('N/A');
            } else {
                return '<i style="color:gray;">receiving...</i>';
            }
        }

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');
        $currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace',$marketplaceId)
            ->getChildObject()
            ->getDefaultCurrency();

        if ((float)$value <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($value);
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_sale_price');
        if ((float)$salePrice > 0) {
            $currentTimestamp = strtotime(Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d 00:00:00'));

            $startDateTimestamp = strtotime($row->getData('online_sale_price_start_date'));
            $endDateTimestamp   = strtotime($row->getData('online_sale_price_end_date'));

            if ($currentTimestamp < $endDateTimestamp) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/help.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');

                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

                $fromDate = Mage::app()->getLocale()->date(
                    $row->getData('online_sale_price_start_date'), $dateFormat
                )->toString($dateFormat);
                $toDate = Mage::app()->getLocale()->date(
                    $row->getData('online_sale_price_end_date'), $dateFormat
                )->toString($dateFormat);

                $intervalHtml = '<img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'">
                            <span class="tool-tip-message" style="display:none; text-align: left; width: 110px;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    <strong>From:</strong> '.$fromDate.'<br/>
                                    <strong>To:</strong> '.$toDate.'
                                </span>
                            </span>';

                $salePriceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($salePrice);

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $salePrice < (float)$value
                ) {
                    $resultHtml .= '<span style="color: grey; text-decoration: line-through;">'.$priceValue.'</span>';
                    $resultHtml .= '<br/>'.$intervalHtml.$salePriceValue;
                } else {
                    $resultHtml .= $priceValue;
                    $resultHtml .= '<br/>'.$intervalHtml.'<span style="color:gray;">'.$salePriceValue.'</span>';
                }
            }
        }

        if (empty($resultHtml)) {
            $resultHtml = $priceValue;
        }

        return $resultHtml;
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
        $listingProductId = (int)$row->getData('id');

        $html = $this->getViewLogIconHtml($listingProductId);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        $synchNote = $listingProduct->getSetting('additional_data', 'synch_template_list_rules_note');
        if (!empty($synchNote)) {

            $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage($synchNote);

            if (empty($html)) {
                $html = <<<HTML
<span style="float:right;">
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$synchNote}</span>
    </span>
</span>
HTML;
            } else {
                $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
            }
        }

        if (!$variationManager->isVariationParent()) {
            return $html . $this->getProductStatus($row->getData('amazon_status')). $this->getLockedTag($row);
        } else {

            $generalId = $listingProduct->getGeneralId();
            $variationChildStatuses = $row->getData('variation_child_statuses');
            if (empty($generalId) || empty($variationChildStatuses)) {
                return $html . $this->getProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) .
                    $this->getLockedTag($row);
            }

            $variationChildStatuses = json_decode($variationChildStatuses, true);
            $variationChildStatuses = array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED =>
                    $variationChildStatuses[Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED],
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED =>
                    $variationChildStatuses[Ess_M2ePro_Model_Listing_Product::STATUS_LISTED],
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED =>
                    $variationChildStatuses[Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED],
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED =>
                    $variationChildStatuses[Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED]
            );

            $linkTitle = Mage::helper('M2ePro')->__('Show all Child Products with such Status');

            foreach ($variationChildStatuses as $status => $productsCount) {
                if (empty($productsCount)) {
                    continue;
                }

                $filter = base64_encode('status=' . $status);

                $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
                $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                $generalId = $row->getData('general_id');
                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $productsCount = <<<HTML
<a onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}', '{$filter}')"
   class="hover-underline"
   title="{$linkTitle}"
   href="javascript:void(0)">[{$productsCount}]</a>
HTML;

                $html .= '' . $this->getProductStatus($status) . '&nbsp;'. $productsCount . '<br/>';
            }

            $html .= $this->getLockedTag($row);
        }

        return $html;
    }

    private function getProductStatus($status)
    {
        switch ($status) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                return '<span style="color: green;">' . Mage::helper('M2ePro')->__('Active') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                return'<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                return'<span style="color: orange; font-weight: bold;">' .
                    Mage::helper('M2ePro')->__('Inactive (Blocked)') . '</span>';
        }

        return '';
    }

    private function getLockedTag($row)
    {
        $html = '';

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        $childCount = 0;

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'child_products_in_action':
                    $childCount++;
                    break;

                default:
                    break;

            }
        }

        if ($childCount > 0) {
            $html .= '<br/><span style="color: #605fff">[Child(s) in Action...]</span>';
        }

        return $html;
    }

    //----------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%')
            )
        );
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $from = $value['from'];
        $to   = $value['to'];

        $collection->getSelect()->where(
            '(alp.online_price >= \''.$from.'\' AND alp.online_price <= \''.$to.'\' AND
            (
                alp.online_sale_price IS NULL OR
                alp.online_sale_price_start_date > NOW() OR
                alp.online_sale_price_end_date < NOW()
            )) OR (alp.online_sale_price >= \''.$from.'\' AND alp.online_sale_price <= \''.$to.'\' AND
            (
                alp.online_sale_price IS NOT NULL AND
                alp.online_sale_price_start_date < NOW() AND
                alp.online_sale_price_end_date > NOW()
            ))'
        );
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where("lp.status = {$value} OR
            (alp.variation_child_statuses REGEXP '\"{$value}\":[^0]') AND alp.is_variation_parent = 1");
    }

    //----------------------------------------

    public function getViewLogIconHtml($listingProductId)
    {
        $listingProductId = (int)$listingProductId;

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Get last messages
        //--------------------------
        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator','listing_product_id')
            )
            ->where('`action_id` IS NOT NULL')
            ->order(array('id DESC'))
            ->limit(30);

        if($listingProduct->getChildObject()->getVariationManager()->isVariationParent()) {
            $dbSelect->where('listing_product_id = ? OR parent_listing_product_id = ?', $listingProductId);
        } else {
            $dbSelect->where('listing_product_id = ?', $listingProductId);
        }

        $logRows = $connRead->fetchAll($dbSelect);
        //--------------------------

        // Get grouped messages by action_id
        //--------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {

            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (count($tempActionRows) > 0) {
                    $actionsRows[] = array(
                        'action_id' => $lastActionId,
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }
                $lastActionId = $row['action_id'];
            }
            $tempActionRows[] = $row;
        }

        if (count($tempActionRows) > 0) {
            $actionsRows[] = array(
                'action_id' => $lastActionId,
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items' => $tempActionRows
            );
        }

        if (count($actionsRows) <= 0) {
            return '';
        }

        if ($listingProduct->getChildObject()->getVariationManager()->isVariationParent()) {
            foreach ($actionsRows as &$actionsRow) {
                if (!empty($actionsRow['items']) && $actionsRow['items'][0]['listing_product_id']==$listingProductId) {
                    continue;
                }
                $actionsRow['action_in_progress'] = $this->isActionInProgress($actionsRow['action_id']);

                $descArr = array();
                foreach ($actionsRow['items'] as $key => &$item) {
                    if (array_key_exists($item['description'], $descArr)) {
                        $descArr[$item['description']]['count']++;
                        unset($actionsRow['items'][$key]);
                        continue;
                    }
                    $item['count'] = 1;
                    $descArr[$item['description']] = $item;
                }
                $actionsRow['items'] = array_values($descArr);
            }
        }

        foreach ($actionsRows as &$actionsRow) {
            usort($actionsRow['items'], function($a, $b)
            {
                $sortOrder = array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 1,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 2,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 3,
                );

                return $sortOrder[$a["type"]] > $sortOrder[$b["type"]];
            });
        }

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Action was completed with warning(s).'
        );

        $icons = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'error',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
        );

        $summary = $this->getLayout()->createBlock('M2ePro/adminhtml_log_grid_summary', '', array(
            'entity_id' => $listingProductId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'ListingGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'ListingGridHandlerObj.hideItemHelp',
        ));

        return $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('List');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Revise');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Remove from Channel');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Remove from Channel & Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL:
                $string = Mage::helper('M2ePro')->__('Status Change');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['initiator']) {
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
                $string = Mage::helper('M2ePro')->__('Manual');
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $string = Mage::helper('M2ePro')->__('Automatic');
                break;
        }

        return $string;
    }

    public function getMainTypeForActionId($actionRows)
    {
        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
            }
            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            }
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Mage::app()->getLocale()->date(strtotime($actionRows[0]['create_date']))->toString($format);
    }

    public function isActionInProgress($actionId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Get last messages
        //--------------------------
        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Processing_Request')->getMainTable()
            )
            ->where('responser_params REGEXP \'"logs_action_id":'.$actionId.'\'');

        $result = $connRead->fetchAll($dbSelect);

        return !empty($result);
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function _getStore()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get store filter
        //----------------------------
        $storeId = $listing['store_id'];
        //----------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    // ####################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridHandlerObj.afterInitPage();
        }, 350);
    });

</script>
JAVASCRIPT;

        return parent::_toHtml().$javascriptsMain;
    }

    // ####################################

    private function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load($listingProductId)->getObjectLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action' => !empty($objectLocks),
            );
            $this->lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->lockedDataCache[$listingProductId];
    }

    // ####################################

    private function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------
        if ((int)$row->getData('amazon_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
        }
        // ---------------------------------

        // ---------------------------------
        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');
        // ---------------------------------

        // ---------------------------------
        $lpId = $row->getData('id');

        $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }
        $productTitle = Mage::helper('M2ePro')->__('Assign ASIN/ISBN For &quot;%product_title%&quot;', $productTitle);
        $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
        // ---------------------------------

        // ---------------------------------

        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------
        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {

            $tip = Mage::helper('M2ePro')->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $iconPath.'processing.gif';

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt="">
</a>
HTML;
        }
        // ---------------------------------

        // ---------------------------------
        $searchSettingsData = $row->getData('search_settings_data');

        $suggestData = array();
        if (!is_null($searchSettingsData)) {
            $searchSettingsData = @json_decode($searchSettingsData,true);
            !empty($searchSettingsData['data']) && $suggestData = $searchSettingsData['data'];

        }
        // ---------------------------------

        $na = Mage::helper('M2ePro')->__('N/A');

        if (!empty($suggestData)) {

            $tip = Mage::helper('M2ePro')->__('Choose ASIN/ISBN from the list');
            $iconSrc = $iconPath.'list.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
        }

        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND) {

            $tip = Mage::helper('M2ePro')->__(
                'There were no Products found on Amazon according to the Listing Search Settings.'
            );
            $tip = Mage::helper('M2ePro')->escapeJs($tip);

            $iconSrc = $iconPath.'error.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript: void(0);" title="{$tip}"
    onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId},'{$tip}');">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
        }

        $tip = Mage::helper('M2ePro')->__('Search for ASIN/ISBN');
        $iconSrc = $iconPath.'search.png';

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId});">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
    }

    private function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');
        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $generalId,
            $marketplaceId
        );

        $generalIdOwnerHtml = '';
        if($row->getData('is_general_id_owner') == Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES){

            $generalIdOwnerHtml = '<br/><span style="font-size: 10px; color: grey;">'.
                                   Mage::helper('M2ePro')->__('creator of ASIN/ISBN').
                                  '</span>';
        }

        if ((int)$row->getData('amazon_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {

            return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>{$generalIdOwnerHtml}
HTML;
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = @json_decode($generalIdSearchInfo, true);
        }

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {

            $tip = Mage::helper('M2ePro')->__('ASIN/ISBN was found automatically');

            $text = <<<HTML
<a href="{$url}" target="_blank" title="{$tip}" style="color:#40AADB;">{$generalId}</a>
HTML;

        } else {

            $text = <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>
HTML;

        }

        // ---------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------

        if ($hasInActionLock) {
            return $text . $generalIdOwnerHtml;
        }

        $listingProductId = (int)$row->getData('id');

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $variationChildStatuses = $row->getData('variation_child_statuses');

        if ($variationManager->isVariationParent() && !empty($variationChildStatuses)) {
            $variationChildStatuses = json_decode($variationChildStatuses, true);
            unset($variationChildStatuses[Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED]);

            foreach ($variationChildStatuses as $variationChildStatus) {
                if (!empty($variationChildStatus)) {
                    return $text . $generalIdOwnerHtml;
                }
            }
        }

        $tip = Mage::helper('M2ePro')->__('Unassign ASIN/ISBN');
        $iconSrc = $iconPath.'unassign.png';

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$listingProductId});"
    title="{$tip}">
    <img src="{$iconSrc}" width="16" height="16"/>
</a>{$generalIdOwnerHtml}
HTML;

        return $text;
    }

    private function getGeneralIdColumnValueGeneralIdOwner($row)
    {
        $text = Mage::helper('M2ePro')->__('New ASIN/ISBN');

        // ---------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------

        if ($hasInActionLock) {
            return $text;
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');

        $tip = Mage::helper('M2ePro')->__('Unassign ASIN/ISBN');
        $iconSrc = $iconPath.'unassign.png';

        $lpId = $row->getData('id');

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$lpId});"
    title="{$tip}"><img src="{$iconSrc}" width="16" height="16"/>
</a>
HTML;
        return $text;
    }

    // ####################################

    protected function getTemplateDescriptionLinkHtml($listingProduct)
    {
        $templateDescriptionEditUrl = $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array(
            'id' => $listingProduct->getChildObject()->getTemplateDescriptionId()
        ));

        $helper = Mage::helper('M2ePro');
        $templateTitle = $listingProduct->getChildObject()->getDescriptionTemplate()->getTitle();

        return <<<HTML
<span style="font-size: 9px;">{$helper->__('Description Policy')}:&nbsp;
    <a target="_blank" href="{$templateDescriptionEditUrl}">
        {$helper->escapeHtml($templateTitle)}</a>
</span>
<br/>
HTML;
    }
}