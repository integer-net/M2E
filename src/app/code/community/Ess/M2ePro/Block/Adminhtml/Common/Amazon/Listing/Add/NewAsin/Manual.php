<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_NewAsin_Manual
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $listingId = $this->getRequest()->getParam('id');

        // Initialization block
        //------------------------------
        $this->setId('newAsinDescriptionTemplateManual');
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__("Set Description Policy for New ASIN/ISBN Creation");
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_listing_add_newAsin_manual';
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/*/resetNewAsin', array(
            '_current' => true
        ));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ListingGridHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));

        $url = $this->getUrl('*/*/index', array('_current' => true, 'step' => 3));
        //------------------------------
        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'ListingGridHandlerObj.checkManualProducts(\''.$url.'\')',
            'class'     => 'scalable next'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $listing = Mage::helper('M2ePro/Component')
            ->getCachedUnknownObject('Listing', $this->getRequest()->getParam('id'));

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        // --------TEXT------------------
        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy'));
        $setDescriptionPolicy = $helper->escapeJs($helper->__('Set Description Policy.'));
        // -------------------------------

        // ---------URL-------------------
        $mapToTemplateDescription = $this->getUrl('*/adminhtml_common_amazon_listing/mapToTemplateDescription');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_common_amazon_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateDescriptionAssign = $this->getUrl(
            '*/adminhtml_common_amazon_listing/validateProductsForTemplateDescriptionAssign');
        $viewTemplateDescriptionsGrid = $this->getUrl('*/*/viewTemplateDescriptionsGrid');

        $mapToNewAsin = $this->getUrl('*/adminhtml_common_amazon_listing/mapToNewAsin');
        $unmapFromNewAsin = $this->getUrl('*/adminhtml_common_amazon_listing/unmapFromAsin');

        $checkNewAsinManualProducts = $this->getUrl('*/*/checkNewAsinManualProducts', array('_current' => true));
        // -------------------------------

        $javascript = <<<HTML
<script type="text/javascript">
    selectTemplateDescription = function (el, templateId, mapToGeneralId)
    {
        ListingGridHandlerObj.mapToTemplateDescription(el, templateId, mapToGeneralId);
    };

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';
    M2ePro.text.setDescriptionPolicy = '{$setDescriptionPolicy}';

    M2ePro.url.mapToTemplateDescription = '{$mapToTemplateDescription}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateDescriptionAssign = '{$validateProductsForTemplateDescriptionAssign}';
    M2ePro.url.viewTemplateDescriptionsGrid = '{$viewTemplateDescriptionsGrid}';

    M2ePro.url.mapToNewAsin = '{$mapToNewAsin}';
    M2ePro.url.unmapFromNewAsin = '{$unmapFromNewAsin}';

    M2ePro.url.checkNewAsinManualProducts = '{$checkNewAsinManualProducts}';

    Event.observe(window, 'load', function() {

        CommonHandler.prototype.scroll_page_to_top = function() { return; }

        ListingGridHandlerObj = new NewAsinTemplateDescriptionGridHandler(
            '{$this->getChild('grid')->getId()}',
            {$this->getListing()->getId()}
        );

        ListingGridHandlerObj.actionHandler.setOptions(M2ePro);
        ListingGridHandlerObj.templateDescriptionHandler.setOptions(M2ePro);
    });

</script>
HTML;

        return $javascript .
        '<div id="search_asin_products_container">' .
        parent::_toHtml() .
        '</div>';
    }

    // ####################################

    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->listing;
    }

    // ####################################

}