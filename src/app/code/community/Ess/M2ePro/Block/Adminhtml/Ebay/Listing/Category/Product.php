<?php

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Product
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_category_product';
        //------------------------------

        // Set header text
        //------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('Set eBay Category for Product(s)');
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
        $url = $this->getUrl('*/*/',array('step' => 1, '_current' => true));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('next', array(
            'class' => 'next',
            'label' => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'EbayListingCategoryProductGridHandlerObj.nextStep();'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        //------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $header = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array(
                'listing' => $listing
            )
        );
        //------------------------------

        return $header->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        $parentHtml = parent::_toHtml();

        //-----------------------------------------
        $url = $this->getUrl('*/*/',array('step' => 3, '_current' => true));
        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'setLocation(\''.$url.'\')'
        );
        $continueButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        //-----------------------------------------

        return <<<HTML
<div id="products_progress_bar"></div>
<div id="products_container">{$parentHtml}</div>
<div id="next_step_warning_popup_content" style="display: none">

    <div style="margin: 10px; height: 155px">
        {$helper->__(
'<b>Warning:</b> There are <span id="failed_count"></span> product(s) of <span id="total_count"></span>
which have no Primary eBay category specified.
<br><br>
You can correct it now by Cancelling this message and choosing Edit Category action.
Or if you are not sure which category to choose, Continue working.
You will have a chance to correct category(s) later in the listing settings.
')}
    </div>

    <div style="text-align: right">
        <a href="javascript:"
            onclick="EbayListingCategoryProductGridHandlerObj.nextStepWarningPopup.hide();">{$helper->__('Cancel')}</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        {$continueButton->toHtml()}
    </div>

</div>
HTML;
    }

    // ########################################
}