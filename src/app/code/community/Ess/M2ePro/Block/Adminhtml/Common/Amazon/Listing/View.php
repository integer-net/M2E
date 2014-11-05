<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_listing_view';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $headerText = Mage::helper('M2ePro')->__(
                'View %component_name% Listing "%listing_title%"',
                Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE),
                $this->escapeHtml($listingData['title'])
            );
        } else {
            $headerText = Mage::helper('M2ePro')->__(
                'View Listing "%listing_title%"', $this->escapeHtml($listingData['title'])
            );
        }

        $this->_headerText = $headerText;
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

        if (!is_null($this->getRequest()->getParam('back'))) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_listing/index');
            $this->_addButton('back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonHandlerObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            ));
            //------------------------------
        }

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
            )
        );
        $this->_addButton('goto_listings', array(
            'label'   => Mage::helper('M2ePro')->__('Listings'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class'   => 'button_link'
        ));
        //------------------------------

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_common_amazon_listing/view',
            array(
                'id' => $listingData['id']
            )
        );

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/listing', array('id' => $listingData['id']));
        $this->_addButton('view_log', array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'CommonHandlerObj.reset_click()',
            'class'   => 'reset'
        ));
        //------------------------------

        $newListing = $this->getRequest()->getParam('new');
        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        //------------------------------
        if (is_null($newListing)) {
            $url = $this->getUrl(
                '*/adminhtml_listing/clearLog',
                array(
                    'id' => $listingData['id'],
                    'back' => $backUrl
                )
            );
            $this->_addButton('clear_log', array(
                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                'onclick' => 'deleteConfirm(\'' . $tempStr . '\', \'' . $url . '\')',
                'class'   => 'clear_log'
            ));
        }
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_amazon_listing/delete', array('id' => $listingData['id']));
        $this->_addButton('delete', array(
            'label'   => Mage::helper('M2ePro')->__('Delete'),
            'onclick' => 'deleteConfirm(\'' . $tempStr . '\', \'' . $url . '\')',
            'class'   => 'delete'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('edit_templates', array(
            'label'   => Mage::helper('M2ePro')->__('Edit Templates'),
            'onclick' => '',
            'class'   => 'drop_down edit_template_drop_down'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_listing/edit',
            array(
                'id'=>$listingData['id'],
                'back'=>$backUrl
            )
        );
        $this->_addButton('edit_settings', array(
            'label'     => Mage::helper('M2ePro')->__('Edit Settings'),
            'onclick'   => 'window.open(\'' . $url . '\',\'_blank\')',
            'class'     => ''
        ));
        //------------------------------

        //------------------------------
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
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        //------------------------------
        $urls = array();

        $path = 'adminhtml_common_log/listing';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'id' => $listingData['id'],
            'back'=>$helper->makeBackUrlParam('*/adminhtml_common_amazon_listing/view',array('id' =>$listingData['id']))
        ));

        $path = 'adminhtml_common_listing/duplicateProducts';
        $urls[$path] = $this->getUrl('*/' . $path);

        $urls = json_encode($urls);
        //------------------------------

        // todo next (change)

        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = 'amazonListingViewGrid' . $listingData['id'];
        $ignoreListings = json_encode(array($listingData['id']));

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');
        $marketplaceInstance = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Marketplace',$marketplaceId);
        $marketplace = json_encode($marketplaceInstance->getData());
        $isNewAsinAvailable = json_encode($marketplaceInstance->getChildObject()->isNewAsinAvailable());
        $isMarketplaceSynchronized = json_encode($marketplaceInstance->getChildObject()->isSynchronized());

        $logViewUrl = $this->getUrl('*/adminhtml_common_log/listing',array(
            'id' => $listingData['id'],
            'back' => $helper->makeBackUrlParam('*/adminhtml_common_amazon_listing/view',
                                                array('id' =>$listingData['id']))
        ));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runStopAndRemoveProducts');
        $runDeleteAndRemoveProducts = $this->getUrl('*/adminhtml_common_amazon_listing/runDeleteAndRemoveProducts');

        $prepareData = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $marketplaceSynchUrl = $this->getUrl(
            '*/adminhtml_common_marketplace/index',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON)
        );

        $getVariationEditPopupUrl = $this->getUrl('*/adminhtml_common_listing/getVariationEditPopup');
        $getVariationManagePopupUrl = $this->getUrl('*/adminhtml_common_listing/getVariationManagePopup');

        $variationEditActionUrl = $this->getUrl('*/adminhtml_common_listing/variationEdit');
        $variationManageActionUrl = $this->getUrl('*/adminhtml_common_listing/variationManage');
        $variationManageGenerateActionUrl = $this->getUrl('*/adminhtml_common_listing/variationManageGenerate');

        $popupTitle = $helper->escapeJs($helper->__('Moving Amazon Items.'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" task has successfully submitted to be processed.')
        );
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__(
            '"%task_title%" task has completed with warnings. <a target="_blank" href="%url%">View log</a> for details.'
        ));
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__(
            '"%task_title%" task has completed with errors. <a target="_blank" href="%url%">View log</a> for details.'
        ));

        $lockedObjNoticeMessage = $helper->escapeJs($helper->__('Some Amazon request(s) are being processed now.'));
        $sendingDataToAmazonMessage = $helper->escapeJs($helper->__(
            'Sending %product_title% product(s) data on Amazon.')
        );
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
                                                        ->__('Listing All Items On Amazon'));
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
                                                             ->__('Listing Selected Items On Amazon'));
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
                                                              ->__('Revising Selected Items On Amazon'));
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
                                                               ->__('Relisting Selected Items On Amazon'));
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
                                                              ->__('Stopping Selected Items On Amazon'));
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
                                                ->escapeJs(Mage::helper('M2ePro')
                                                ->__('Stopping On Amazon And Removing From Listing Selected Items'));
        $deletingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
                                                    ->escapeJs(Mage::helper('M2ePro')
                                                    ->__('Removing From Amazon And Listing Selected Items'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Product(s) was not moved. <a target="_blank" href="%url%">View log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some product(s) was not moved. <a target="_blank" href="%url%">View log</a> for details.')
        );

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the products you want to perform the action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $assignString = Mage::helper('M2ePro')->__('Assign ASIN/ISBN');
        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

        $searchAsinManual = $this->getUrl('*/adminhtml_common_amazon_listing/searchAsinManual');
        $suggestedAsinGridHmtl = $this->getUrl('*/adminhtml_common_amazon_listing/getSuggestedAsinGrid');
        $searchAsinAuto = $this->getUrl('*/adminhtml_common_amazon_listing/searchAsinAuto');
        $mapToAsin = $this->getUrl('*/adminhtml_common_amazon_listing/mapToAsin');
        $unmapFromAsin = $this->getUrl('*/adminhtml_common_amazon_listing/unmapFromAsin');

        $newAsinUrl = $this->getUrl('*/adminhtml_common_amazon_template_newProduct',array(
            'marketplace_id' => Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id'),
        ));

        $enterProductSearchQueryMessage = $helper->escapeJs(
            $helper->__('Please enter product name or ASIN/ISBN/UPC/EAN.')
        );
        $autoMapAsinProgressTitle = $helper->escapeJs($helper->__('Assign ASIN/ISBN to Item(s)'));
        $autoMapAsinErrorMessage = $helper->escapeJs(
            $helper->__('Server is currently unavailable. Please try again later.')
        );
        $newAsinNotAvailable = $helper->escapeJs(
            $helper->__('The new ASIN creation functionality is not available in %code% marketplace yet.')
        );
        $notSynchronizedMarketplace = $helper->escapeJs(
            $helper->__(
                'In order to use New ASIN functionality, please re-synchronize marketplace data.'
            ).' '.
            $helper->__(
                'Press "Save And Update" button after redirect on marketplace page.'
            )
        );

        $noVariationsLeftText = $helper->__('All variations are already added.');

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.url.add({$urls});

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';
    M2ePro.url.runDeleteAndRemoveProducts = '{$runDeleteAndRemoveProducts}';

    M2ePro.url.searchAsinManual = '{$searchAsinManual}';
    M2ePro.url.searchAsinAuto = '{$searchAsinAuto}';
    M2ePro.url.suggestedAsinGrid = '{$suggestedAsinGridHmtl}';
    M2ePro.url.mapToAsin = '{$mapToAsin}';
    M2ePro.url.unmapFromAsin = '{$unmapFromAsin}';

    M2ePro.url.newAsin = '{$newAsinUrl}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2ePro.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.url.marketplace_synch = '{$marketplaceSynchUrl}';

    M2ePro.url.get_variation_edit_popup = '{$getVariationEditPopupUrl}';
    M2ePro.url.get_variation_manage_popup = '{$getVariationManagePopupUrl}';

    M2ePro.url.variation_edit_action = '{$variationEditActionUrl}';
    M2ePro.url.variation_manage_action = '{$variationManageActionUrl}';
    M2ePro.url.variation_manage_generate_action = '{$variationManageGenerateActionUrl}';

    M2ePro.text.popup_title = '{$popupTitle}';
    M2ePro.text.failed_products_popup_title = '{$failedProductsPopupTitle}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToAmazonMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.deleting_and_removing_selected_items_message = '{$deletingAndRemovingSelectedItemsMessage}';

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

    M2ePro.text.assign = '{$assignString}';
    M2ePro.text.confirm = '{$textConfirm}';

    M2ePro.text.enter_productSearch_query = '{$enterProductSearchQueryMessage}';
    M2ePro.text.automap_asin_progress_title = '{$autoMapAsinProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapAsinErrorMessage}';

    M2ePro.text.new_asin_not_available = '{$newAsinNotAvailable}';
    M2ePro.text.not_synchronized_marketplace = '{$notSynchronizedMarketplace}';

    M2ePro.text.no_variations_left = '{$noVariationsLeftText}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    M2ePro.customData.marketplace = {$marketplace};
    M2ePro.customData.isNewAsinAvailable = {$isNewAsinAvailable};
    M2ePro.customData.isMarketplaceSynchronized = {$isMarketplaceSynchronized};

    Event.observe(window, 'load', function() {

        ListingGridHandlerObj = new AmazonListingGridHandler(
            'amazonListingViewGrid{$listingData['id']}',
            {$listingData['id']}
        );

        // todo next
        ListingGridHandlerObj.actionHandler.setOptions(M2ePro);
        ListingGridHandlerObj.movingHandler.setOptions(M2ePro);
        ListingGridHandlerObj.productSearchHandler.setOptions(M2ePro);

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        ListingProductVariationHandlerObj = new ListingProductVariationHandler(M2ePro,
                                                                               ListingGridHandlerObj);

        if (M2ePro.productsIdsForList) {
            ListingGridHandlerObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            ListingGridHandlerObj.actionHandler.listAction();
        }

    });

</script>
JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_view_help');
        $productSearchMenuBlock = $this->getLayout()
                                       ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_menu');
        $productSearchBlock = $this->getLayout()
                                   ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_main');

        //------------------------------
        $data = array(
            'target_css_class' => 'edit_template_drop_down',
            'items'            => $this->getTemplatesButtonDropDownItems()
        );
        $templatesDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $templatesDropDownBlock->setData($data);
        //------------------------------

        //------------------------------
        $data = array(
            'target_css_class' => 'add_products_drop_down',
            'items'            => $this->getAddProductsDropDownItems()
        );
        $addProductsDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $addProductsDropDownBlock->setData($data);
        //------------------------------

        return $javascriptsMain
            . $templatesDropDownBlock->toHtml()
            . $addProductsDropDownBlock->toHtml()
            . $helpBlock->toHtml()
            . $productSearchMenuBlock->toHtml()
            . $productSearchBlock->toHtml()
            . parent::getGridHtml();
    }

    protected function getTemplatesButtonDropDownItems()
    {
        $items = array();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_template_sellingFormat/edit',
            array(
                'id' => $listingData['template_selling_format_id']
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Selling Format Template'),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_template_synchronization/edit',
            array(
                'id' => $listingData['template_synchronization_id']
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Synchronization Template'),
            'target' => '_blank'
        );
        //------------------------------

        return $items;
    }

    public function getAddProductsDropDownItems()
    {
        $items = array();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('view', array('id' => $listingData['id']));

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_listing/product',
            array(
                'id' => $listingData['id'],
                'back' => $backUrl
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Products List')
        );
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_listing/categoryProduct',
            array(
                'id' => $listingData['id'],
                'back' => $backUrl
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Categories')
        );
        //------------------------------

        return $items;
    }
}