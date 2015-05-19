<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View_Sellercentral_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $sellingFormatTemplate Ess_M2ePro_Model_Amazon_Template_SellingFormat */
    private $sellingFormatTemplate = NULL;

    private $lockedDataCache = array();

    private $parentAsins;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('amazonListingViewSellercentralGrid'.$listingData['id']);
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
        $collection
            ->setListingProductModeOn()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->joinTable(
                array('cisi' => 'cataloginventory/stock_item'),
                'product_id=entity_id',
                array('qty' => 'qty',
                    'is_in_stock' => 'is_in_stock'),
                '{{table}}.stock_id=1',
                'left'
            );

        //----------------------------

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'amazon_status'   => 'status',
                'component_mode'  => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id'],
                'status' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED
                )
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'                    => 'general_id',
                'search_settings_status'        => 'search_settings_status',
                'amazon_sku'                    => 'sku',
                'online_qty'                    => 'online_qty',
                'online_price'                  => 'online_price',
                'online_sale_price'             => 'online_sale_price',
                'online_sale_price_start_date'  => 'online_sale_price_start_date',
                'online_sale_price_end_date'    => 'online_sale_price_end_date',
                'is_afn_channel'                => 'is_afn_channel',
                'is_general_id_owner'           => 'is_general_id_owner',
                'variation_child_statuses'      => 'variation_child_statuses',
                'is_variation_parent'           => 'is_variation_parent',
                'variation_parent_id'           => 'variation_parent_id'
            ),
            '{{table}}.is_variation_parent = 0'
        );
        //----------------------------
//        exit($collection->getSelect()->__toString());

        // Change default Magento message when items count is zero
        $collectionForCounting = clone $collection;

        if (count($collectionForCounting) === 0) {
            $this->setEmptyText(
                Mage::helper('M2ePro')->__(
                    'Only Simple and Child Products listed on Amazon will be shown in Seller Сentral View Mode.'
                )
            );
        }

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
            'width'     => '300px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
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
            'width' => '125px',
            'index' => 'amazon_status',
            'filter_index' => 'amazon_status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Inactive (Blocked)')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
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

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = (int)$listingData['store_id'];

        $withoutImageHtml = '<a href="'
            .$this->getUrl('adminhtml/catalog_product/edit',
                array('id' => $value))
            .'" target="_blank">'.$value.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/',
                'show_products_thumbnails');
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($value);
        $magentoProduct->setStoreId($storeId);

        $imageUrlResized = $magentoProduct->getThumbnailImageLink();
        if (is_null($imageUrlResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $value.'<hr style="border: 1px solid silver; border-bottom: none;"><img src="'.
            $imageUrlResized.'" />';
        $withImageHtml = str_replace('>'.$value.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');
        is_null($tempSku)
        && $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load($row->getData('id'));
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationProduct()) {
            return $value;
        }

        if ($variationManager->isRelationChildType()) {
            $typeModel = $variationManager->getTypeModel();

            $productOptions = $typeModel->getProductOptions();
            $channelOptions = $typeModel->getChannelOptions();

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
            $parentAmazonListingProduct = $typeModel->getParentListingProduct()->getChildObject();

            $matchedAttributes = $parentAmazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getMatchedAttributes();

            if (!empty($matchedAttributes)) {

                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
                    $sortedOptions[$amazonAttr] = $channelOptions[$amazonAttr];
                }

                $channelOptions = $sortedOptions;
            }

            $value .= '<div style="font-weight:bold;font-size: 11px;color: grey;margin-left: 7px;margin-top: 5px;">'.
                Mage::helper('M2ePro')->__('Magento Variation') . '</div>';
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 24px">';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '</br>';
            }
            $value .= '</div>';

            $value .= '<div style="font-weight:bold;font-size: 11px;color: grey;margin-left: 7px;margin-top: 5px;">'.
                Mage::helper('M2ePro')->__('Amazon Variation') . '</div>';
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 24px">';
            foreach ($channelOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '</br>';
            }
            $value .= '</div>';

            return $value;
        }

        $productOptions = array();
        if ($listingProduct->getChildObject()->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $productOptions = $listingProduct->getChildObject()->getVariationManager()
                ->getTypeModel()->getProductOptions();
        }

        $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
        foreach ($productOptions as $attribute => $option) {
            !$option && $option = '--';
            $value .= '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }
        $value .= '</div>';

        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $value,
            $marketplaceId
        );

        $parentAsinHtml = '';
        $variationParentId = $row->getData('variation_parent_id');
        if(!empty($variationParentId)){
            $parentAsinHtml = '<br><span style="display: block;
                                                margin-bottom: 5px;
                                                font-size: 10px;
                                                color: grey;">'.
                Mage::helper('M2ePro')->__('child ASIN/ISBN</br>of parent %parent_asin%',
                    $this->getParentAsin($row->getData('id'))) . '</span>';
        }

        $generalIdOwnerHtml = '';
        if($row->getData('is_general_id_owner') == 1){
            $generalIdOwnerHtml = '<span style="font-size: 10px; color: grey; display: block;">'.
                                   Mage::helper('M2ePro')->__('creator of ASIN/ISBN').
                                  '</span>';
        }
        return <<<HTML
<a href="{$url}" target="_blank">{$value}</a>{$parentAsinHtml}{$generalIdOwnerHtml}
HTML;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
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
            if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
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
            $startDateTimestamp = strtotime($row->getData('online_sale_price_start_date'));
            $endDateTimestamp   = strtotime($row->getData('online_sale_price_end_date'));

            $iconHelpPath = $this->getSkinUrl('M2ePro/images/help.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');

            $intervalHtml = '<img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'">
                            <span class="tool-tip-message tip-left" style="display:none;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray; font-size: 10px;">
                                    From: '.date('Y-m-d', $startDateTimestamp).'<br/>
                                    To: '.date('Y-m-d', $endDateTimestamp).'
                                </span>
                            </span>';

            $currentTimestamp = strtotime(Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d 00:00:00'));

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
        switch ($row->getData('amazon_status')) {

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

        $value .= $this->getViewLogIconHtml($row->getData('id'));

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

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

    //----------------------------------------

    public function getViewLogIconHtml($listingProductId)
    {
        $listingProductId = (int)$listingProductId;

        // Get last messages
        //--------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_product_id` = ?',$listingProductId)
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

    private function getParentAsin($childId)
    {
        if (is_null($this->parentAsins)) {
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableAmazonListingProduct = Mage::getSingleton('core/resource')
                ->getTableName('m2epro_amazon_listing_product');

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id','variation_parent_id'))
                ->where('listing_product_id IN (?)', $this->getCollection()->getAllIds())
                ->where('variation_parent_id IS NOT NULL');

            $parentIds = Mage::getResourceModel('core/config')
                ->getReadConnection()
                ->fetchPairs($select);

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id', 'general_id'))
                ->where('listing_product_id IN (?)', $parentIds);

            $parentAsins = Mage::getResourceModel('core/config')
                ->getReadConnection()
                ->fetchPairs($select);

            $this->parentAsins = array();
            foreach ($parentIds as $childId => $parentId) {
                $this->parentAsins[$childId] = $parentAsins[$parentId];
            }
        }

        return $this->parentAsins[$childId];
    }

    // ####################################
}