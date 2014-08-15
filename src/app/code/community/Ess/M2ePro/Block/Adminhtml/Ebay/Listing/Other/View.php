<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_other_view';
        //------------------------------

        // Set header text
        //------------------------------
        $additionalTitleString = '';
        if ($accountId = $this->getRequest()->getParam('account')) {
            $accountObj = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account',$accountId
            );
            $additionalTitleString .= Mage::helper('M2ePro')->__('eBay User ID').': "'.$accountObj->getTitle().'"';
        }
        if ($marketplaceId = $this->getRequest()->getParam('marketplace')) {
            $marketplaceObj = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace',$marketplaceId
            );
            !empty($additionalTitleString) && $additionalTitleString .= ', ';
            $additionalTitleString .= Mage::helper('M2ePro')->__('eBay Site').': "'.$marketplaceObj->getTitle().'"';
        }
        !empty($additionalTitleString) && $additionalTitleString = ' ('.$additionalTitleString.')';
        $this->_headerText = Mage::helper('M2ePro')->__('3rd Party Listings').$additionalTitleString;
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
        if (!is_null($this->getRequest()->getParam('back'))) {
            $url = Mage::helper('M2ePro')->getBackUrl();
            $this->_addButton('back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonHandlerObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            ));
        }
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'CommonHandlerObj.reset_click()',
            'class'   => 'reset'
        ));
        //------------------------------
    }

    // ####################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $urls = $helper->getControllerActions('adminhtml_listing_other');
        $urls['adminhtml_ebay_log/listingOther'] = $this->getUrl('*/adminhtml_ebay_log/listingOther');
        $urls['adminhtml_listing_other_mapping/map'] = $this->getUrl('*/adminhtml_listing_other_mapping/map');
        $urls = json_encode($urls);

        $translations = json_encode(array(
            'Mapping Product' => $helper->__('Mapping Product'),
            'Product does not exist.' => $helper->__('Product does not exist.'),
            'Please enter correct product ID.' => $helper->__('Please enter correct product ID.'),
            'Product(s) was successfully mapped.' => $helper->__('Product(s) was successfully mapped.'),
            'Please enter correct product ID or SKU' => $helper->__('Please enter correct product ID or SKU')
        ));

        // todo next (change)
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $logViewUrl = $this->getUrl('*/adminhtml_ebay_log/listingOther', array(
            'back'=>$helper->makeBackUrlParam('*/adminhtml_listing_other/index')
        ));

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_ebay_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the mapping attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_ebay_listing_other_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_other_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_other_moving/moveToListing');

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Products were not moved. <a target="_blank" href="%url%">View log</a> for details.', $logViewUrl
        ));
        $someProductsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Some of the products were not moved. <a target="_blank" href="%url%">View log</a> for details.',$logViewUrl
        ));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully mapped.'));
        $mappingProductMessage = $helper->escapeJs($helper->__('Mapping Product'));
        $productDoesNotExistMessage = $helper->escapeJs($helper->__('Product does not exist.'));

        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data.'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully removed.'));

        // M2ePro_TRANSLATIONS
        // Current eBay version only supports simple products in mapping. Please, choose simple product.
        $temp = 'Current eBay version only supports simple products in mapping. Please, choose simple product.';
        $selectSimpleProductMessage = $helper->escapeJs($helper->__($temp));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% product(s).'));

        $checkLockListing = $this->getUrl('*/adminhtml_listing_other/checkLockListing', array('component'=>$component));
        $lockListingNow = $this->getUrl('*/adminhtml_listing_other/lockListingNow', array('component'=>$component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listing_other/unlockListingNow', array('component'=>$component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing_other/getErrorsSummary');

        $runReviseProducts = $this->getUrl('*/adminhtml_ebay_listing_other/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_ebay_listing_other/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_ebay_listing_other/runStopProducts');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs($helper->__(
            '"%task_title%" task has successfully completed.')
        );

        // M2ePro_TRANSLATIONS
        // "%task_title%" task has completed with warnings. <a target="_blank" href="%url%">View log</a> for details.
        $temp = '"%task_title%" task has completed with warnings. ';
        $temp .= '<a target="_blank" href="%url%">View log</a> for details.';
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__($temp));

        // M2ePro_TRANSLATIONS
        // "%task_title%" task has completed with errors. <a target="_blank" href="%url%">View log</a> for details.
        $temp = '"%task_title%" task has completed with errors. ';
        $temp .= '<a target="_blank" href="%url%">View log</a> for details.';
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__($temp));

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %product_title% product(s) data on eBay.'));
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

        $selectItemsMessage = $helper->escapeJs($helper->__(
            'Please select the products you want to perform the action on.'
        ));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $javascript = <<<JAVASCRIPT
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    // todo next (change)

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

    M2eProEbay.url.mapAutoToProduct = '{$mapAutoToProductUrl}';
    M2eProEbay.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProEbay.url.prepareData = '{$prepareData}';
    M2eProEbay.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProEbay.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProEbay.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProEbay.url.moveToListing = '{$moveToListing}';

    M2eProEbay.url.removingProducts = '{$removingProductsUrl}';
    M2eProEbay.url.unmappingProducts = '{$unmappingProductsUrl}';

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
    M2eProEbay.text.not_enough_data = '{$notEnoughDataMessage}';
    M2eProEbay.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2eProEbay.text.successfully_removed = '{$successfullyRemovedMessage}';

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

    M2eProEbay.customData.componentMode = '{$component}';
    M2eProEbay.customData.gridId = 'ebayListingOtherGrid';

    //

    Event.observe(window,'load',function() {

        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');

        EbayListingOtherGridHandlerObj = new EbayListingOtherGridHandler('ebayListingOtherViewGrid');
        EbayListingOtherMappingHandlerObj = new ListingOtherMappingHandler(EbayListingOtherGridHandlerObj,'ebay');

        EbayListingOtherGridHandlerObj.movingHandler.setOptions(M2eProEbay);
        EbayListingOtherGridHandlerObj.actionHandler.setOptions(M2eProEbay);
        EbayListingOtherGridHandlerObj.autoMappingHandler.setOptions(M2eProEbay);
        EbayListingOtherGridHandlerObj.removingHandler.setOptions(M2eProEbay);
        EbayListingOtherGridHandlerObj.unmappingHandler.setOptions(M2eProEbay);

    });

</script>
JAVASCRIPT;

        $mapToProductBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping');

        return  $javascript .
                $mapToProductBlock->toHtml() .
                '<div id="listing_other_progress_bar"></div>' .
                '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
                '<div id="listing_other_content_container">' .
                parent::_toHtml() .
                '</div>';
    }

    // ####################################
}