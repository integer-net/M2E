<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Ebay_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $sellingFormatTemplate Ess_M2ePro_Model_Ebay_Template_SellingFormat */
    private $sellingFormatTemplate = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewGridEbay'.$listing->getId());
        //------------------------------

        $this->sellingFormatTemplate = $listing->getChildObject()->getSellingFormatTemplate();
        $this->showAdvancedFilterProductsOption = false;
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    // ####################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    // ####################################

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex.' '.strtoupper($column->getDir()));
        }
        return $this;
    }

    // ####################################

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        //--------------------------------
        // Get collection
        //----------------------------
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        //--------------------------------

        // Join listing product tables
        //----------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'status' => 'status',
                'component_mode' => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$listingData['id']
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'    => 'listing_product_id',
                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(online_qty - online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_category'       => 'online_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_buyitnow_price' => 'online_buyitnow_price',
                'template_category_id'  => 'template_category_id'
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            NULL,
            'left'
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
            'frame_callback' => array($this, 'callbackColumnProductId'),
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / SKU / eBay Category'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'online_title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('ebay_item_id', array(
            'header'    => Mage::helper('M2ePro')->__('Item ID'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'text',
            'index'     => 'item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('available_qty', array(
            'header'    => Mage::helper('M2ePro')->__('Available QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'available_qty',
            'sortable'  => (bool)version_compare(Mage::helper('M2ePro/Magento')->getVersion(), '1.4.2'),
            'filter'    => false,
            'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty')
        ));

        $this->addColumn('online_qty_sold', array(
            'header'    => Mage::helper('M2ePro')->__('Sold QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
        ));

        $this->addColumn('online_buyitnow_price', array(
            'header'    => Mage::helper('M2ePro')->__('"Buy It Now" Price'),
            'align'     =>'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_buyitnow_price',
            'frame_callback' => array($this, 'callbackColumnOnlineBuyItNowPrice')
        ));

        $this->addColumn('end_date', array(
            'header'    => Mage::helper('M2ePro')->__('End Date'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'end_date',
            'frame_callback' => array($this, 'callbackColumnEndTime')
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('M2ePro')->__('Status'),
                'width' => '100px',
                'index' => 'status',
                'filter_index' => 'status',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN    => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_SOLD       => Mage::helper('M2ePro')->__('Sold'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Stopped'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED   => Mage::helper('M2ePro')->__('Finished'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            ));

        // todo adviser
//        $this->addColumn('adviser', array(
//            'header'    => Mage::helper('M2ePro')->__('Adviser'),
//            'align'     => 'right',
//            'width'     => '150px',
//            'type'      => 'text',
//            'index'     => 'adviser',
//            'sortable'  => false,
//            'filter'    => false,
////            'frame_callback' => array($this, 'callbackColumnAdviser')
//        ));

        if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
            $this->addColumn('developer_action', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'left',
                'width'     => '150px',
                'type'      => 'text',
                'renderer'  => 'M2ePro/adminhtml_listing_view_grid_column_renderer_developerAction',
                'index'     => 'value',
                'filter'    => false,
                'sortable'  => false,
                'js_handler' => 'EbayListingEbayGridHandlerObj'
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Set mass-action
        //--------------------------------
        $data = array(
            'label'    => Mage::helper('M2ePro')->__('List Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?'),
        );

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
            $collection->addFieldToFilter('status',array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED));
            $collection->getSize() == 0 && $data['selected'] = true;
        }

        $this->getMassactionBlock()->addItem('list', $data);

        $this->getMassactionBlock()->addItem('revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on eBay / Remove From Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            $this->getMassactionBlock()->addItem('editCategorySettings', array(
                'label'    => Mage::helper('M2ePro')->__('Edit eBay Categories Settings'),
                'url'      => '',
            ));
        }
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = $row->getName();

        $onlineTitle = $row->getData('online_title');
        !empty($onlineTitle) && $value = $onlineTitle;

        if (strlen($value) > 60) {
            $value = substr($value, 0, 60) . '...';
        }

        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        if (is_null($sku = $row->getData('sku'))) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $value .= '<br/><strong>'
            .Mage::helper('M2ePro')->__('SKU')
            .':</strong> '
            .Mage::helper('M2ePro')->escapeHtml($sku);

        if ($category = $row->getData('online_category')) {
            $value .= '<br><br><strong>'.
                Mage::helper('M2ePro')->__('Category').
                ':</strong> '.
                Mage::helper('M2ePro')->escapeHtml($category);
        }

        $value .= '<br /><strong>' . Mage::helper('M2ePro')->__('eBay Fee') . ':</strong>&nbsp;';
        $value .= $this->getItemFeeHtml($row);

        return $value;
    }

    private function getItemFeeHtml($row)
    {
        /* @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ||
            $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            $additionalData = (array)json_decode($row->getData('additional_data'), true);

            if (empty($additionalData['ebay_item_fees']['listing_fee']['fee'])) {
                return Mage::getSingleton('M2ePro/Currency')->formatPrice(
                    $listing->getMarketplace()->getChildObject()->getCurrency(),
                    0
                );
            }

            $fee = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_product');
            $fee->setData('fees', $additionalData['ebay_item_fees']);
            $fee->setData('product_name', $row->getData('name'));

            return $fee->toHtml();
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        $label = Mage::helper('M2ePro')->__('estimate');

        return <<<HTML
[<a href="javascript:void(0);"
    onclick="EbayListingEbayGridHandlerObj.getEstimatedFees({$listingProductId});">{$label}</a>]
HTML;

    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/gotoEbay/',
            array(
                'item_id' => $value,
                'account_id' => $listingData['account_id'],
                'marketplace_id' => $listingData['marketplace_id']
            )
        );

        return '<a href="' . $url . '" target="_blank">'.$value.'</a>';
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

    public function callbackColumnOnlineStartPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        /* @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        return Mage::app()->getLocale()
            ->currency($listing->getMarketplace()->getChildObject()->getCurrency())
            ->toCurrency($value);
    }

    public function callbackColumnOnlineReservePrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        /* @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        return Mage::app()->getLocale()
            ->currency($listing->getMarketplace()->getChildObject()->getCurrency())
            ->toCurrency($value);
    }

    public function callbackColumnOnlineBuyItNowPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        /* @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        return Mage::app()->getLocale()
            ->currency($listing->getMarketplace()->getChildObject()->getCurrency())
            ->toCurrency($value);
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

        return $value.$this->getViewLogIconHtml($row->getData('listing_product_id'));
    }

    public function callbackColumnEndTime($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
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
                array('attribute'=>'online_sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%'),
                array('attribute'=>'online_title','like'=>'%'.$value.'%'),
                array('attribute'=>'online_category', 'like'=>'%'.$value.'%')
            )
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

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last action was completed with warning(s).'
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
            'view_help_handler' => 'EbayListingEbayGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'EbayListingEbayGridHandlerObj.hideItemHelp',
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
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL:
                $string = Mage::helper('M2ePro')->__('Status Change');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Translation');
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
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascriptsMain = <<<HTML

<script type="text/javascript">
    EbayListingEbayGridHandlerObj.afterInitPage();
    EbayListingEbayGridHandlerObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');
</script>

HTML;
            return parent::_toHtml().$javascriptsMain;
        }

        // todo next (change)

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        // static routes
        $urls = array(
            'adminhtml_ebay_log/listing' => $this->getUrl(
                '*/adminhtml_ebay_log/listing', array(
                    'id' =>$listingData['id']
                )
            )
        );

        $path = 'adminhtml_ebay_listing/getEstimatedFees';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $listingData['id']
        ));

        $path = 'adminhtml_ebay_listing/getCategoryChooserHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $listingData['id']
        ));

        $path = 'adminhtml_ebay_listing/getCategorySpecificHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $listingData['id']
        ));

        $path = 'adminhtml_ebay_listing/saveCategoryTemplate';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $listingData['id']
        ));

        $urls = json_encode($urls);

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list',true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $component . 'ListingViewGrid' . $listingData['id'];
        $ignoreListings = json_encode(array($listingData['id']));

        $logViewUrl = $this->getUrl('*/adminhtml_ebay_log/listing',array(
            'id'=>$listingData['id'],
            'back'=>$helper->makeBackUrlParam('*/adminhtml_ebay_listing/view',array('id'=>$listingData['id']))
        ));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_ebay_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_ebay_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_ebay_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopAndRemoveProducts');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs($helper->__(
            '"%task_title%" task has successfully completed.'
        ));

        // M2ePro_TRANSLATIONS
        // %task_title%" task has completed with warnings. <a target="_blank" href="%url%">View log</a> for details.
        $tempString = '"%task_title%" task has completed with warnings. ';
        $tempString .= '<a target="_blank" href="%url%">View log</a> for details.';
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__($tempString));

        // M2ePro_TRANSLATIONS
        // "%task_title%" task has completed with errors. <a target="_blank" href="%url%">View log</a> for details.
        $tempString = '"%task_title%" task has completed with errors. ';
        $tempString .= '<a target="_blank" href="%url%">View log</a> for details.';
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__($tempString));

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %product_title% product(s) data on eBay.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('The listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing is empty.')
        );
        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing All Items On eBay')
        );
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing Selected Items On eBay')
        );
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Revising Selected Items On eBay')
        );
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Relisting Selected Items On eBay')
        );
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping Selected Items On eBay')
        );
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping On eBay And Removing From Listing Selected Items')
        );

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the products you want to perform the action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $prepareData = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_ebay_listing_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Product(s) was not moved. <a target="_blank" href="%url%">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some product(s) was not moved. <a target="_blank" href="%url%">View log</a> for details.')
        );

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $translations = json_encode(array(
            'eBay Categories' => Mage::helper('M2ePro')->__('eBay Categories'),
            'Specifics' => Mage::helper('M2ePro')->__('Specifics'),
            'Estimated Fee Details' => Mage::helper('M2ePro')->__('Estimated Fee Details'),
        ));

        $isSimpleViewMode = json_encode(Mage::helper('M2ePro/View_Ebay')->isSimpleMode());
        $showAutoAction   = json_encode((bool)$this->getRequest()->getParam('auto_actions'));

        $showMotorNotification= json_encode((bool)$this->isShowMotorNotification());

        // M2ePro_TRANSLATIONS
        // Please check eBay Motors compatibility attribute.You can find it in %menu_label% > Configuration > <a target="_blank" href="%url%">General</a>.
        $motorNotification = $helper->escapeJs($helper->__(
            'Please check eBay Motors compatibility attribute.'.
            'You can find it in %menu_label% > Configuration > <a target="_blank" href="%url%">General</a>.',
            Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
            $this->getUrl('*/adminhtml_ebay_configuration')
        ));

        $javascriptsMain = <<<HTML

<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2ePro.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.text.popup_title = '{$popupTitle}';
    M2ePro.text.failed_products_popup_title = '{$failedProductsPopupTitle}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.sending_data_message = '{$sendingDataToEbayMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';

    M2ePro.text.successfully_moved = '{$successfullyMovedMessage}';
    M2ePro.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2ePro.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {

        EbayListingEbayGridHandlerObj = new EbayListingEbayGridHandler(
            '{$this->getId()}',
            {$listingData['id']}
        );
        EbayListingEbayGridHandlerObj.afterInitPage();
        EbayListingEbayGridHandlerObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');

        EbayListingEbayGridHandlerObj.actionHandler.setOptions(M2ePro);

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        if (M2ePro.productsIdsForList) {
            EbayListingEbayGridHandlerObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            EbayListingEbayGridHandlerObj.actionHandler.listAction();
        }

        if (!{$isSimpleViewMode} && {$showAutoAction}) {
            EbayListingAutoActionHandlerObj.loadAutoActionHtml();
        }

        if ({$showMotorNotification}) {
            EbayListingEbayGridHandlerObj.showMotorsNotificationPopUp('{$motorNotification}');
        }

    });

</script>

HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    // ####################################

    private function getGridIdsJson()
    {
        $select = clone $this->getCollection()->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->resetJoinLeft();

        $select->columns('elp.listing_product_id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        return implode(',',$connRead->fetchCol($select));
    }

    // ####################################

    protected function isShowMotorNotification()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if ($listing->getMarketplaceId() != Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            return false;
        }

        $configValue = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/motors_specifics_attribute/', 'listing_notification_shown'
        );

        if ($configValue) {
            return false;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/motors_specifics_attribute/', 'listing_notification_shown', 1
        );

        return true;
    }

    // ####################################
}