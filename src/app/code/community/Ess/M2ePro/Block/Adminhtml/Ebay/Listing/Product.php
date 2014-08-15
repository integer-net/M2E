<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_product_source';
        $this->_controller .= ucfirst($this->getRequest()->getParam('source'));
        //------------------------------

        // Set header text
        //------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('Select Products');
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

        if ((bool)$this->getRequest()->getParam('listing_creation',false)) {
            $url = $this->getUrl('*/*/sourceMode', array('_current' => true));
        } else {
            $url = $this->getUrl('*/adminhtml_ebay_listing/view',array(
                'id' => $this->getRequest()->getParam('listing_id'),
            ));

            if ($backParam = $this->getRequest()->getParam('back')) {
                $url = Mage::helper('M2ePro')->getBackUrl();
            }
        }

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\')'
        ));
        //------------------------------
        $this->_addButton('video_tutorial', array(
            'label'     => Mage::helper('M2ePro')->__('Show Video Tutorial'),
            'class'     => 'button_link',
            'onclick'   => 'VideoTutorialHandlerObj.openPopUp();'
        ));

        //------------------------------
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $this->_addButton('auto_action', array(
                'label'     => Mage::helper('M2ePro')->__('Automatic Actions'),
                'onclick'   => 'EbayListingAutoActionHandlerObj.loadAutoActionHtml();'
            ));
        }
        //------------------------------

        //------------------------------
        $this->_addButton('continue', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => 'ListingProductAddHandlerObj.continue();'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $listingId = (int)$this->getRequest()->getParam('listing_id');

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId))
        );

        return $viewHeaderBlock->toHtml() .
               parent::getGridHtml();
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>' .
               '<div id="add_products_container">' .
               parent::_toHtml() .
               '</div>' .
               $this->getVideoTutorialHtml() .
               $this->getAutoactionPopupHtml() .
               $this->getSettingsPopupHtml();
    }

    //#############################################

    private function getVideoTutorialHtml()
    {
        $videoId = 'iBEiQ8Ilya8';
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            $videoId = '_fEtRN2eYCA';
        }

        return <<<HTML
<div id="video_tutorial_pop_up" style="display: none;">
    <div class="player_container" style="margin: 20px 5px; ">
    <object width="853" height="480">
        <param name="movie" value="http://www.youtube.com/v/{$videoId}?version=3&amp;hl=ru_RU&amp;rel=0&amp;vq=hd720"/>
        <param name="allowFullScreen" value="true"/>
        <param name="allowscriptaccess" value="always"/>
        <embed src="http://www.youtube.com/v/{$videoId}?version=3&amp;hl=ru_RU&amp;rel=0&amp;vq=hd720"
               type="application/x-shockwave-flash" width="853" height="480"
               allowscriptaccess="always" allowfullscreen="true">
        </embed>
    </object>
    </div>
</div>
HTML;

    }

    //#############################################

    private function getAutoactionPopupHtml()
    {
        $helper = Mage::helper('M2ePro');

        $onclick = <<<JS
ListingProductAddHandlerObj.autoactionPopup.close();
EbayListingAutoActionHandlerObj.loadAutoActionHtml();
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Start Configure'),
            'onclick' => $onclick
        );
        $startConfigureButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        return <<<HTML
<div id="autoaction_popup_content" style="display: none">
    <div style="margin: 10px; height: 153px">
        {$helper->__(
'<b>
    Do you want to set up a rule by which products will be automatically added or deleted from the current M2E listing?
</b>.
<br><br>
Click Start Configure to create a rule<br> or Cancel if you do not want to do it now.
<br><br>
<b>Note:</b> You can always return to it by clicking Automatic Action button on this page.'
        )}
    </div>

    <div style="text-align: right">
        <a href="javascript:"
            onclick="ListingProductAddHandlerObj.cancelAutoActionPopup();">{$helper->__('Cancel')}</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        {$startConfigureButton->toHtml()}
    </div>
</div>
HTML;
    }

    //#############################################

    private function getSettingsPopupHtml()
    {
        $helper = Mage::helper('M2ePro');

        // -------------------------------------
        $onclick = <<<JS
ListingProductAddHandlerObj.settingsPopupYesClick();
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Yes'),
            'onclick' => $onclick
        );
        $yesButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // -------------------------------------

        // -------------------------------------
        $onclick = <<<JS
ListingProductAddHandlerObj.settingsPopupNoClick();
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('No'),
            'onclick' => $onclick
        );
        $noButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // -------------------------------------

        // M2ePro_TRANSLATIONS
        // Choose <b>Yes</b> if you want to override the default settings for this M2E Pro listing and to choose different settings for certain products.
        return <<<HTML
<div id="settings_popup_content" style="display: none">
    <div style="margin: 10px; height: 150px">
        <h3>{$helper->__('Do you want to customize the M2E Pro listing settings for some products?')}</h3>
        <br/>
        <p>{$helper->__('Choose <b>Yes</b> if you want to override the default settings for this M2E Pro listing '.
                        'and to choose different settings for certain products.')}</p>
    </div>

    <div class="clear"></div>
    <div class="left">
        <div style="margin-left: 20px">
            <input id="remember_checkbox" type="checkbox">
            &nbsp;&nbsp;
            <label for="remember_checkbox">{$helper->__('Remember my choice')}</label>
        </div>
    </div>
    <div class="right">
        {$yesButton->toHtml()}
        <div style="display: inline-block;"></div>
        {$noButton->toHtml()}
    </div>
    <div class="clear"></div>
</div>
HTML;
    }

    //#############################################
}