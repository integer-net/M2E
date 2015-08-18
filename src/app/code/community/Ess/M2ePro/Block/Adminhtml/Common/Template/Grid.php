<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Common_Template_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const TEMPLATE_SELLING_FORMAT = 'selling_format';
    const TEMPLATE_SYNCHRONIZATION = 'synchronization';

    protected $nick;

    // ##########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonTemplateGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ##########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Prepare selling format collection
        // ----------------------------------
        $collectionSellingFormat = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collectionSellingFormat->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            array('id as template_id', 'title',
                  new Zend_Db_Expr('\''.self::TEMPLATE_SELLING_FORMAT.'\' as `type`'),
                  'create_date', 'update_date')
        );
        $collectionSellingFormat->getSelect()->where('component_mode = (?)', $this->nick);
        // ----------------------------------

        // Prepare synchronization collection
        // ----------------------------------
        $collectionSynchronization = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collectionSynchronization->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            array('id as template_id', 'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SYNCHRONIZATION.'\' as `type`'),
                'create_date', 'update_date')
        );
        $collectionSynchronization->getSelect()->where('component_mode = (?)', $this->nick);
        // ----------------------------------

        // Prepare union select
        // ----------------------------------
        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect()
        ));
        // ----------------------------------

        // Prepare result collection
        // ----------------------------------
        $resultCollection = new Varien_Data_Collection_Db($connRead);
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array('template_id', 'title', 'type', 'create_date', 'update_date')
        );
        // ----------------------------------

//        echo $resultCollection->getSelectSql(true); exit;

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'        => Mage::helper('M2ePro')->__('Title'),
            'align'         => 'left',
            'type'          => 'text',
//            'width'         => '150px',
            'index'         => 'title',
            'escape'        => true,
            'filter_index'  => 'main_table.title'
        ));

        $options = array(
            self::TEMPLATE_SELLING_FORMAT
                => Mage::helper('M2ePro')->__('Selling Format'),
            self::TEMPLATE_SYNCHRONIZATION
                => Mage::helper('M2ePro')->__('Synchronization')
        );
        $this->addColumn('type', array(
            'header'        => Mage::helper('M2ePro')->__('Type'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'sortable'      => false,
            'index'         => 'type',
            'filter_index'  => 'main_table.type',
            'options'       => $options
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getTemplateId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array(
                        'base' => '*/adminhtml_common_template/edit',
                        'params' => array(
                            'type' => '$type',
                            'channel' => $this->nick
                        )
                    ),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array(
                        'base' => '*/adminhtml_common_template/delete',
                        'params' => array(
                            'type' => '$type',
                            'channel' => $this->nick
                        )
                    ),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
        ));

        return parent::_prepareColumns();
    }

    // ##########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/adminhtml_common_template/edit',
            array(
                'id' => $row->getData('template_id'),
                'type' => $row->getData('type'),
                'back' => 1
            )
        );
    }

    // ##########################################
}