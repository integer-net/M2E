<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Play_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_play_listing_view';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
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
                    array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY))
                .'\')',
            'class'   => 'button_link'
        ));

        $this->_addButton('view_log', array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'setLocation(\''
                .$this->getUrl('*/adminhtml_log/listing',
                    array('id'=>$listingData['id'],
                        'back'=>Mage::helper('M2ePro')
                            ->makeBackUrlParam('*/adminhtml_play_listing/view',
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
                                ->makeBackUrlParam('*/adminhtml_play_listing/view',
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
                .$this->getUrl('*/adminhtml_'.Ess_M2ePro_Helper_Component_Play::NICK.'_listing/delete',
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
                .$this->getUrl('*/adminhtml_play_listing/edit',
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
        $component = Ess_M2ePro_Helper_Component_Play::NICK;

        $temp = $helper->getSessionValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $listingData = $helper->getGlobalValue('temp_data');

        $gridId = 'playListingViewGrid' . $listingData['id'];
        $ignoreListings = json_encode(array($listingData['id']));

        $logViewUrl = $this->getUrl('*/adminhtml_log/listing', array(
            'id' =>$listingData['id'],
            'back'=>$helper->makeBackUrlParam('*/adminhtml_play_listing/view', array('id' =>$listingData['id']))
        ));
        $checkLockListing = $this->getUrl('*/adminhtml_listing/checkLockListing', array('component' => $component));
        $lockListingNow = $this->getUrl('*/adminhtml_listing/lockListingNow', array('component' => $component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listing/unlockListingNow', array('component' => $component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_play_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_play_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_play_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_play_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_play_listing/runStopAndRemoveProducts');

        $prepareData = $this->getUrl('*/adminhtml_listing/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_play_listing/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_play_listing/moveToListing');

        $tempDropDownHtml = $helper->escapeJs($this->getEditTemplateDropDownHtml());
        $tempAddProductsDropDownHtml = $helper->escapeJs($this->getAddProductsDropDownHtml());

        $popupTitle = $helper->escapeJs($helper->__('Moving Play.com Items.'));
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
            $helper->__('Some Play.com request(s) are being processed now.')
        );
        $sendingDataToPlayMessage = $helper->escapeJs($helper->__('Sending %s product(s) data on Play.com.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing All Items On Play.com'));
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing Selected Items On Play.com'));
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Revising Selected Items On Play.com'));
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Play.com'));
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Play.com'));
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping On Play.com And Removing From Listing Selected Items'));

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
        $assignString = Mage::helper('M2ePro')->__('Assign Play.com SKU');

        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

        $searchPlayIDManual = $this->getUrl('*/adminhtml_play_listing/searchPlayIDManual');
        $suggestedPlayIDGridHmtl = $this->getUrl('*/adminhtml_play_listing/getSuggestedPlayIDGrid');
        $searchPlayIDAuto = $this->getUrl('*/adminhtml_play_listing/searchPlayIDAuto');
        $mapToPlayID = $this->getUrl('*/adminhtml_play_listing/mapToPlayID');
        $unmapFromPlayID = $this->getUrl('*/adminhtml_play_listing/unmapFromPlayID');

        $enterProductSearchQueryMessage = $helper->escapeJs(
            $helper->__('Please enter product name or ISBN.')
        );
        $autoMapPlayIDProgressTitle = $helper->escapeJs($helper->__('Assign Play ID to Item(s)'));
        $autoMapPlayIDErrorMessage = $helper->escapeJs(
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

    M2ePro.url.searchPlayIDManual = '{$searchPlayIDManual}';
    M2ePro.url.searchPlayIDAuto = '{$searchPlayIDAuto}';
    M2ePro.url.suggestedPlayIDGrid = '{$suggestedPlayIDGridHmtl}';
    M2ePro.url.mapToPlayID = '{$mapToPlayID}';
    M2ePro.url.unmapFromPlayID = '{$unmapFromPlayID}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2ePro.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.text.popup_title = '{$popupTitle}';
    M2ePro.text.failed_products_popup_title = '{$failedProductsPopupTitle}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToPlayMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';

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
    M2ePro.text.automap_play_id_progress_title = '{$autoMapPlayIDProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapPlayIDErrorMessage}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {
        ListingActionHandlerObj = new ListingActionHandler(M2ePro,{$listingData['id']});
        PlayListingMoveToListingHandlerObj = new ListingMoveToListingHandler(M2ePro);
        PlayListingProductSearchHandlerObj = new PlayListingProductSearchHandler(M2ePro);
        ListingItemGridHandlerObj = new ListingItemGridHandler( M2ePro,
                                                                'playListingViewGrid{$listingData['id']}',
                                                                1,
                                                                2,
                                                                ListingActionHandlerObj,
                                                                PlayListingMoveToListingHandlerObj,
                                                                PlayListingProductSearchHandlerObj,
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

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_view_help');
        $productSearchMenuBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_productSearch_menu');
        $productSearchBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_play_listing_productSearch_main');

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
        $sellingFormatUrl = $this->getUrl('*/adminhtml_play_template_sellingFormat/edit', array(
            'id' => $listingData['template_selling_format_id']
        ));
        $synchronizationTemplate = Mage::helper('M2ePro')->__('Synchronization Template');
        $synchronizationUrl = $this->getUrl('*/adminhtml_play_template_synchronization/edit', array(
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

        $fromProductsListUrl = $this->getUrl('*/adminhtml_play_listing/product',array(
            'id'=>$listingData['id'],
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('view',array(
                'id'=>$listingData['id']
            ))
        ));
        $fromProductsList = Mage::helper('M2ePro')->__('From Products List');

        $fromCategoriesUrl = $this->getUrl('*/adminhtml_play_listing/categoryProduct',array(
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
