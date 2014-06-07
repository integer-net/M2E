<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_NewProduct_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateNewProductGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------

        $this->attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                    ->load()->toOptionHash();

        //------------------------------
        $listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('listing_product_ids');

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_listing_product');

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($connWrite->select()
                ->from($tableListingProduct, 'product_id')
                ->where('id in (?)', $listingProductIds));

        $this->attributesSetsIds = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getFromProducts($productsIds, Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS);
    }

    protected function _prepareCollection()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $collection = Mage::getModel('M2ePro/Amazon_Template_NewProduct')->getCollection();
        $collection->addFieldToFilter('`marketplace_id`', $marketplaceId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'       => Mage::helper('M2ePro')->__('ID'),
            'align'        => 'right',
            'type'         => 'number',
            'width'        => '50px',
            'index'        => 'id',
            'filter_index' => 'id',
            'frame_callback' => array($this, 'callbackColumnId')
        ));

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '100px',
            'index'        => 'title',
            'filter_index' => 'title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('node_title', array(
            'header'       => Mage::helper('M2ePro')->__('Department'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '100px',
            'index'        => 'node_title',
            'filter_index' => 'node_title',
            'frame_callback' => array($this, 'callbackColumnNodeTitle')
        ));

        $this->addColumn('category_path', array(
            'header'       => Mage::helper('M2ePro')->__('Category'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '350px',
            'index'        => 'category_path',
            'filter_index' => 'category_path',
            'frame_callback' => array($this, 'callbackColumnCategoryPath')
        ));

        $this->addColumn('attribute_sets', array(
            'header' => Mage::helper('M2ePro')->__('Attribute Sets'),
            'align'  => 'left',
            'width'  => '200px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnAttributeSets')
        ));

        $marketplace_id = $this->getRequest()->getParam('marketplace_id');

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_amazon_template_newProduct',array(
            'marketplace_id'      => $marketplace_id,
        ));

        $this->addColumn('assignment', array(
            'header'       => Mage::helper('M2ePro')->__('Assignment'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '130px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('M2ePro')->__('Edit Template'),
                    'field'   => 'id',
                    'url'     => array(
                        'base'   => '*/adminhtml_common_amazon_template_newProduct/edit',
                        'params' => array(
                            'marketplace_id' => $marketplace_id,
                            'back'           => $back
                        )
                    )
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Delete Template'),
                    'confirm' => Mage::helper('M2ePro')->__('Are you sure?'),
                    'field'   => 'ids',
                    'url'     => array(
                        'base'   => '*/adminhtml_common_amazon_template_newProduct/delete',
                        'params' => array(
                            'marketplace_id' => $marketplace_id,
                            'back'           => $back
                        )
                    )
                ),
            )
        ));
    }

    // ####################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set delete action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => $this->getUrl('*/*/delete'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnId($value, $row, $column, $isExport)
    {
        return $value.'&nbsp;';
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return '&nbsp'.$value;
    }

    public function callbackColumnNodeTitle($value, $row, $column, $isExport)
    {
        return '&nbsp'.$value;
    }

    public function callbackColumnCategoryPath($value, $row, $column, $isExport)
    {
        return '&nbsp;'.$value;
    }

    public function callbackColumnAttributeSets($value, $row, $column, $isExport)
    {
        $attributeSets = Mage::getModel('M2ePro/AttributeSet')->getCollection()
            ->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_AMAZON_TEMPLATE_NEW_PRODUCT)
            ->addFieldToFilter('object_id',(int)$row->getId())
            ->toArray();

        $value = '';
        foreach ($attributeSets['items'] as $attributeSet) {
            if (strlen($value) > 100) {
                $value .= ', <strong>...</strong>';
                break;
            }
            if (isset($this->attributeSets[$attributeSet['attribute_set_id']])) {
                $value != '' && $value .= ', ';
                $value .= $this->attributeSets[$attributeSet['attribute_set_id']];
            }
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_template_newProduct/map/',
            array(
                'id' => $row->getId(),
                'marketplace_id' => $this->getRequest()->getParam('marketplace_id')
            )
        );
        $newAsinTemplateAttributes = $row->getAttributeSetsIds();

        $listingAttributesAreIncludedInNewAsinTemplate = true;
        foreach ($this->attributesSetsIds as $attributeSetId) {
            if (array_search($attributeSetId, $newAsinTemplateAttributes) === false) {
                $listingAttributesAreIncludedInNewAsinTemplate = false;
                continue;
            }
        }

        if ($listingAttributesAreIncludedInNewAsinTemplate) {
            $confirmMessage = Mage::helper('M2ePro')->__('Are you sure?');
            $actions = '&nbsp;<a href="javascript:;" onclick="confirm(\''.$confirmMessage.'\') && ';
            $actions .= 'setLocation(\''.$url.'\');">';
            $actions .= Mage::helper('M2ePro')->__('Assign To This Template');
            $actions .= '</a>';
        } else {
            $actions = '<span style="color: #808080;">';
            $actions .= '&nbsp;'.Mage::helper('M2ePro')->__('Attribute Sets Mismatch');
            $actions .= '</span>';
        }

        return $actions;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_newProduct/templateNewProductGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        $marketplace_id = $this->getRequest()->getParam('marketplace_id');

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_amazon_template_newProduct',array(
            'marketplace_id'      => $marketplace_id
        ));

        return $this->getUrl('*/adminhtml_common_amazon_template_newProduct/edit', array(
            'id' => $row->getId(),
            'marketplace_id' => $marketplace_id,
            'back' => $back
        ));
    }

    // ####################################
}