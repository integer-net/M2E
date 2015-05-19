<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_TemplateDescription_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $attributesSetsIds;
    protected $marketplaceId;
    protected $listingProduct;
    protected $productsAttributesCountVariations;

    protected $checkNewAsinAccepted = false;
    protected $productsIds;
    protected $mapToTemplateJsFn = 'ListingGridHandlerObj.templateDescriptionHandler.mapToTemplateDescription';

    //------------------------------

    /**
     * @return string
     */
    public function getMapToTemplateJsFn()
    {
        return $this->mapToTemplateJsFn;
    }

    /**
     * @param string $mapToTemplateLink
     */
    public function setMapToTemplateJsFn($mapToTemplateLink)
    {
        $this->mapToTemplateJsFn = $mapToTemplateLink;
    }

    //------------------------------

    /**
     * @param boolean $checkNewAsinAccepted
     */
    public function setCheckNewAsinAccepted($checkNewAsinAccepted)
    {
        $this->checkNewAsinAccepted = $checkNewAsinAccepted;
    }

    /**
     * @return boolean
     */
    public function getCheckNewAsinAccepted()
    {
        return (bool) $this->checkNewAsinAccepted;
    }

    //------------------------------

    /**
     * @param mixed $productsIds
     */
    public function setProductsIds($productsIds)
    {
        $this->productsIds = $productsIds;
    }

    /**
     * @return mixed
     */
    public function getProductsIds()
    {
        return $this->productsIds;
    }

    //------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateDescriptionGrid');

        // Set default values
        //------------------------------
        $this->setFilterVisibility(false);
        //$this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        //------------------------------
    }

    //------------------------------

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Template_Description_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Description');
        $collection->addFieldToFilter('marketplace_id', $this->getMarketplaceId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('status', array(
            'header'       => Mage::helper('M2ePro')->__('Status/Reason'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '140px',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $this->addColumn('action', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'left',
            'type'         => 'number',
            'width'        => '55px',
            'index'        => 'id',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnAction')
        ));
    }

    protected function _prepareLayout()
    {
        $this->setChild('refresh_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'id' => 'description_template_refresh_btn',
                    'label'     => Mage::helper('M2ePro')->__('Refresh'),
                    'onclick'   => $this->getJsObjectName().'.reload()'
                ))
        );

        return parent::_prepareLayout();
    }

    // ####################################

    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        return $this->getRefreshButtonHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $templateDescriptionEditUrl = $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array(
            'id' => $row->getData('id')
        ));

        $title = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));

        $categoryWord = Mage::helper('M2ePro')->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
            : Mage::helper('M2ePro')->__('N/A');

        return <<<HTML
<a target="_blank" href="{$templateDescriptionEditUrl}">{$title}</a>
<div>
    <span style="font-weight: bold">{$categoryWord}</span>: <span style="color: #505050">{$categoryPath}</span><br/>
</div>
HTML;

    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($this->getCheckNewAsinAccepted()) {
            if (!$row->getChildObject()->isNewAsinAccepted()) {
                return '<span style="color: #808080;">' .
                    Mage::helper('M2ePro')->__('New ASIN/ISBN feature is disabled') . '</span>';
            }

            $productAttrCounts = $this->getProductAttributesCountVariations();

            if (!empty($productAttrCounts)) {
                $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
                $detailsModel->setMarketplaceId($this->getMarketplaceId());
                $themes = $detailsModel->getVariationThemes($row->getProductDataNick());

                if (empty($themes)) {
                    return '<span style="color: #808080;">' .
                        Mage::helper('M2ePro')->__(
                            'Selected Category doesn\'t support Variational Products'
                        ) . '</span>';
                }

                $themeAttrCounts = array();
                foreach ($themes as $theme) {
                    $themeAttrCounts[] = count($theme['attributes']);
                }

                if (count(array_intersect($productAttrCounts, $themeAttrCounts)) !== count($productAttrCounts)) {
                    return '<span style="color: #808080;">' .
                        Mage::helper('M2ePro')->__(
                            'This number of Variation Attributes cannot be used in chosen Category'
                        ) . '</span>';
                }
            }
        }

        return '<span style="color: green;">' . Mage::helper('M2ePro')->__('Ready to be assigned') . '</span>';
    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign');
        $mapToAsin = '';

        if ($this->getCheckNewAsinAccepted()) {
            if (!$row->getChildObject()->isNewAsinAccepted()) {
                return '<span style="color: #808080;">' . $assignText . '</span>';
            }

            $productAttrCounts = $this->getProductAttributesCountVariations();

            if (!empty($productAttrCounts)) {
                $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
                $detailsModel->setMarketplaceId($this->getMarketplaceId());
                $themes = $detailsModel->getVariationThemes($row->getProductDataNick());

                if (empty($themes)) {
                    return '<span style="color: #808080;">' . $assignText . '</span>';
                }

                $themeAttrCounts = array();
                foreach ($themes as $theme) {
                    $themeAttrCounts[] = count($theme['attributes']);
                }

                if (count(array_intersect($productAttrCounts, $themeAttrCounts)) !== count($productAttrCounts)) {
                    return '<span style="color: #808080;">' . $assignText . '</span>';
                }
            }

            $mapToAsin = ',1';
        }

        return '<a href="javascript:void(0);"'
            . 'onclick="' . $this->getMapToTemplateJsFn() . '(this, '
            . $value . $mapToAsin .');">'.$assignText.'</a>';
    }

    // ####################################

    protected function _toHtml()
    {
        $productsIdsStr = implode(',', $this->getProductsIds());

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#amazonTemplateDescriptionGrid div.grid th').each(function(el){
        el.style.padding = '5px 5px';
    });

    $$('#amazonTemplateDescriptionGrid div.grid td').each(function(el){
        el.style.padding = '5px 5px';
    });

    {$this->getJsObjectName()}.reloadParams = {$this->getJsObjectName()}.reloadParams || {};
    {$this->getJsObjectName()}.reloadParams['products_ids'] = '{$productsIdsStr}';

</script>
JAVASCRIPT;

        //------------------------------
        $templateDescriptionNewUrl = $this->getNewTemplateDescriptionUrl();

        $data = array(
            'id'    => 'templateDescription_addNew_button',
            'label' => Mage::helper('M2ePro')->__('Add New Description Policy'),
            'class' => 'templateDescription_addNew_button',
            'style' => 'float: right;',
            'onclick' => 'ListingGridHandlerObj.templateDescriptionHandler'
                . '.createTemplateDescriptionInNewTab(\'' . $templateDescriptionNewUrl . '\')'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        //------------------------------

        $buttonBlockHtml = ($this->canDisplayContainer()) ? $buttonBlock->toHtml(): '';

        return parent::_toHtml() . $buttonBlockHtml . $javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewTemplateDescriptionsGrid', array(
            '_current' => true,
            '_query' => array(
                'check_is_new_asin_accepted' => $this->getCheckNewAsinAccepted()
            )
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function getMarketplaceId()
    {
        if(empty($this->marketplaceId)) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $productsIds = $this->getProductsIds();
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productsIds[0]);
            $this->marketplaceId = $listingProduct->getListing()->getMarketplaceId();
        }

        return $this->marketplaceId;
    }

    //---------------------------------------

    protected function setNoTemplatesText()
    {
        $templateDescriptionEditUrl = $this->getNewTemplateDescriptionUrl();

        $messageTxt = Mage::helper('M2ePro')->__('Description Policies are not found for current Marketplace.');
        $linkTitle = Mage::helper('M2ePro')->__('Create New Description Policy.');

        $message = <<<HTML
<p>{$messageTxt} <a href="javascript:void(0);"
    id="templateDescription_addNew_link"
    onclick="ListingGridHandlerObj.templateDescriptionHandler.createTemplateDescriptionInNewTab(
        '{$templateDescriptionEditUrl}');">{$linkTitle}</a>
</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateDescriptionUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_description/new', array(
            'is_new_asin_accepted'  => $this->getCheckNewAsinAccepted(),
            'marketplace_id'        => $this->getMarketplaceId()
        ));
    }

    //---------------------------------------

    protected function getParentListingProduct()
    {
        $productIds = $this->getProductsIds();
        if (count($productIds) == 1 && empty($this->listingProduct)) {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productIds[0]);
            if ($listingProduct->getChildObject()->getVariationManager()->isVariationParent()) {
                $this->listingProduct = $listingProduct;
            }
        }
        return $this->listingProduct;
    }

    //---------------------------------------

    protected function getProductAttributesCountVariations()
    {
        if (is_null($this->productsAttributesCountVariations)) {
            $this->productsAttributesCountVariations = array();

            /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection $collection */
            $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
            $collection->addFieldToFilter('additional_data', array('notnull' => true));
            $collection->addFieldToFilter('id', array('in' => $this->getProductsIds()));
            $collection->addFieldToSelect('additional_data');

            $collection->join(
                array('alp' => 'M2ePro/Amazon_Listing_Product'),
                'listing_product_id=id',
                null
            );
            $collection->addFieldToFilter('is_variation_parent', 1);

            foreach ($collection->getData() as $row) {
                $data = json_decode($row['additional_data'], true);

                $count = count($data['variation_product_attributes']);
                if (!in_array($count, $this->productsAttributesCountVariations)) {
                    $this->productsAttributesCountVariations[] = $count;
                }
            }
        }

        return $this->productsAttributesCountVariations;
    }

    //---------------------------------------
}