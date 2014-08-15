<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Other extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('3rd Party Listings');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            //------------------------------
        }

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing/index');
        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\''. $url .'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/listingOther');
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'window.open(\''.$url.'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        //------------------------------
        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_common_amazon_listing_other/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_common_buy_listing_other/index'),
            self::TAB_ID_PLAY   => $this->getUrl('*/adminhtml_common_play_listing_other/index'),
        );

        $this->isAjax = json_encode($this->getRequest()->isXmlHttpRequest());
        //------------------------------
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_other_grid')
            );
        }
        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listing_other_moving/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_other_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_other_moving/moveToListing');

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_common_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully mapped.'));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with default settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Amazon Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not moved. <a target="_blank" href="%url%">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Some of the products were not moved. <a target="_blank" href="%url%">View log</a> for details.'
        ));

        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully removed.'));

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the products you want to perform the action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% product(s).'));

        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));
        $selectOnlyMapped = $helper->escapeJs($helper->__('Only mapped products must be selected.'));
        $selectTheSameTypeProducts = $helper->escapeJs(
            $helper->__('Selected items must belong to the same Account and Marketplace.')
        );

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    M2eProAmazon = {};
    M2eProAmazon.url = {};
    M2eProAmazon.formData = {};
    M2eProAmazon.customData = {};
    M2eProAmazon.text = {};

    M2eProAmazon.url.prepareData = '{$prepareData}';
    M2eProAmazon.url.createDefaultListing = '{$createDefaultListingUrl}';
    M2eProAmazon.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProAmazon.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProAmazon.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProAmazon.url.moveToListing = '{$moveToListing}';

    M2eProAmazon.url.mapAutoToProduct = '{$mapAutoToProductUrl}';

    M2eProAmazon.url.removingProducts = '{$removingProductsUrl}';
    M2eProAmazon.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2eProAmazon.text.create_listing = '{$createListing}';
    M2eProAmazon.text.popup_title = '{$popupTitle}';
    M2eProAmazon.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProAmazon.text.confirm = '{$confirmMessage}';
    M2eProAmazon.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProAmazon.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProAmazon.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';
    M2eProAmazon.text.not_enough_data = '{$notEnoughDataMessage}';
    M2eProAmazon.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2eProAmazon.text.successfully_removed = '{$successfullyRemovedMessage}';

    M2eProAmazon.text.select_items_message = '{$selectItemsMessage}';
    M2eProAmazon.text.select_action_message = '{$selectActionMessage}';

    M2eProAmazon.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProAmazon.text.processing_data_message = '{$processingDataMessage}';
    M2eProAmazon.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProAmazon.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProAmazon.text.select_only_mapped_products = '{$selectOnlyMapped}';
    M2eProAmazon.text.select_the_same_type_products = '{$selectTheSameTypeProducts}';

    M2eProAmazon.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2eProAmazon.text.success_word = '{$successWord}';
    M2eProAmazon.text.notice_word = '{$noticeWord}';
    M2eProAmazon.text.warning_word = '{$warningWord}';
    M2eProAmazon.text.error_word = '{$errorWord}';
    M2eProAmazon.text.close_word = '{$closeWord}';

    M2eProAmazon.customData.componentMode = '{$componentMode}';
    M2eProAmazon.customData.gridId = 'amazonListingOtherGrid';

    var init = function () {
        AmazonListingOtherGridHandlerObj    = new AmazonListingOtherGridHandler('amazonListingOtherGrid');
        AmazonListingOtherMappingHandlerObj = new ListingOtherMappingHandler(
            AmazonListingOtherGridHandlerObj,
            'amazon'
        );

        AmazonListingOtherGridHandlerObj.movingHandler.setOptions(M2eProAmazon);
        AmazonListingOtherGridHandlerObj.autoMappingHandler.setOptions(M2eProAmazon);
        AmazonListingOtherGridHandlerObj.removingHandler.setOptions(M2eProAmazon);
        AmazonListingOtherGridHandlerObj.unmappingHandler.setOptions(M2eProAmazon);
    }

    if ($$('.tabs-horiz').first()) {
        var amazonTabId = $$('.tabs-horiz').first().id + '_amazon';
        $(amazonTabId).observe('click', init);
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_other_help');

        return $javascriptsMain .
               $helpBlock->toHtml() .
               $this->getAmazonTabBlockFilterHtml() .
               parent::getAmazonTabHtml();
    }

    private function getAmazonTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_common_listing_other'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_common_listing_other'
        ));

        return '<div class="filter_block">' .
               $marketplaceFilterBlock->toHtml() .
               $accountFilterBlock->toHtml() .
               '</div>';
    }

    // ########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $this->setChild(
                'buy_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_other_grid')
            );
        }
        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Buy::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_common_log/listingOther',
            array(
                'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK),
                'back' => $helper->makeBackUrlParam('*/adminhtml_common_listing_other/index/tab/' . $componentMode)
            )
        );

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listing_other_moving/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_other_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_other_moving/moveToListing');

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_common_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully mapped.'));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with default settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Rakuten.com Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not moved. <a target="_blank" href="%url%">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Some of the products were not moved. <a target="_blank" href="%url%">View log</a> for details.'
        ));

        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data.'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully removed.'));

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the products you want to perform the action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% product(s).'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));
        $selectOnlyMapped = $helper->escapeJs($helper->__('Only mapped products must be selected.'));
        $selectTheSameTypeProducts = $helper->escapeJs(
            $helper->__('Selected items must belong to the same Account and Marketplace.')
        );

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    M2eProBuy = {};
    M2eProBuy.url = {};
    M2eProBuy.formData = {};
    M2eProBuy.customData = {};
    M2eProBuy.text = {};

    M2eProBuy.url.logViewUrl = '{$logViewUrl}';
    M2eProBuy.url.prepareData = '{$prepareData}';
    M2eProBuy.url.createDefaultListing = '{$createDefaultListingUrl}';
    M2eProBuy.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProBuy.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProBuy.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProBuy.url.moveToListing = '{$moveToListing}';
    M2eProBuy.url.mapAutoToProduct = '{$mapAutoToProductUrl}';
    M2eProBuy.url.removingProducts = '{$removingProductsUrl}';
    M2eProBuy.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2eProBuy.text.create_listing = '{$createListing}';
    M2eProBuy.text.popup_title = '{$popupTitle}';
    M2eProBuy.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProBuy.text.confirm = '{$confirmMessage}';
    M2eProBuy.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProBuy.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProBuy.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';
    M2eProBuy.text.not_enough_data = '{$notEnoughDataMessage}';
    M2eProBuy.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2eProBuy.text.successfully_removed = '{$successfullyRemovedMessage}';

    M2eProBuy.text.select_items_message = '{$selectItemsMessage}';
    M2eProBuy.text.select_action_message = '{$selectActionMessage}';

    M2eProBuy.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProBuy.text.processing_data_message = '{$processingDataMessage}';
    M2eProBuy.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProBuy.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProBuy.text.select_only_mapped_products = '{$selectOnlyMapped}';
    M2eProBuy.text.select_the_same_type_products = '{$selectTheSameTypeProducts}';

    M2eProBuy.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2eProBuy.text.success_word = '{$successWord}';
    M2eProBuy.text.notice_word = '{$noticeWord}';
    M2eProBuy.text.warning_word = '{$warningWord}';
    M2eProBuy.text.error_word = '{$errorWord}';
    M2eProBuy.text.close_word = '{$closeWord}';

    M2eProBuy.customData.componentMode = '{$componentMode}';
    M2eProBuy.customData.gridId = 'buyListingOtherGrid';

    var init = function () {
        BuyListingOtherGridHandlerObj    = new BuyListingOtherGridHandler('buyListingOtherGrid');
        BuyListingOtherMappingHandlerObj = new ListingOtherMappingHandler(
            BuyListingOtherGridHandlerObj,
            'buy'
        );

        BuyListingOtherGridHandlerObj.movingHandler.setOptions(M2eProBuy);
        BuyListingOtherGridHandlerObj.autoMappingHandler.setOptions(M2eProBuy);
        BuyListingOtherGridHandlerObj.removingHandler.setOptions(M2eProBuy);
        BuyListingOtherGridHandlerObj.unmappingHandler.setOptions(M2eProBuy);
    }

    if ($$('.tabs-horiz').first()) {
        var buyTabId = $$('.tabs-horiz').first().id + '_buy';
        $(buyTabId).observe('click', init);
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);
</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_other_help');

        return $javascriptsMain .
            $helpBlock->toHtml() .
            $this->getBuyTabBlockFilterHtml() .
            parent::getBuyTabHtml();
    }

    private function getBuyTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
            'controller_name' => 'adminhtml_common_listing_other'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
            'controller_name' => 'adminhtml_common_listing_other'
        ));

        return '<div class="filter_block">' .
            $marketplaceFilterBlock->toHtml() .
            $accountFilterBlock->toHtml() .
            '</div>';
    }

    // ########################################

    protected function getPlayTabBlock()
    {
        if (!$this->getChild('play_tab')) {
            $this->setChild(
                'play_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_other_grid')
            );
        }
        return $this->getChild('play_tab');
    }

    public function getPlayTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Play::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_common_log/listingOther',
            array(
                'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Play::NICK),
                'back' => $helper->makeBackUrlParam(
                    '*/adminhtml_common_listing_other/index',
                    array(
                        'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_PLAY
                    )
                )
            )
        );

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listing_other_moving/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_other_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_other_moving/moveToListing');

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_common_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully mapped.'));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with default settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Play.com Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Products were not moved. <a target="_blank" href="%url%">View log</a> for details.'
        ));
        $someProductsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Some of the products were not moved. <a target="_blank" href="%url%">View log</a> for details.'
        ));

        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully removed.'));

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log'));

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the products you want to perform the action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% product(s).'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));
        $selectOnlyMapped = $helper->escapeJs($helper->__('Only mapped products must be selected.'));
        $selectTheSameTypeProducts = $helper->escapeJs(
            $helper->__('Selected items must belong to the same Account and Marketplace.')
        );

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    M2eProPlay = {};
    M2eProPlay.url = {};
    M2eProPlay.formData = {};
    M2eProPlay.customData = {};
    M2eProPlay.text = {};

    M2eProPlay.url.logViewUrl = '{$logViewUrl}';
    M2eProPlay.url.prepareData = '{$prepareData}';
    M2eProPlay.url.createDefaultListing = '{$createDefaultListingUrl}';
    M2eProPlay.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProPlay.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProPlay.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProPlay.url.moveToListing = '{$moveToListing}';
    M2eProPlay.url.mapAutoToProduct = '{$mapAutoToProductUrl}';
    M2eProPlay.url.removingProducts = '{$removingProductsUrl}';
    M2eProPlay.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2eProPlay.text.create_listing = '{$createListing}';
    M2eProPlay.text.popup_title = '{$popupTitle}';
    M2eProPlay.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProPlay.text.confirm = '{$confirmMessage}';
    M2eProPlay.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProPlay.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProPlay.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';
    M2eProPlay.text.not_enough_data = '{$notEnoughDataMessage}';
    M2eProPlay.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2eProPlay.text.successfully_removed = '{$successfullyRemovedMessage}';

    M2eProPlay.text.select_items_message = '{$selectItemsMessage}';
    M2eProPlay.text.select_action_message = '{$selectActionMessage}';

    M2eProPlay.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProPlay.text.processing_data_message = '{$processingDataMessage}';
    M2eProPlay.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProPlay.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProPlay.text.select_only_mapped_products = '{$selectOnlyMapped}';
    M2eProPlay.text.select_the_same_type_products = '{$selectTheSameTypeProducts}';

    M2eProPlay.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2eProPlay.text.success_word = '{$successWord}';
    M2eProPlay.text.notice_word = '{$noticeWord}';
    M2eProPlay.text.warning_word = '{$warningWord}';
    M2eProPlay.text.error_word = '{$errorWord}';
    M2eProPlay.text.close_word = '{$closeWord}';

    M2eProPlay.customData.componentMode = '{$componentMode}';
    M2eProPlay.customData.gridId = 'playListingOtherGrid';

    var init = function () {
        PlayListingOtherGridHandlerObj    = new PlayListingOtherGridHandler('playListingOtherGrid');
        PlayListingOtherMappingHandlerObj = new ListingOtherMappingHandler(
            PlayListingOtherGridHandlerObj,
            'play'
        );

        PlayListingOtherGridHandlerObj.movingHandler.setOptions(M2eProPlay);
        PlayListingOtherGridHandlerObj.autoMappingHandler.setOptions(M2eProPlay);
        PlayListingOtherGridHandlerObj.removingHandler.setOptions(M2eProPlay);
        PlayListingOtherGridHandlerObj.unmappingHandler.setOptions(M2eProPlay);
    }

    if ($$('.tabs-horiz').first()) {
        var playTabId = $$('.tabs-horiz').first().id + '_play';
        $(playTabId).observe('click', init);
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_listing_other_help');

        return $javascriptsMain .
            $helpBlock->toHtml() .
            $this->getPlayTabBlockFilterHtml() .
            parent::getPlayTabHtml();
    }

    private function getPlayTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
            'controller_name' => 'adminhtml_common_listing_other'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
            'controller_name' => 'adminhtml_common_listing_other'
        ));

        return '<div class="filter_block">' .
            $marketplaceFilterBlock->toHtml() .
            $accountFilterBlock->toHtml() .
            '</div>';
    }

    // ########################################

    protected function _toHtml()
    {
        return '<div id="listing_other_progress_bar"></div>' .
               '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
               '<div id="listing_other_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    protected function _componentsToHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $translations = json_encode(array(

            'Mapping Product' => $helper->__('Mapping Product'),
            'Product does not exist.' => $helper->__('Product does not exist.'),
            'Please enter correct product ID.' => $helper->__('Please enter correct product ID.'),
            'Product(s) was successfully mapped.' => $helper->__('Product(s) was successfully mapped.'),
            'Please enter correct product ID or SKU' => $helper->__('Please enter correct product ID or SKU'),

            'Current version only supports simple products. Please, choose simple product.' => $helper->__(
                'Current version only supports simple products. Please, choose simple product.'
            ),

            'Item was not mapped as the chosen %product_id% Simple Product has Custom Options.' => $helper->__(
                'Item was not mapped as the chosen %product_id% Simple Product has Custom Options.'
            )

        ));

        $urls = json_encode(array(

            'adminhtml_common_log/listingOther' => $this->getUrl('*/adminhtml_common_log/listingOther',array(
                'back' => $helper->makeBackUrlParam('*/adminhtml_common_listing_other/index')
            )),

            'adminhtml_listing_other_mapping/map' => $this->getUrl('*/adminhtml_listing_other_mapping/map'),

        ));

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');
    });

</script>
JAVASCRIPT;

        $mapToProductBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping');

        return $javascriptsMain .
               $mapToProductBlock->toHtml() .
               parent::_componentsToHtml();
    }

    // ########################################
}