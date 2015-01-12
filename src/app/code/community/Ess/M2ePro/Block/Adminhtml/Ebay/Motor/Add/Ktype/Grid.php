<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Ktype_Grid extends Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Grid
{
    // ##########################################################

    public function getCompatibilityType()
    {
        return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE;
    }

    // ##########################################################

    protected function _prepareCollection()
    {
        $listing = Mage::getModel('M2ePro/Listing')->load($this->getListingId());

        $collection = new Ess_M2ePro_Model_Mysql4_Ebay_Motor_Ktypes_Collection('ktype');
        $collection->addFieldToFilter('marketplace_id', $listing->getMarketplaceId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ##########################################################

    protected function _prepareColumns()
    {
        $this->addColumn('ktype', array(
            'header' => Mage::helper('M2ePro')->__('KType'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'ktype',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnIdentifier')
        ));

        $this->addColumn('make', array(
            'header' => Mage::helper('M2ePro')->__('Make'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'make',
            'width'  => '150px'
        ));

        $this->addColumn('model', array(
            'header' => Mage::helper('M2ePro')->__('Model'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'model',
            'width'  => '150px'
        ));

        $this->addColumn('variant', array(
            'header' => Mage::helper('M2ePro')->__('Variant'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'variant',
            'width'  => '150px'
        ));

        $this->addColumn('body_style', array(
            'header' => Mage::helper('M2ePro')->__('Body Style'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'body_style',
            'width'  => '150px'
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('M2ePro')->__('Type'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'type',
            'width'  => '150px'
        ));

        $this->addColumn('from_year', array(
            'header' => Mage::helper('M2ePro')->__('From Year'),
            'align'  => 'left',
            'type'   => 'number',
            'index'  => 'from_year',
            'width'  => '150px'
        ));

        $this->addColumn('to_year', array(
            'header' => Mage::helper('M2ePro')->__('To Year'),
            'align'  => 'left',
            'type'   => 'number',
            'index'  => 'to_year',
            'width'  => '150px'
        ));

        $this->addColumn('engine', array(
            'header' => Mage::helper('M2ePro')->__('Engine'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'engine',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
        ));

        return parent::_prepareColumns();
    }

    // ##########################################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/motorKtypeGrid', array('listing_id' => $this->getListingId()));
    }

    // ##########################################################
}