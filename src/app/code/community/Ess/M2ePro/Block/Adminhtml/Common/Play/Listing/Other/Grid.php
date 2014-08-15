<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Other_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        /** @var $this->connRead Varien_Db_Adapter_Pdo_Mysql */
        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Initialization block
        //------------------------------
        $this->setId('playListingOtherGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    // ####################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Other');

        $collection->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            'mp.id = main_table.marketplace_id',
            array('marketplace_title' => 'mp.title')
        );

        // Add Filter By Account
        $accountFilter = $this->getRequest()->getParam('playAccount');
        if (!empty($accountFilter)) {
            $collection->addFieldToFilter('account_id', $accountFilter);
        }

        //var_dump($collection->getSelect()->__toString()); exit();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('M2ePro')->__('Product ID'),
            'align'  => 'left',
            'width'  => '80px',
            'type'   => 'number',
            'index'  => 'product_id',
            'filter_index' => 'product_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('M2ePro')->__('Product Title / Reference Code'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'title',
            'filter_index'  => 'second_table.title',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('Identifier'),
            'align'  => 'left',
            'width'  => '100px',
            'type'   => 'text',
            'index'  => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('online_qty', array(
            'header' => Mage::helper('M2ePro')->__('Play.com QTY'),
            'align'  => 'right',
            'width'  => '100px',
            'type'   => 'number',
            'index'  => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty')
        ));

        $this->addColumn('online_price_gbr', array(
            'header' => Mage::helper('M2ePro')->__('Play.com GBP Price'),
            'align'  => 'right',
            'width'  => '100px',
            'type'   => 'number',
            'index'  => 'online_price_gbr',
            'filter_index' => 'online_price_gbr',
            'frame_callback' => array($this, 'callbackColumnPriceGbr')
        ));

        $this->addColumn('online_price_euro', array(
            'header' => Mage::helper('M2ePro')->__('Play.com EUR Price'),
            'align'  => 'right',
            'width'  => '100px',
            'type'   => 'number',
            'index'  => 'online_price_euro',
            'filter_index' => 'online_price_euro',
            'frame_callback' => array($this, 'callbackColumnPriceEuro')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width'  => '60px',
            'index'  => 'status',
            'type'   => 'options',
            'filter_index' => 'main_table.status',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_common_listing_other/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_PLAY
            )
        );

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('M2ePro')->__('View Log'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => '*/adminhtml_common_log/listingOther',
                        'params' => array(
                            'back' => $backUrl
                        )
                    )
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Clear Log'),
                    'confirm' => Mage::helper('M2ePro')->__('Are you sure?'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => '*/adminhtml_listing_other/clearLog',
                        'params' => array(
                            'back' => $backUrl
                        )
                    )
                )
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        //--------------------------------
        $this->setMassactionIdField('`main_table`.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('autoMapping', array(
            'label'   => Mage::helper('M2ePro')->__('Map Item(s) Automatically'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('moving', array(
            'label'   => Mage::helper('M2ePro')->__('Move Item(s) To Listing'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('removing', array(
            'label'   => Mage::helper('M2ePro')->__('Remove Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('unmapping', array(
            'label'   => Mage::helper('M2ePro')->__('Unmap Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $productTitle = $row->getData('title');

            if (is_null($productTitle) || $productTitle === '') {
                $productTitle = Mage::helper('M2ePro')->__('N/A');
            } else {
                $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);
                $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
                if (strlen($productTitle) > 60) {
                    $productTitle = substr($productTitle, 0, 60) . '...';
                }
            }

            $htmlValue = '&nbsp;<a href="javascript:void(0);"
                                   onclick="PlayListingOtherMappingHandlerObj.openPopUp(\''
                         .$productTitle
                         .'\','
                         .(int)$row->getId()
                         .');">'
                         .Mage::helper('M2ePro')->__('Map').'</a>';

            if (Mage::helper('M2ePro/Magento')->isDeveloper()) {
                $htmlValue .= '<br>' . $row->getId();
            }

            return $htmlValue;
        }

        $htmlValue = '&nbsp<a href="'
            .$this->getUrl('adminhtml/catalog_product/edit',
                array('id' => $row->getData('product_id')))
            .'" target="_blank">'
            .$row->getData('product_id')
            .'</a>';

        $htmlValue .= '&nbsp&nbsp&nbsp<a href="javascript:void(0);"'
            .' onclick="PlayListingOtherGridHandlerObj.movingHandler.getGridHtml('
            .json_encode(array((int)$row->getData('id')))
            .')">'
            .Mage::helper('M2ePro')->__('Move')
            .'</a>';

        if (Mage::helper('M2ePro/Magento')->isDeveloper()) {
            $htmlValue .= '<br>' . $row->getId();
        }

        return $htmlValue;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $value = Mage::helper('M2ePro')->escapeHtml($value);
        if (strlen($value) > 60) {
            $value = substr($value, 0, 60) . '...';
        }
        $value = '<span>' . $value . '</span>';

        $tempSku = $row->getData('sku');
        is_null($tempSku) && $tempSku = Mage::helper('M2ePro')->__('N/A');

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('Reference Code').':</strong> '
                  .Mage::helper('M2ePro')->escapeHtml($tempSku);

        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        $generalIdType = $row->getData('general_id_type');

        if (is_null($row->getData('link_info'))) {
             return '<strong>'.Mage::helper('M2ePro')->__($generalIdType).':</strong><p>'.$value.'</p>';
        }

        $linkInfo = json_decode($row->getData('link_info'),true);
        $url = Mage::helper('M2ePro/Component_Play')->getItemUrl($linkInfo['play_id'], $linkInfo['category_code']);

        return '<strong>'.Mage::helper('M2ePro')->__($generalIdType).': </strong>
                <a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPriceGbr($value, $row, $column, $isExport)
    {
        if ((float)$value <= 0) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return Mage::app()->getLocale()->currency('GBP')->toCurrency($value);
    }

    public function callbackColumnPriceEuro($value, $row, $column, $isExport)
    {
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
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value.$this->getViewLogIconHtml($row->getId());
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.title LIKE ? OR second_table.sku LIKE ?', '%'.$value.'%');
    }

    // ####################################

    public function getViewLogIconHtml($listingOtherId)
    {
        $listingOtherId = (int)$listingOtherId;

        // Get last messages
        //--------------------------
        $dbSelect = $this->connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_other_id` = ?', $listingOtherId)
            ->where('`action_id` IS NOT NULL')
            ->order(array('id DESC'))
            ->limit(30);

        $logRows = $this->connRead->fetchAll($dbSelect);
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
            'entity_id' => $listingOtherId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'PlayListingOtherGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'PlayListingOtherGridHandlerObj.hideItemHelp',
        ));

        return $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_STATUS_ON_CHANNEL:
                $string = Mage::helper('M2ePro')->__('Status Change');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['initiator']) {
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

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof PlayListingOtherGridHandlerObj != 'undefined') {
        PlayListingOtherGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            PlayListingOtherGridHandlerObj.afterInitPage();
        }, 350);
    });

</script>
JAVASCRIPT;

        return parent::_toHtml().$javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_play_listing_other/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}