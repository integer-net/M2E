<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const VIEW_MODE_EBAY        = 'ebay';
    const VIEW_MODE_MAGENTO     = 'magento';
    const VIEW_MODE_SETTINGS    = 'settings';
    const VIEW_MODE_TRANSLATION = 'translation';

    const DEFAULT_VIEW_MODE = self::VIEW_MODE_EBAY;

    /** @var Ess_M2ePro_Model_Listing */
    private $listing = NULL;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        $this->listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('ebayListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_view_' . $this->getViewMode();
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
        $this->_headerText = Mage::helper('M2ePro')->__('View Listing');
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_log/listing',
            array(
                'id'   => $this->listing->getId()
            )
        );
        $this->_addButton('view_log', array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\',\'_blank\')',
            'class'   => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('edit_templates', array(
            'label'   => Mage::helper('M2ePro')->__('Edit Listing Settings'),
            'onclick' => '',
            'class'   => 'drop_down edit_default_settings_drop_down'
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

    // ########################################

    public function getViewMode()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return self::VIEW_MODE_EBAY;
        }

        $allowedModes = array(
            self::VIEW_MODE_EBAY,
            self::VIEW_MODE_MAGENTO,
            self::VIEW_MODE_SETTINGS,
            self::VIEW_MODE_TRANSLATION,
        );
        $mode = $this->getParam('view_mode', self::DEFAULT_VIEW_MODE);

        if (in_array($mode, $allowedModes)) {
            return $mode;
        }

        return self::DEFAULT_VIEW_MODE;
    }

    protected function getParam($paramName, $default = NULL)
    {
        $session = Mage::helper('M2ePro/Data_Session');
        $sessionParamName = $this->getId() . $this->listing->getId() . $paramName;

        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            $session->setValue($sessionParamName, $param);
            return $param;
        } elseif ($param = $session->getValue($sessionParamName)) {
            return $param;
        }

        return $default;
    }

    // ########################################

    public function getHeaderHtml()
    {
        //------------------------------
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collection->addFieldToFilter('id', array('neq' => $this->listing->getId()));
        $collection->setPageSize(200);
        $collection->setOrder('title', 'ASC');

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[] = array(
                'label' => $item->getTitle(),
                'url' => $this->getUrl('*/*/view', array('id' => $item->getId()))
            );
        }
        //------------------------------

        if (count($items) == 0) {
            return parent::getHeaderHtml();
        }

        //------------------------------
        $data = array(
            'target_css_class' => 'listing-profile-title',
            'style' => 'max-height: 120px; overflow: auto; width: 200px;',
            'items' => $items
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);
        //------------------------------

        return parent::getHeaderHtml() . $dropDownBlock->toHtml();
    }

    public function getHeaderText()
    {
        //------------------------------
        $changeProfile = Mage::helper('M2ePro')->__('Change Listing');
        $headerText = parent::getHeaderText();
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($this->listing->getTitle());
        //------------------------------

        return <<<HTML
{$headerText}&nbsp;
<a href="javascript: void(0);"
   id="listing-profile-title"
   class="listing-profile-title"
   style="font-weight: bold;"
   title="{$changeProfile}"><span class="drop_down_header">"{$listingTitle}"</span></a>
HTML;
    }

    // ########################################

    protected  function _toHtml()
    {
        return '<div id="listing_view_progress_bar"></div>'.
               '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>'.
               '<div id="listing_view_content_container">'.
               parent::_toHtml().
               '</div>';
    }

    // ########################################

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $html = '';

        //------------------------------
        $data = array(
            'target_css_class' => 'edit_default_settings_drop_down',
            'items'            => $this->getDefaultSettingsButtonDropDownItems()
        );
        $templatesDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $templatesDropDownBlock->setData($data);

        $html .= $templatesDropDownBlock->toHtml();
        //------------------------------

        //------------------------------
        $listingSwitcher = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_listingSwitcher');

        $html .= $listingSwitcher->toHtml();
        //------------------------------

        //------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_help');

        $html .= $helpBlock->toHtml();
        //------------------------------

        //------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => Mage::helper('M2ePro/Data_Global')->getValue('temp_data'))
        );

        $html .= $viewHeaderBlock->toHtml();
        //------------------------------

        //------------------------------
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            if ($this->listing->getChildObject()->isEstimatedFeesObtainRequired()) {
                $obtain = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_obtain');
                $obtain->setData('listing_id', $this->listing->getId());

                $html .= $obtain->toHtml();
            } elseif ($this->listing->getChildObject()->getEstimatedFees()) {
                $preview = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_preview');
                $preview->setData('fees', $this->listing->getChildObject()->getEstimatedFees());
                $preview->setData('product_name',
                                  $this->listing->getChildObject()->getEstimatedFeesSourceProductName());

                $html .= $preview->toHtml();
            }
        }
        //------------------------------

        //------------------------------
        $data = array(
            'target_css_class' => 'add_products_drop_down',
            'items'            => $this->getAddProductsDropDownItems()
        );
        $addProductsDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $addProductsDropDownBlock->setData($data);
        //------------------------------

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        //------------------------------
        $urls = json_encode(array_merge(
            $helper->getControllerActions(
                'adminhtml_ebay_listing', array('_current' => true)
            ),
            $helper->getControllerActions(
                'adminhtml_ebay_listing_autoAction', array('listing_id' => $this->getRequest()->getParam('id'))
            ),
            $helper->getControllerActions(
                'adminhtml_ebay_listing_transferring', array('listing_id' => $this->getRequest()->getParam('id'))
            ),
            $helper->getControllerActions('adminhtml_ebay_account'),
            $helper->getControllerActions('adminhtml_ebay_listing_categorySettings'),
            $helper->getControllerActions('adminhtml_ebay_marketplace'),
            array('adminhtml_system_store/index' =>
                Mage::helper('adminhtml')->getUrl('adminhtml/system_store/')),
            array('logViewUrl' =>
                $this->getUrl('M2ePro/adminhtml_common_log/synchronization',
                    array('back'=>$helper->makeBackUrlParam('*/adminhtml_common_synchronization/index')))),
            array('runSynchNow' =>
                $this->getUrl('M2ePro/adminhtml_common_marketplace/runSynchNow')),
            array('synchCheckProcessingNow' =>
                $this->getUrl('M2ePro/adminhtml_common_synchronization/synchCheckProcessingNow'))
        ));
        //------------------------------

        //------------------------------
        $translations = json_encode(array(
            'Automatic Actions' => $helper->__('Automatic Actions'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 category.' => $helper->__('You must select at least 1 category.'),
            'Rule with the same title already exists.' => $helper->__('Rule with the same title already exists.'),
            'Compatibility Attribute' => $helper->__('Compatibility Attribute'),
            'Sell on Another eBay Site' => $helper->__('Sell on Another eBay Site'),
            'Translation Service' => $helper->__('Translation Service'),
            'You must select at least 1 listing.' => $helper->__('You must select at least 1 listing.'),
            'Data migration.' => $helper->__('Data migration...'),
            'Creating templates in process. Please wait...' =>
                $helper->__('Creating policies in process. Please wait...'),
            'Creating translation account in process. Please wait...' =>
                $helper->__('Creating translation account in process. Please wait...'),
            'Creating listing in process. Please wait...' =>
                $helper->__('Creating listing in process. Please wait...'),
            'Adding products in process. Please wait...' =>
                $helper->__('Adding products in process. Please wait...'),
            'Products failed to add' => $helper->__('Failed products'),
            'Migration success.' => $helper->__('The products have been successfully added into Destination listing.'),
            'Migration error.' => $helper->__('The products have not been added into Destination listing'
                                           .' because products with the same Magento Product IDs already exist there.'),
            'Some products categories settings are not set or attributes for title or description are empty.' =>
                $helper->__('Some products Categories settings are not set'
                           .' or attributes for Title or Description are empty.'),
            'Another Synchronization Is Already Running.' => $helper->__('Another Synchronization Is Already Running.'),
            'Getting information. Please wait ...' => $helper->__('Getting information. Please wait ...'),
            'Preparing to start. Please wait ...' => $helper->__('Preparing to start. Please wait ...'),
            'Synchronization has successfully ended.' => $helper->__('Synchronization has successfully ended.'),
            'Synchronization ended with warnings. <a target="_blank" href="%url%">View log</a> for details.' =>
                $helper->__(
                    'Synchronization ended with warnings. <a target="_blank" href="%url%">View log</a> for details.'),
            'Synchronization ended with errors. <a target="_blank" href="%url%">View log</a> for details.' =>
                $helper->__(
                    'Synchronization ended with errors. <a target="_blank" href="%url%">View log</a> for details.')
        ));
        //------------------------------

        $html .= <<<HTML
<script type="text/javascript">
    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
</script>
HTML;

        $javascript = '';

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $html .= <<<HTML
<script type="text/javascript">
    EbayListingAutoActionHandlerObj = new EbayListingAutoActionHandler();
</script>
HTML;
        }
        //------------------------------

        return $html .
               $addProductsDropDownBlock->toHtml() .
               parent::getGridHtml() .
               $javascript;
    }

    // ########################################

    protected function getDefaultSettingsButtonDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_template/editListing',
            array(
                'id' => $this->listing->getId(),
                'tab' => 'general'
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Payment and Shipping'),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_template/editListing',
            array(
                'id' => $this->listing->getId(),
                'tab' => 'selling'
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Selling'),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $url = $this->getUrl(
                '*/adminhtml_ebay_template/editListing',
                array(
                    'id' => $this->listing->getId(),
                    'tab' => 'synchronization'
                )
            );
            $items[] = array(
                'url' => $url,
                'label' => Mage::helper('M2ePro')->__('Synchronization'),
                'target' => '_blank'
            );
        }
        //------------------------------

        //------------------------------
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $items[] = array(
                'url' => 'javascript: void(0);',
                'onclick' => 'EbayListingAutoActionHandlerObj.loadAutoActionHtml();',
                'label' => Mage::helper('M2ePro')->__('Automatic Actions')
            );
        }
        //------------------------------

        return $items;
    }

    // ####################################

    public function getAddProductsDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = $this->getUrl('*/adminhtml_ebay_listing_productAdd',array(
            'source' => 'products',
            'clear' => true,
            'listing_id' => $this->listing->getId()
        ));
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Products List')
        );
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_ebay_listing_productAdd',array(
            'source' => 'categories',
            'clear' => true,
            'listing_id' => $this->listing->getId()
        ));
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Categories')
        );
        //------------------------------

        return $items;
    }

    // ####################################
}