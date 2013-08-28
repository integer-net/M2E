<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
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

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_listing/index').'\')',
            'class'     => 'button_link'
        ));

        $url = $this->getUrl(
            '*/adminhtml_log/listingOther',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listingOther/index'))
        );
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_EBAY   => $this->getUrl('*/adminhtml_ebay_listingOther/index'),
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_amazon_listingOther/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_buy_listingOther/index'),
            self::TAB_ID_PLAY    => $this->getUrl('*/adminhtml_play_listingOther/index'),
        );

        $this->isAjax = json_encode($this->getRequest()->isXmlHttpRequest());
    }

    // ########################################

    protected function getHelpBlockJavascript($helpContainerId)
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return '';
        }

        return <<<JAVASCRIPT
<script type="text/javascript">
    setTimeout(function() {
        ModuleNoticeObj.observeModulePrepareStart($('{$helpContainerId}'));
    }, 50);
</script>
JAVASCRIPT;
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (!$this->getChild('ebay_tab')) {
            $this->setChild('ebay_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other'));
        }
        return $this->getChild('ebay_tab');
    }

    public function getEbayTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $model = 'Listing_Other';

        $logViewUrl = $this->getUrl('*/adminhtml_log/listingOther', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK),
            'back'=>$helper->makeBackUrlParam('*/adminhtml_listingOther/index')
        ));

        $mapToProductUrl = $this->getUrl('*/adminhtml_'.$component.'_listingOther/mapToProduct');
        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_'.$component.'_listingOther/mapAutoToProduct');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $prepareData = $this->getUrl('*/adminhtml_listingOther/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_ebay_listingOther/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_ebay_listingOther/moveToListing');

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not moved. <a href="%s">View log</a> for details.', $logViewUrl)
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some of the products were not moved. <a href="%s">View log</a> for details.', $logViewUrl)
        );

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully mapped.'));
        $mappingProductMessage = $helper->escapeJs($helper->__('Mapping Product'));
        $productDoesNotExistMessage = $helper->escapeJs($helper->__('Product does not exist.'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        // ->__('Current eBay version only supports simple products in mapping. Please, choose simple product.')
        $temp = 'Current eBay version only supports simple products in mapping. Please, choose simple product.';
        $selectSimpleProductMessage = $helper->escapeJs($helper->__($temp));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %s product(s).'));

        $checkLockListing = $this->getUrl('*/adminhtml_listingOther/checkLockListing', array('component'=>$component));
        $lockListingNow = $this->getUrl('*/adminhtml_listingOther/lockListingNow', array('component'=>$component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listingOther/unlockListingNow', array('component'=>$component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listingOther/getErrorsSummary');

        $runReviseProducts = $this->getUrl('*/adminhtml_ebay_listingOther/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_ebay_listingOther/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_ebay_listingOther/runStopProducts');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs($helper->__('"%s" task has successfully completed.'));

        // ->__('"%s" task has completed with warnings. <a href="%s">View log</a> for details.')
        $temp = '"%s" task has completed with warnings. <a href="%s">View log</a> for details.';
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__($temp));

        // ->__('"%s" task has completed with errors. <a href="%s">View log</a> for details.')
        $temp = '"%s" task has completed with errors. <a href="%s">View log</a> for details.';
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__($temp));

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %s product(s) data on eBay.'));
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

        $invalidDataMessage = $helper->escapeJs($helper->__('Please enter correct product ID.'));
        $enterProductOrSkuMessage = $helper->escapeJs($helper->__('Please enter correct product ID or SKU'));
        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));
        $selectOnlyMapped = $helper->escapeJs($helper->__('Only mapped products must be selected.'));
        $selectTheSameTypeProducts = $helper->escapeJs(
            $helper->__('Selected items must belong to the same Account and Marketplace.')
        );

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    M2eProEbay = {};
    M2eProEbay.url = {};
    M2eProEbay.formData = {};
    M2eProEbay.customData = {};
    M2eProEbay.text = {};

    M2eProEbay.url.logViewUrl = '{$logViewUrl}';
    M2eProEbay.url.checkLockListing = '{$checkLockListing}';
    M2eProEbay.url.lockListingNow = '{$lockListingNow}';
    M2eProEbay.url.unlockListingNow = '{$unlockListingNow}';
    M2eProEbay.url.getErrorsSummary = '{$getErrorsSummary}';

    M2eProEbay.url.runReviseProducts = '{$runReviseProducts}';
    M2eProEbay.url.runRelistProducts = '{$runRelistProducts}';
    M2eProEbay.url.runStopProducts = '{$runStopProducts}';

    M2eProEbay.url.mapToProduct = '{$mapToProductUrl}';
    M2eProEbay.url.mapAutoToProductUrl = '{$mapAutoToProductUrl}';
    M2eProEbay.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProEbay.url.prepareData = '{$prepareData}';
    M2eProEbay.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProEbay.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProEbay.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProEbay.url.moveToListing = '{$moveToListing}';

    M2eProEbay.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProEbay.text.mapping_product_title = '{$mappingProductMessage}';
    M2eProEbay.text.product_does_not_exist = '{$productDoesNotExistMessage}';
    M2eProEbay.text.select_simple_product = '{$selectSimpleProductMessage}';
    M2eProEbay.text.invalid_data = '{$invalidDataMessage}';
    M2eProEbay.text.enter_product_or_sku = '{$enterProductOrSkuMessage}';
    M2eProEbay.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProEbay.text.processing_data_message = '{$processingDataMessage}';

    M2eProEbay.text.popup_title = '{$popupTitle}';
    M2eProEbay.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProEbay.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProEbay.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProEbay.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2eProEbay.text.task_completed_message = '{$taskCompletedMessage}';
    M2eProEbay.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2eProEbay.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2eProEbay.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2eProEbay.text.sending_data_message = '{$sendingDataToEbayMessage}';
    M2eProEbay.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2eProEbay.text.listing_locked_message = '{$listingLockedMessage}';
    M2eProEbay.text.listing_empty_message = '{$listingEmptyMessage}';

    M2eProEbay.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2eProEbay.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2eProEbay.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2eProEbay.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2eProEbay.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2eProEbay.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';

    M2eProEbay.text.select_items_message = '{$selectItemsMessage}';
    M2eProEbay.text.select_action_message = '{$selectActionMessage}';

    M2eProEbay.text.select_only_mapped_products = '{$selectOnlyMapped}';
    M2eProEbay.text.select_the_same_type_products = '{$selectTheSameTypeProducts}';

    M2eProEbay.text.confirm = '{$confirmMessage}';

    M2eProEbay.text.success_word = '{$successWord}';
    M2eProEbay.text.notice_word = '{$noticeWord}';
    M2eProEbay.text.warning_word = '{$warningWord}';
    M2eProEbay.text.error_word = '{$errorWord}';
    M2eProEbay.text.close_word = '{$closeWord}';

    M2eProEbay.customData.model = '{$model}';
    M2eProEbay.customData.componentMode = '{$component}';
    M2eProEbay.customData.gridId = 'ebayListingOtherGrid';

    var init = function () {
        EbayListingOtherMapToProductHandlerObj = new ListingOtherMapToProductHandler(M2eProEbay);
        EbayListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2eProEbay);
        EbayListingActionHandlerObj = new ListingActionHandler(M2eProEbay,'listingOther');
        EbayListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2eProEbay);
        EbayListingOtherAutoMapHandlerObj = new ListingOtherAutoMapHandler(M2eProEbay);
        EbayListingItemGridHandlerObj = new ListingItemGridHandler(
            M2eProEbay,'ebayListingOtherGrid',3,2,EbayListingActionHandlerObj,
            EbayListingMoveToListingHandlerObj, undefined, EbayListingOtherAutoMapHandlerObj
        );
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_help');

        $javascriptsMain .= $this->getHelpBlockJavascript($helpBlock->getContainerId());

        return $javascriptsMain . $helpBlock->toHtml() . $this->getEbayTabBlockFilterHtml() . parent::getEbayTabHtml();
    }

    private function getEbayTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'controller_name' => 'adminhtml_listingOther'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'controller_name' => 'adminhtml_listingOther'
        ));

        return '<div class="filter_block">' .
               $marketplaceFilterBlock->toHtml() .
               $accountFilterBlock->toHtml() .
               '</div>';
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_other_grid')
            );
        }
        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $logViewUrl = $this->getUrl('*/adminhtml_log/listingOther', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Amazon::NICK),
            'back'=>$helper->makeBackUrlParam('*/adminhtml_listingOther/index')
        ));

        $prepareData = $this->getUrl('*/adminhtml_listingOther/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listingOther/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_amazon_listingOther/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_amazon_listingOther/moveToListing');
        $mapToProductUrl = $this->getUrl('*/adminhtml_amazon_listingOther/mapToProduct');
        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_amazon_listingOther/mapAutoToProduct');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with default settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Amazon Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not moved. <a href="%s">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some of the products were not moved. <a href="%s">View log</a> for details.')
        );

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %s product(s).'));
        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully mapped.'));
        $mappingProductMessage = $helper->escapeJs($helper->__('Mapping Product'));
        $productDoesNotExistMessage = $helper->escapeJs($helper->__('Product does not exist.'));

        // ->__('Item was not mapped as the chosen %s Simple Product has Custom Options.')
        $tempSelectWithOutOptions = 'Item was not mapped as the chosen %s Simple Product has Custom Options.' . ' ';
        $tempSelectWithOutOptions = Mage::helper('M2ePro')->__($tempSelectWithOutOptions);

        // ->__('Please, choose another Simple Product.')
        $temp2SelectWithOutOptions = 'Please, choose another Simple Product.';
        $temp2SelectWithOutOptions = Mage::helper('M2ePro')->__($temp2SelectWithOutOptions);

        $selectWithOutOptions = $tempSelectWithOutOptions . $temp2SelectWithOutOptions;

        // ->__('Current Amazon version only supports simple products. Please, choose simple product.')
        $temp = 'Current Amazon version only supports simple products. Please, choose simple product.';
        $selectSimpleProductMessage = $helper->escapeJs($helper->__($temp));

        $invalidDataMessage = $helper->escapeJs($helper->__('Please enter correct product ID.'));
        $enterProductOrSkuMessage = $helper->escapeJs($helper->__('Please enter correct product ID or SKU'));
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

    M2eProAmazon.url.logViewUrl = '{$logViewUrl}';
    M2eProAmazon.url.prepareData = '{$prepareData}';
    M2eProAmazon.url.createDefaultListing = '{$createDefaultListingUrl}';
    M2eProAmazon.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProAmazon.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProAmazon.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProAmazon.url.moveToListing = '{$moveToListing}';
    M2eProAmazon.url.mapToProduct = '{$mapToProductUrl}';
    M2eProAmazon.url.mapAutoToProductUrl = '{$mapAutoToProductUrl}';

    M2eProAmazon.text.create_listing = '{$createListing}';
    M2eProAmazon.text.popup_title = '{$popupTitle}';
    M2eProAmazon.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProAmazon.text.confirm = '{$confirmMessage}';
    M2eProAmazon.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProAmazon.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProAmazon.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2eProAmazon.text.select_items_message = '{$selectItemsMessage}';
    M2eProAmazon.text.select_action_message = '{$selectActionMessage}';

    M2eProAmazon.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProAmazon.text.mapping_product_title = '{$mappingProductMessage}';
    M2eProAmazon.text.product_does_not_exist = '{$productDoesNotExistMessage}';
    M2eProAmazon.text.select_simple_product = '{$selectSimpleProductMessage}';
    M2eProAmazon.text.invalid_data = '{$invalidDataMessage}';
    M2eProAmazon.text.enter_product_or_sku = '{$enterProductOrSkuMessage}';
    M2eProAmazon.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProAmazon.text.processing_data_message = '{$processingDataMessage}';
    M2eProAmazon.text.select_without_options = '{$selectWithOutOptions}';
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
        AmazonListingOtherMapToProductHandlerObj = new ListingOtherMapToProductHandler(M2eProAmazon);
        AmazonListingActionHandlerObj = new ListingActionHandler(M2eProAmazon);
        AmazonListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2eProAmazon);
        AmazonListingOtherAutoMapHandlerObj = new ListingOtherAutoMapHandler(M2eProAmazon);
        AmazonListingItemGridHandlerObj = new ListingItemGridHandler( M2eProAmazon,
                                                                      'amazonListingOtherGrid',
                                                                      3,
                                                                      2,
                                                                      AmazonListingActionHandlerObj,
                                                                      AmazonListingMoveToListingHandlerObj,
                                                                      undefined,
                                                                      AmazonListingOtherAutoMapHandlerObj );
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_other_help');

        $javascriptsMain .= $this->getHelpBlockJavascript($helpBlock->getContainerId());

        return $javascriptsMain .
               $helpBlock->toHtml() .
               $this->getAmazonTabBlockFilterHtml() .
               parent::getAmazonTabHtml();
    }

    private function getAmazonTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_listingOther'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_listingOther'
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
            $this->setChild('buy_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_other_grid'));
        }
        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Buy::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_log/listingOther',
            array(
                'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK),
                'back' => $helper->makeBackUrlParam('*/adminhtml_listingOther/index/tab/' . $componentMode)
            )
        );

        $prepareData = $this->getUrl('*/adminhtml_listingOther/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listingOther/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_buy_listingOther/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_buy_listingOther/moveToListing');
        $mapToProductUrl = $this->getUrl('*/adminhtml_buy_listingOther/mapToProduct');
        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_buy_listingOther/mapAutoToProduct');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with default settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Rakuten.com Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not moved. <a href="%s">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some of the products were not moved. <a href="%s">View log</a> for details.')
        );

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %s product(s).'));
        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully mapped.'));
        $mappingProductMessage = $helper->escapeJs($helper->__('Mapping Product'));
        $productDoesNotExistMessage = $helper->escapeJs($helper->__('Product does not exist.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $selectWithOutOptions = 'Item was not mapped as the chosen %s Simple Product has Custom Options. ';
        $selectWithOutOptions .= 'Please, choose another Simple Product.';
        $selectWithOutOptions = Mage::helper('M2ePro')->__($selectWithOutOptions);

        // ->__('Current Rakuten.com version only supports simple products. Please, choose simple product.')
        $temp = 'Current Rakuten.com version only supports simple products. Please, choose simple product.';
        $selectSimpleProductMessage = $helper->escapeJs($helper->__($temp));

        $invalidDataMessage = $helper->escapeJs($helper->__('Please enter correct product ID.'));
        $enterProductOrSkuMessage = $helper->escapeJs($helper->__('Please enter correct product ID or SKU'));
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
    M2eProBuy.url.mapToProduct = '{$mapToProductUrl}';
    M2eProBuy.url.mapAutoToProductUrl = '{$mapAutoToProductUrl}';

    M2eProBuy.text.create_listing = '{$createListing}';
    M2eProBuy.text.popup_title = '{$popupTitle}';
    M2eProBuy.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProBuy.text.confirm = '{$confirmMessage}';
    M2eProBuy.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProBuy.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProBuy.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2eProBuy.text.select_items_message = '{$selectItemsMessage}';
    M2eProBuy.text.select_action_message = '{$selectActionMessage}';

    M2eProBuy.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProBuy.text.mapping_product_title = '{$mappingProductMessage}';
    M2eProBuy.text.product_does_not_exist = '{$productDoesNotExistMessage}';
    M2eProBuy.text.select_simple_product = '{$selectSimpleProductMessage}';
    M2eProBuy.text.invalid_data = '{$invalidDataMessage}';
    M2eProBuy.text.enter_product_or_sku = '{$enterProductOrSkuMessage}';
    M2eProBuy.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProBuy.text.processing_data_message = '{$processingDataMessage}';
    M2eProBuy.text.select_without_options = '{$selectWithOutOptions}';
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
        BuyListingOtherMapToProductHandlerObj = new ListingOtherMapToProductHandler(M2eProBuy);
        BuyListingActionHandlerObj = new ListingActionHandler(M2eProBuy);
        BuyListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2eProBuy);
        BuyListingOtherAutoMapHandlerObj = new ListingOtherAutoMapHandler(M2eProBuy);
        BuyListingItemGridHandlerObj = new ListingItemGridHandler( M2eProBuy,
                                                                      'buyListingOtherGrid',
                                                                      3,
                                                                      2,
                                                                      BuyListingActionHandlerObj,
                                                                      BuyListingMoveToListingHandlerObj,
                                                                      undefined,
                                                                      BuyListingOtherAutoMapHandlerObj );
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_other_help');

        $javascriptsMain .= $this->getHelpBlockJavascript($helpBlock->getContainerId());

        return $javascriptsMain .
            $helpBlock->toHtml() .
            $this->getBuyTabBlockFilterHtml() .
            parent::getBuyTabHtml();
    }

    private function getBuyTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
            'controller_name' => 'adminhtml_listingOther'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
            'controller_name' => 'adminhtml_listingOther'
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
            $this->setChild('play_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_other_grid'));
        }
        return $this->getChild('play_tab');
    }

    public function getPlayTabHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Play::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_log/listingOther',
            array(
                'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Play::NICK),
                'back' => $helper->makeBackUrlParam('*/adminhtml_listingOther/index/tab/' . $componentMode)
            )
        );

        $prepareData = $this->getUrl('*/adminhtml_listingOther/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listingOther/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_play_listingOther/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_play_listingOther/moveToListing');
        $mapToProductUrl = $this->getUrl('*/adminhtml_play_listingOther/mapToProduct');
        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_play_listingOther/mapAutoToProduct');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with default settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Play.com Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not moved. <a href="%s">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some of the products were not moved. <a href="%s">View log</a> for details.')
        );

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %s product(s).'));
        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully mapped.'));
        $mappingProductMessage = $helper->escapeJs($helper->__('Mapping Product'));
        $productDoesNotExistMessage = $helper->escapeJs($helper->__('Product does not exist.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $selectWithOutOptions = 'Item was not mapped as the chosen %s Simple Product has Custom Options. ';
        $selectWithOutOptions .= 'Please, choose another Simple Product.';
        $selectWithOutOptions = Mage::helper('M2ePro')->__($selectWithOutOptions);

        // ->__('Current Play.com version only supports simple products. Please, choose simple product.')
        $temp = 'Current Play.com version only supports simple products. Please, choose simple product.';
        $selectSimpleProductMessage = $helper->escapeJs($helper->__($temp));

        $invalidDataMessage = $helper->escapeJs($helper->__('Please enter correct product ID.'));
        $enterProductOrSkuMessage = $helper->escapeJs($helper->__('Please enter correct product ID or SKU'));
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
    M2eProPlay.url.mapToProduct = '{$mapToProductUrl}';
    M2eProPlay.url.mapAutoToProductUrl = '{$mapAutoToProductUrl}';

    M2eProPlay.text.create_listing = '{$createListing}';
    M2eProPlay.text.popup_title = '{$popupTitle}';
    M2eProPlay.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProPlay.text.confirm = '{$confirmMessage}';
    M2eProPlay.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProPlay.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProPlay.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2eProPlay.text.select_items_message = '{$selectItemsMessage}';
    M2eProPlay.text.select_action_message = '{$selectActionMessage}';

    M2eProPlay.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProPlay.text.mapping_product_title = '{$mappingProductMessage}';
    M2eProPlay.text.product_does_not_exist = '{$productDoesNotExistMessage}';
    M2eProPlay.text.select_simple_product = '{$selectSimpleProductMessage}';
    M2eProPlay.text.invalid_data = '{$invalidDataMessage}';
    M2eProPlay.text.enter_product_or_sku = '{$enterProductOrSkuMessage}';
    M2eProPlay.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProPlay.text.processing_data_message = '{$processingDataMessage}';
    M2eProPlay.text.select_without_options = '{$selectWithOutOptions}';
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
        PlayListingOtherMapToProductHandlerObj = new ListingOtherMapToProductHandler(M2eProPlay);
        PlayListingActionHandlerObj = new ListingActionHandler(M2eProPlay);
        PlayListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2eProPlay);
        PlayListingOtherAutoMapHandlerObj = new ListingOtherAutoMapHandler(M2eProPlay);
        PlayListingItemGridHandlerObj = new ListingItemGridHandler( M2eProPlay,
                                                                      'playListingOtherGrid',
                                                                      3,
                                                                      2,
                                                                      PlayListingActionHandlerObj,
                                                                      PlayListingMoveToListingHandlerObj,
                                                                      undefined,
                                                                      PlayListingOtherAutoMapHandlerObj );
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_other_help');

        $javascriptsMain .= $this->getHelpBlockJavascript($helpBlock->getContainerId());

        return $javascriptsMain .
            $helpBlock->toHtml() .
            $this->getPlayTabBlockFilterHtml() .
            parent::getPlayTabHtml();
    }

    private function getPlayTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
            'controller_name' => 'adminhtml_listingOther'
        ));
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
            'controller_name' => 'adminhtml_listingOther'
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
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');
    });

</script>
JAVASCRIPT;

        $mapToProductBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapToProduct');

        return $javascriptsMain .
               $mapToProductBlock->toHtml() .
               parent::_componentsToHtml();
    }

    // ########################################
}