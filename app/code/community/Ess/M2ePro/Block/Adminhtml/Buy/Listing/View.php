<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_buy_listing_view';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE);
        } else {
            $componentName = '';
        }

        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');
        $this->_headerText = Mage::helper('M2ePro')->__('View%s Listing', $componentName).
                                                        ' "'.$this->escapeHtml($listingData['title']).'"';
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

            $this->_addButton('back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonHandlerObj.back_click(\''
                    .Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index')
                    .'\')',
                'class'   => 'back'
            ));
        }

        $this->_addButton('goto_listings', array(
            'label'   => Mage::helper('M2ePro')->__('Listings'),
            'onclick' => 'setLocation(\''
                .$this->getUrl('*/adminhtml_listing/index',
                    array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY))
                .'\')',
            'class'   => 'button_link'
        ));

        $this->_addButton('view_log', array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'setLocation(\''
                .$this->getUrl('*/adminhtml_log/listing',
                    array('id'=>$listingData['id'],
                        'back'=>Mage::helper('M2ePro')
                            ->makeBackUrlParam('*/adminhtml_buy_listing/view',
                            array('id'=>$listingData['id']))))
                .'\')',
            'class'   => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'ListingItemGridHandlerObj.reset_click()',
            'class'   => 'reset'
        ));

        $newListing = $this->getRequest()->getParam('new');
        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        if (is_null($newListing)) {

            $this->_addButton('clear_log', array(
                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                'onclick' => 'deleteConfirm(\''
                    .$tempStr.'\', \''
                    . $this->getUrl('*/adminhtml_listing/clearLog',
                        array('id'=>$listingData['id'],
                            'back'=>Mage::helper('M2ePro')
                                ->makeBackUrlParam('*/adminhtml_buy_listing/view',
                                array('id'=>$listingData['id']))))
                    .'\')',
                'class'   => 'clear_log'
            ));
        }

        $this->_addButton('delete', array(
            'label'   => Mage::helper('M2ePro')->__('Delete'),
            'onclick' => 'deleteConfirm(\''
                .$tempStr
                .'\', \''
                .$this->getUrl('*/adminhtml_'.Ess_M2ePro_Helper_Component_Buy::NICK.'_listing/delete',
                    array('id'=>$listingData['id']))
                .'\')',
            'class'   => 'delete'
        ));

        $this->_addButton('edit_templates', array(
            'label'   => Mage::helper('M2ePro')->__('Edit Templates'),
            'onclick' => '',
            'class'   => 'drop_down edit_template_drop_down'
        ));

        $this->_addButton('edit_settings', array(
            'label'     => Mage::helper('M2ePro')->__('Edit Settings'),
            'onclick'   => 'window.open(\''
                .$this->getUrl('*/adminhtml_buy_listing/edit',
                    array('id'=>$listingData['id'],
                        'back'=>Mage::helper('M2ePro')
                            ->makeBackUrlParam('view',
                            array('id'=>$listingData['id']))))
                .'\',\'_blank\')',
            'class'     => ''
        ));

        $this->_addButton('add_products', array(
            'label'     => Mage::helper('M2ePro')->__('Add Products'),
            'onclick'   => '',
            'class'     => 'add drop_down add_products_drop_down'
        ));
        //------------------------------
    }

    protected function _toHtml()
    {
        return '<div id="listing_view_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_view_content_container">'.
            parent::_toHtml() .
            '</div>';
    }

    public function getGridHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $component = Ess_M2ePro_Helper_Component_Buy::NICK;

        $temp = $helper->getSessionValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $listingData = $helper->getGlobalValue('temp_data');

        $gridId = 'buyListingViewGrid' . $listingData['id'];
        $ignoreListings = json_encode(array($listingData['id']));

        $logViewUrl = $this->getUrl('*/adminhtml_log/listing', array(
            'id' =>$listingData['id'],
            'back'=>$helper->makeBackUrlParam('*/adminhtml_buy_listing/view', array('id' =>$listingData['id']))
        ));
        $checkLockListing = $this->getUrl('*/adminhtml_listing/checkLockListing', array('component' => $component));
        $lockListingNow = $this->getUrl('*/adminhtml_listing/lockListingNow', array('component' => $component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listing/unlockListingNow', array('component' => $component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_buy_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_buy_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_buy_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_buy_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_buy_listing/runStopAndRemoveProducts');

        $prepareData = $this->getUrl('*/adminhtml_listing/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_buy_listing/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_buy_listing/moveToListing');

        $tempDropDownHtml = $helper->escapeJs($this->getEditTemplateDropDownHtml());
        $tempAddProductsDropDownHtml = $helper->escapeJs($this->getAddProductsDropDownHtml());

        $popupTitle = $helper->escapeJs($helper->__('Moving Rakuten.com Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%s" task has successfully submitted to be processed.')
        );
        $taskCompletedWarningMessage = $helper->escapeJs(
            $helper->__('"%s" task has completed with warnings. <a href="%s">View log</a> for details.')
        );
        $taskCompletedErrorMessage = $helper->escapeJs(
            $helper->__('"%s" task has completed with errors. <a href="%s">View log</a> for details.')
        );

        $lockedObjNoticeMessage = $helper->escapeJs(
            $helper->__('Some Rakuten.com request(s) are being processed now.')
        );
        $sendingDataToBuyMessage = $helper->escapeJs($helper->__('Sending %s product(s) data on Rakuten.com.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing All Items On Rakuten.com'));
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing Selected Items On Rakuten.com'));
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Revising Selected Items On Rakuten.com'));
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Rakuten.com'));
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Rakuten.com'));
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping On Rakuten.com And Removing From Listing Selected Items'));
        $addNewSkuSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
            ->__('Adding New SKU On Rakuten.com'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Product(s) was not moved. <a href="%s">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some product(s) was not moved. <a href="%s">View log</a> for details.')
        );

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $naString = Mage::helper('M2ePro')->__('N/A');
        $assignString = Mage::helper('M2ePro')->__('Assign Rakuten.com SKU');

        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

        $searchBuyComSkuManual = $this->getUrl('*/adminhtml_buy_listing/searchBuyComSkuManual');
        $suggestedBuyComSkuGridHmtl = $this->getUrl('*/adminhtml_buy_listing/getSuggestedBuyComSkuGrid');
        $searchBuyComSkuAuto = $this->getUrl('*/adminhtml_buy_listing/searchBuyComSkuAuto');
        $mapToBuyComSku = $this->getUrl('*/adminhtml_buy_listing/mapToBuyComSku');
        $unmapFromBuyComSku = $this->getUrl('*/adminhtml_buy_listing/unmapFromBuyComSku');

        $newGeneralId = $this->getUrl('*/adminhtml_buy_template_newProduct');

        $marketplaceId = Mage::helper('M2ePro')->getGlobalValue('marketplace_id');
        $marketplaceInstance = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Marketplace',$marketplaceId);
        $marketplace = json_encode($marketplaceInstance->getData());

        $isMarketplaceSynchronized = json_encode($marketplaceInstance->getChildObject()->isSynchronized());
        $marketplaceSynchUrl = $this->getUrl(
            '*/adminhtml_marketplace/index',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Marketplace::TAB_ID_RAKUTEN)
        );
        $notSynchronizedMarketplace = $helper->escapeJs(
            $helper->__(
                'In order to use New SKU functionality, please re-synchronize marketplace data.'
            ).' '.
                $helper->__(
                    'Press "Save And Update" button after redirect on marketplace page.'
                )
        );

        $enterProductSearchQueryMessage = $helper->escapeJs(
            $helper->__('Please enter product name or Rakuten.com SKU/Seller SKU/ISBN/UPC.')
        );
        $autoMapBuyComSkuProgressTitle = $helper->escapeJs($helper->__('Assign Rakuten.com SKU to Item(s)'));
        $autoMapBuyComSkuErrorMessage = $helper->escapeJs(
            $helper->__('Server is currently unavailable. Please try again later.')
        );

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.checkLockListing = '{$checkLockListing}';
    M2ePro.url.lockListingNow = '{$lockListingNow}';
    M2ePro.url.unlockListingNow = '{$unlockListingNow}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';

    M2ePro.url.searchBuyComSkuManual = '{$searchBuyComSkuManual}';
    M2ePro.url.searchBuyComSkuAuto = '{$searchBuyComSkuAuto}';
    M2ePro.url.suggestedBuyComSkuGrid = '{$suggestedBuyComSkuGridHmtl}';
    M2ePro.url.mapToBuyComSku = '{$mapToBuyComSku}';
    M2ePro.url.unmapFromBuyComSku = '{$unmapFromBuyComSku}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2ePro.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.url.newGeneralId = '{$newGeneralId}';
    M2ePro.url.marketplace_synch = '{$marketplaceSynchUrl}';

    M2ePro.text.popup_title = '{$popupTitle}';
    M2ePro.text.failed_products_popup_title = '{$failedProductsPopupTitle}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToBuyMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.add_new_sku_selected_items_message = '{$addNewSkuSelectedItemsMessage}';

    M2ePro.text.successfully_moved = '{$successfullyMovedMessage}';
    M2ePro.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2ePro.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';

    M2ePro.text.na = '{$naString}';
    M2ePro.text.assign = '{$assignString}';

    M2ePro.text.confirm = '{$textConfirm}';

    M2ePro.text.enter_productSearch_query = '{$enterProductSearchQueryMessage}';
    M2ePro.text.automap_buy_com_sku_progress_title = '{$autoMapBuyComSkuProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapBuyComSkuErrorMessage}';

    M2ePro.text.not_synchronized_marketplace = '{$notSynchronizedMarketplace}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    M2ePro.customData.marketplace = {$marketplace};
    M2ePro.customData.isMarketplaceSynchronized = {$isMarketplaceSynchronized};

    Event.observe(window, 'load', function() {
        ListingActionHandlerObj = new ListingActionHandler(M2ePro,{$listingData['id']});
        BuyListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2ePro);
        BuyListingProductSearchHandlerObj = new BuyListingProductSearchHandler(M2ePro);
        ListingItemGridHandlerObj = new ListingItemGridHandler( M2ePro,
                                                                'buyListingViewGrid{$listingData['id']}',
                                                                1,
                                                                2,
                                                                ListingActionHandlerObj,
                                                                BuyListingMoveToListingHandlerObj,
                                                                BuyListingProductSearchHandlerObj,
                                                                undefined);
        ListingItemGridHandlerObj.setMaxProductsInPart(100,0);
        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        $$('.edit_template_drop_down')[0].innerHTML += '{$tempDropDownHtml}';
        $$('.add_products_drop_down')[0].innerHTML += '{$tempAddProductsDropDownHtml}';

        DropDownObj = new DropDown();
        DropDownObj.prepare($$('.edit_template_drop_down')[0]);
        DropDownObj.prepare($$('.add_products_drop_down')[0]);

        if (M2ePro.productsIdsForList) {
            eval(ListingItemGridHandlerObj.gridId+'_massactionJsObject.checkedString = M2ePro.productsIdsForList;');
            $$('select#'+ListingItemGridHandlerObj.gridId+'_massaction-select option')[1].selected = 'selected';
            ListingItemGridHandlerObj.massactionSubmitClick(true);
        }
    });

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_view_help');
        $productSearchMenuBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_productSearch_menu');
        $productSearchBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_productSearch_main');

        return $javascriptsMain .
            $helpBlock->toHtml() .
            $productSearchMenuBlock->toHtml() .
            $productSearchBlock->toHtml() .
            parent::getGridHtml();
    }

    public function getEditTemplateDropDownHtml()
    {
        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $sellingFormatTemplate = Mage::helper('M2ePro')->__('Selling Format Template');
        $sellingFormatUrl = $this->getUrl('*/adminhtml_buy_template_sellingFormat/edit', array(
            'id' => $listingData['template_selling_format_id']
        ));
        $synchronizationTemplate = Mage::helper('M2ePro')->__('Synchronization Template');
        $synchronizationUrl = $this->getUrl('*/adminhtml_buy_template_synchronization/edit', array(
            'id' => $listingData['template_synchronization_id']
        ));

        return <<<HTML
<ul style="display: none;">
    <li href="{$sellingFormatUrl}" target="_blank">{$sellingFormatTemplate}</li>
    <li href="{$synchronizationUrl}" target="_blank">{$synchronizationTemplate}</li>
</ul>
HTML;
    }

    public function getAddProductsDropDownHtml()
    {
        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $fromProductsListUrl = $this->getUrl('*/adminhtml_buy_listing/product',array(
            'id'=>$listingData['id'],
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('view',array(
                'id'=>$listingData['id']
            ))
        ));
        $fromProductsList = Mage::helper('M2ePro')->__('From Products List');

        $fromCategoriesUrl = $this->getUrl('*/adminhtml_buy_listing/categoryProduct',array(
            'id'=>$listingData['id'],
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('view',array(
                'id'=>$listingData['id']
            ))
        ));
        $fromCategories = Mage::helper('M2ePro')->__('From Categories');

        return <<<HTML
<ul style="display: none;">
    <li href="{$fromProductsListUrl}">{$fromProductsList}</li>
    <li href="{$fromCategoriesUrl}">{$fromCategories}</li>
</ul>
HTML;
    }

}
