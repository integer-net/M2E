<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_Manage_Tabs_Variations_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $childListingProducts = null;
    protected $currentProductVariations = null;
    protected $usedProductVariations = null;

    protected $listingProductId;
    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    //------------------------------

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct;

    protected function getListingProduct()
    {
        if(empty($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //------------------------------

    private $lockedDataCache = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonVariationProductManageGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection
        //----------------------------
        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->distinct();
        $collection->getSelect()->where("`second_table`.`variation_parent_id` = ?",(int)$this->getListingProductId());
        //----------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
        $parentType = $this->getListingProduct()->getChildObject()->getVariationManager()->getTypeModel();

        $channelAttributesSets = $parentType->getChannelAttributesSets();

        if ($parentType->hasMatchedAttributes()) {
            $channelAttributes = array_values($parentType->getMatchedAttributes());
        } else if (!empty($channelAttributesSets)) {
            $channelAttributes = array_keys($channelAttributesSets);
        } else {
            $channelAttributes = array();
        }

        $this->addColumn('product_options', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Variation'),
            'align'     => 'left',
            'width' => '210px',
            'sortable' => false,
            'index'     => 'additional_data',
            'filter_index' => 'additional_data',
            'frame_callback' => array($this, 'callbackColumnProductOptions'),
            'filter'   => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
            'options' => $parentType->getProductAttributes(),
            'filter_condition_callback' => array($this, 'callbackProductOptions')
        ));

        $this->addColumn('channel_options', array(
            'header'    => Mage::helper('M2ePro')->__('Amazon Variation'),
            'align'     => 'left',
            'width' => '210px',
            'sortable' => false,
            'index'     => 'additional_data',
            'filter_index' => 'additional_data',
            'frame_callback' => array($this, 'callbackColumnChannelOptions'),
            'filter'   => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
            'options' => $channelAttributes,
            'filter_condition_callback' => array($this, 'callbackChannelOptions')
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'sku',
            'frame_callback' => array($this, 'callbackColumnAmazonSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '100px',
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

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
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
        $this->getMassactionBlock()->addItem('list', array(
            'label'    => Mage::helper('M2ePro')->__('List Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('deleteAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Remove from Channel & Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnProductOptions($additionalData, $row, $column, $isExport)
    {
        $html = '';

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $row->getChildObject()->getVariationManager()->getTypeModel();

        $html .= '<div class="product-options-main" style="font-size: 11px; color: grey; margin-left: 7px">';
        $productOptions = $typeModel->getProductOptions();
        if (!empty($productOptions)) {
            $html .= '<div class="product-options-list">';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $html .= '<span class="attribute-row"><span class="attribute"><strong>' .
                    Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong></span>:&nbsp;<span class="value">' . Mage::helper('M2ePro')->escapeHtml($option) .
                    '</span></span><br/>';
            }
            $html .= '</div>';
        }

        if ($this->canChangeProductVariation($row)) {

            $listingProductId = $row->getId();
            $attributes = $this->getListingProduct()->getChildObject()
                ->getVariationManager()->getTypeModel()->getProductAttributes();
            $variationsTree = $this->getProductVariationsTree($row);

            sort($attributes);

            $linkTitle = Mage::helper('M2ePro')->__('Change Variation');
            $linkContent = Mage::helper('M2ePro')->__('Change Variation');

            $attributes = Mage::helper('M2ePro')->escapeHtml(json_encode($attributes));
            $variationsTree = Mage::helper('M2ePro')->escapeHtml(json_encode($variationsTree));

            $html .= <<<HTML
<form action="javascript:void(0);" class="product-options-edit"></form>
<a href="javascript:"
    onclick="ListingGridHandlerObj.editProductOptions(this, {$attributes}, {$variationsTree}, {$listingProductId})"
    title="{$linkTitle}">{$linkContent}</a>
HTML;
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnChannelOptions($additionalData, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $row->getChildObject();

        $typeModel = $amazonListingProduct->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $typeModel->getParentListingProduct()->getChildObject();

        $matchedAttributes = $parentAmazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getMatchedAttributes();

        if (!$typeModel->isVariationChannelMatched()) {
            if (!$typeModel->isVariationProductMatched() || !$amazonListingProduct->isGeneralIdOwner()) {
                return '';
            }

            if (empty($matchedAttributes)) {
                return '';
            }

            $options = array();

            foreach ($typeModel->getProductOptions() as $attribute => $value) {
                $options[$matchedAttributes[$attribute]] = $value;
            }
        } else {
            $options = $typeModel->getChannelOptions();

            if (!empty($matchedAttributes)) {

                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
                    $sortedOptions[$amazonAttr] = $options[$amazonAttr];
                }

                $options = $sortedOptions;
            }
        }

        if (empty($options)) {
            return '';
        }

        $generalId = $amazonListingProduct->getGeneralId();

        $html = '<div style="font-size: 11px; color: grey; margin-left: 7px">';

        foreach ($options as $attribute => $option) {
            !$option && $option = '--';

            $attrName = Mage::helper('M2ePro')->escapeHtml($attribute);
            $optionName = Mage::helper('M2ePro')->escapeHtml($option);

            if (empty($generalId) && $amazonListingProduct->isGeneralIdOwner()) {
                $html .= <<<HTML
{$attrName}:&nbsp;{$optionName}<br/>
HTML;
            } else {
                $html .= <<<HTML
<b>{$attrName}</b>:&nbsp;{$optionName}<br/>
HTML;
            }

        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        if ($row->getData('defected_messages')) {
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
        if (is_null($generalId) || $generalId === '') {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $this->getListingProduct()->getChildObject();
            if ($amazonListingProduct->isGeneralIdOwner()) {
                return Mage::helper('M2ePro')->__('New ASIN/ISBN');
            }

            return Mage::helper('M2ePro')->__('N/A');
        }
        return $this->getGeneralIdLink($generalId);
    }

    public function callbackColumnAvailableQty($qty, $row, $column, $isExport)
    {
        if (is_null($qty) || $qty === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }
        return $qty;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
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

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $listingProductId = (int)$row->getData('id');

        $html = $this->getViewLogIconHtml($row);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);

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

        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html .= '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $html .= '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html .= '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[Listing...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relisting...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revising...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stopping...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stopping...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Removing...]</span>';
                    break;

                default:
                    break;

            }
        }

        return $html;
    }

    public function callbackProductOptions($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if(is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"variation_product_options":[^}]*'.$value['attr'].'":"' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value'])))
                );
            }
        }
    }

    public function callbackChannelOptions($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if(is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"variation_channel_options":[^}]*'.$value['attr'].'":"' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value'])))
                );
            }
        }
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
            '(online_price >= \''.$from.'\' AND online_price <= \''.$to.'\' AND
            (
                online_sale_price IS NULL OR
                online_sale_price_start_date > NOW() OR
                online_sale_price_end_date < NOW()
            )) OR (online_sale_price >= \''.$from.'\' AND online_sale_price <= \''.$to.'\' AND
            (
                online_sale_price IS NOT NULL AND
                online_sale_price_start_date < NOW() AND
                online_sale_price_end_date > NOW()
            ))'
        );
    }

    // ####################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return string
     */
    public function getViewLogIconHtml($listingProduct)
    {
        $listingProductId = (int)$listingProduct->getId();

        // Get last messages
        //--------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_product_id` = ?', $listingProductId)
            ->where('`action_id` IS NOT NULL')
            ->order(array('id DESC'))
            ->limit(30);

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
            'entity_id' => (int)$listingProduct->getId(),
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

    // ####################################

    public function getMainButtonsHtml()
    {
        $html = '';
        if($this->getFilterVisibility()){
            $html.= $this->getAddNewChildButtonsHtml();
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }
        return $html;
    }

    public function getMassactionBlockHtml()
    {
        if ($this->isNewChildAllowed()) {
            $blockName = 'adminhtml_common_amazon_listing_variation_product_manage_tabs_variations_child_form';
            $form = $this->getLayout()->createBlock('M2ePro/'.$blockName);
            $form->setListingProductId($this->getListingProductId());

            return $form->toHtml() . parent::getMassactionBlockHtml();
        }

        return parent::getMassactionBlockHtml();
    }

    private function getAddNewChildButtonsHtml()
    {
        if ($this->isNewChildAllowed()) {

            //------------------------------
            $data = array(
                'label'   => Mage::helper('adminhtml')->__('Add New Child Product'),
                'onclick' => 'ListingGridHandlerObj.showNewChildForm('. !$this->hasUnusedChannelVariations() .')',
                'class'   => 'task',
                'id'      => 'add_new_child_button'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('add_new_child_button', $buttonBlock);
            //------------------------------

        }

        return $this->getChildHtml('add_new_child_button');
    }

    protected function isNewChildAllowed()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getListingProduct()->getChildObject();

        if (!$amazonListingProduct->getGeneralId()) {
            return false;
        }

        if (!$amazonListingProduct->getVariationManager()->getTypeModel()->hasMatchedAttributes()) {
            return false;
        }

        if (!$this->hasUnusedProductVariation()) {
            return false;
        }

        if ($this->hasChildWithEmptyProductOptions()) {
            return false;
        }

        if (!$this->isGeneralIdOwner() && !$this->hasUnusedChannelVariations()) {
            return false;
        }

        if (!$this->isGeneralIdOwner() && $this->hasChildWithEmptyChannelOptions()) {
            return false;
        }

        return true;
    }

    public function isGeneralIdOwner()
    {
        return $this->getListingProduct()->getChildObject()->isGeneralIdOwner();
    }

    public function getCurrentChannelVariations()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChannelVariations();
    }

    public function hasUnusedProductVariation()
    {
        return count($this->getChildListingProducts()) < count($this->getCurrentProductVariations());
    }

    public function hasUnusedChannelVariations()
    {
        return count($this->getUsedChannelVariations()) < count($this->getCurrentChannelVariations());
    }

    public function hasChildWithEmptyProductOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return true;
            }
        }

        return false;
    }

    public function hasChildWithEmptyChannelOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                return true;
            }
        }

        return false;
    }

    public function getUsedChannelVariations()
    {
        $usedOptions = array();

        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                continue;
            }

            $usedOptions[] = $childTypeModel->getChannelOptions();
        }

        return $usedOptions;
    }

    // ------------------------------------

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_listing_variation_product_manage/viewVariationsGridAjax', array(
            '_current' => true
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function _toHtml()
    {
        $listingId = $this->getListingProduct()->getListingId();

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        //------------------------------
        $urls = array();

        $path = 'adminhtml_common_log/listingProduct';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'channel' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'back'=>$helper->makeBackUrlParam('*/adminhtml_common_amazon_listing/view',array('id' => $listingId))
        ));

        $urls['adminhtml_common_amazon_listing_variation_product_manage/createNewChild'] = $this->getUrl(
            '*/adminhtml_common_amazon_listing_variation_product_manage/createNewChild');

        $urls = json_encode($urls);
        //------------------------------

        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $logViewUrl = $this->getUrl('*/adminhtml_common_log/listing', array(
            'id' =>$listingId,
            'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_AMAZON,
            'back'=>$helper->makeBackUrlParam('*/adminhtml_common_amazon_listing/view', array('id' => $listingId))
        ));

        $checkLockListing = $this->getUrl('*/adminhtml_listing/checkLockListing', array('component' => $component));
        $lockListingNow = $this->getUrl('*/adminhtml_listing/lockListingNow', array('component' => $component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listing/unlockListingNow', array('component' => $component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runStopAndRemoveProducts');
        $runDeleteAndRemoveProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runDeleteAndRemoveProducts');

        $setChildListingProductOptions = $this->getUrl(
            '*/adminhtml_common_amazon_listing_variation_product_manage/setChildListingProductOptions');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task has successfully submitted to be processed.')
        );
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__(
            '"%task_title%" Task has completed with warnings.
            <a target="_blank" href="%url%">View Log</a> for details.'
        ));
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__(
            '"%task_title%" Task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.'
        ));

        $lockedObjNoticeMessage = $helper->escapeJs($helper->__('Some Amazon request(s) are being processed now.'));
        $sendingDataToAmazonMessage = $helper->escapeJs($helper->__(
                'Sending %product_title% Product(s) data on Amazon.'
            ));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing All Items On Amazon'));
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing Selected Items On Amazon'));
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Revising Selected Items On Amazon'));
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Amazon'));
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Amazon'));
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
                ->__('Stopping On Amazon And Removing From Listing Selected Items'));
        $deletingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
                ->__('Removing From Amazon And Listing Selected Items'));

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select Items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $errorChangingProductOptions = $helper->escapeJs($helper->__('Please Select Product Options.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));
        $confirmWord = $helper->escapeJs($helper->__('Confirm'));
        $cancelWord = $helper->escapeJs($helper->__('Cancel'));

        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

        $mapToTemplateDescription = $this->getUrl('*/adminhtml_common_amazon_listing/mapToTemplateDescription');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_common_amazon_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateDescriptionAssign = $this->getUrl(
            '*/adminhtml_common_amazon_listing/validateProductsForTemplateDescriptionAssign');
        $viewTemplateDescriptionsGrid = $this->getUrl('*/adminhtml_common_amazon_listing/viewTemplateDescriptionsGrid');
        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy for Products'));

        $generalId = $this->getListingProduct()->getGeneralId();
        $hideMassAction = empty($generalId) ?
            '$(\'amazonVariationProductManageGrid_massaction-form\').hide();' : '';

        $javascriptMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.url.add({$urls});

    M2ePro.url.logViewUrl = '{$logViewUrl}';

    M2ePro.url.checkLockListing = '{$checkLockListing}';
    M2ePro.url.lockListingNow = '{$lockListingNow}';
    M2ePro.url.unlockListingNow = '{$unlockListingNow}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';
    M2ePro.url.runDeleteAndRemoveProducts = '{$runDeleteAndRemoveProducts}';
    M2ePro.url.setChildListingProductOptions = '{$setChildListingProductOptions}';

    M2ePro.url.mapToTemplateDescription = '{$mapToTemplateDescription}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateDescriptionAssign = '{$validateProductsForTemplateDescriptionAssign}';
    M2ePro.url.viewTemplateDescriptionsGrid = '{$viewTemplateDescriptionsGrid}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToAmazonMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.deleting_and_removing_selected_items_message = '{$deletingAndRemovingSelectedItemsMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.error_changing_product_options = '{$errorChangingProductOptions}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';
    M2ePro.text.confirm_word = '{$confirmWord}';
    M2ePro.text.cancel_word = '{$cancelWord}';

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';

    M2ePro.text.confirm = '{$textConfirm}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = 'amazonVariationProductManageGrid';

    // fix for tool tip position in iframe
    MagentoFieldTip.prototype.changeToolTipPosition = function(element)
    {
        var toolTip = element.up().select('.tool-tip-message')[0];

        var settings = {
            setHeight: false,
            setWidth: false,
            setLeft: true,
            offsetTop: 25,
            offsetLeft: -20
        };

        if (element.up().getStyle('float') == 'right') {
            settings.offsetLeft += 18;
        }
        if (element.up().match('span')) {
            settings.offsetLeft += 15;
        }

        toolTip.clonePosition(element, settings);

        if (toolTip.hasClassName('tip-left')) {
            toolTip.style.left = (parseInt(toolTip.style.left) - toolTip.getWidth() - 10) + 'px';
        }
    };

    Event.observe(window, 'load', function() {

        CommonHandler.prototype.scroll_page_to_top = function() { return; }

        ListingGridHandlerObj = new AmazonListingVariationProductManageVariationsGridHandler(
            'amazonVariationProductManageGrid',
            {$listingId}
        );

        // todo next (temp solution)
        ListingGridHandlerObj.actionHandler.setOptions(M2ePro);
        ListingGridHandlerObj.templateDescriptionHandler.setOptions(M2ePro);

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        setTimeout(function() {
            ListingGridHandlerObj.afterInitPage();
        }, 350);
    });

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

    {$hideMassAction}

</script>
JAVASCRIPT;

        $additionalCss = <<<CSS
<style>
    body {
        background: none;
    }

    .wrapper {
        min-width: inherit;
    }

    .footer {
        display: none;
    }

    .middle {
        padding: 0px;
        background: none;
    }

    td.help_line .hl_close {
        margin-top: -6px;
    }

    td.help_line .hl_header {
        padding: 0 0 10px !important;
    }

</style>
CSS;

        return  '<div id="messages"></div>' .
                '<div id="listing_view_progress_bar"></div>' .
                '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
                '<div id="listing_view_content_container">'.
                    parent::_toHtml() .
                '</div>' . $javascriptMain . $additionalCss;
    }

    // ####################################

    private function canChangeProductVariation(Ess_M2ePro_Model_Listing_Product $childListingProduct)
    {
        if (!$this->hasUnusedProductVariation()) {
            return false;
        }

        $lockData = $this->getLockedData($childListingProduct);
        if ($lockData['in_action']) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonChildListingProduct */
        $amazonChildListingProduct = $childListingProduct->getChildObject();

        if (!$amazonChildListingProduct->getGeneralId()) {
            return false;
        }

        $childTypeModel = $amazonChildListingProduct->getVariationManager()->getTypeModel();

        if ($childTypeModel->isVariationProductMatched() && $this->hasChildWithEmptyProductOptions()) {
            return false;
        }

        return true;
    }

    private function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load($row->getData('id'))->getObjectLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action' => !empty($objectLocks),
            );
            $this->lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->lockedDataCache[$listingProductId];
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
<span style="font-size: 9px;">{$helper->__('Description Title')}:&nbsp;
    <a target="_blank" href="{$templateDescriptionEditUrl}">
        {$helper->escapeHtml($templateTitle)}</a>
</span>
<br/>
HTML;
    }

    // ####################################

    public function getProductVariationsTree($childProduct)
    {
        $unusedVariations = $this->getUnusedProductVariations();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $childProduct->getChildObject()->getVariationManager()->getTypeModel();

        if ($childTypeModel->isVariationProductMatched()) {
            $unusedVariations[] = $childTypeModel->getProductOptions();
        }

        $variationsSets = $this->getAttributesVariationsSets($unusedVariations);
        $firstAttribute = key($variationsSets);

        return $this->prepareVariations($firstAttribute,$unusedVariations,$variationsSets);
    }

    private function prepareVariations($currentAttribute,$unusedVariations,$variationsSets,$filters = array())
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
                    $nextAttribute,$unusedVariations,$variationsSets,$filters
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
        foreach ($unusedVariations as $key => $magentoVariation) {
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
                    unset($unusedVariations[$key]);
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

        if (count($unusedVariations) < 1) {
            return false;
        }

        ksort($return[$currentAttribute]);

        return $return;
    }

    // ####################################

    public function getCurrentProductVariations()
    {

        if (!is_null($this->currentProductVariations)) {
            return $this->currentProductVariations;
        }

        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $productVariations = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productVariations[] = $productOption;
        }

        return $this->currentProductVariations = $productVariations;
    }

    public function getUsedProductVariations()
    {
        if (is_null($this->usedProductVariations)) {

            $usedOptions = array();

            foreach ($this->getChildListingProducts() as $childListingProduct) {
                /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

                /**
                 * @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel
                 */
                $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

                if (!$childTypeModel->isVariationProductMatched()) {
                    continue;
                }

                $usedOptions[] = $childTypeModel->getProductOptions();
            }

            $this->usedProductVariations = $usedOptions;
        }

        return $this->usedProductVariations;
    }

    // ####################################

    public function getUnusedProductVariations()
    {
        return $this->getUnusedVariations($this->getCurrentProductVariations(), $this->getUsedProductVariations());
    }

    private function getUnusedVariations($currentVariations, $usedVariations)
    {
        if (empty($currentVariations)) {
            return array();
        }

        if (empty($usedVariations)) {
            return $currentVariations;
        }

        $unusedOptions = array();

        foreach ($currentVariations as $id => $currentOption) {
            if ($this->isVariationExistsInArray($currentOption, $usedVariations)) {
                continue;
            }

            $unusedOptions[$id] = $currentOption;
        }

        return $unusedOptions;
    }

    private function isVariationExistsInArray(array $needle, array $haystack)
    {
        foreach ($haystack as $option) {
            if ($option != $needle) {
                continue;
            }

            return true;
        }

        return false;
    }

    // ####################################

    public function getChildListingProducts()
    {
        if (!is_null($this->childListingProducts)) {
            return $this->childListingProducts;
        }

        return $this->childListingProducts = $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getAttributesVariationsSets($variations)
    {
        $attributesOptions = array();

        foreach ($variations as $variation) {
            foreach ($variation as $attr => $option) {
                if (!isset($attributesOptions[$attr])) {
                    $attributesOptions[$attr] = array();
                }
                if (!in_array($option, $attributesOptions[$attr])) {
                    $attributesOptions[$attr][] = $option;
                }
            }
        }

        ksort($attributesOptions);

        return $attributesOptions;
    }

    // ####################################

    protected function getGeneralIdLink($generalId)
    {
        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $generalId,
            $this->getListingProduct()->getListing()->getMarketplaceId()
        );

        return <<<HTML
<a href="{$url}" target="_blank" title="{$generalId}" >{$generalId}</a>
HTML;
    }

    // ####################################
}