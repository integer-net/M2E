<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const VIEW_MODE_EBAY     = 'ebay';
    const VIEW_MODE_MAGENTO  = 'magento';
    const VIEW_MODE_SETTINGS = 'settings';

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

        $allowedModes = array(self::VIEW_MODE_EBAY, self::VIEW_MODE_MAGENTO, self::VIEW_MODE_SETTINGS);
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
                $preview->setData('product_name', $this->listing->getChildObject()->getEstimatedFeesSourceProductName());

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

        //------------------------------
        $urls = json_encode(array_merge(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_ebay_listing', array('_current' => true)
            ),
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_ebay_listing_autoAction', array('listing_id' => $this->getRequest()->getParam('id'))
            )
        ));
        //------------------------------

        //------------------------------
        $translations = json_encode(array(
            'Automatic Actions' => $this->__('Automatic Actions'),
            'Based on Magento Categories' => $this->__('Based on Magento Categories'),
            'You must select at least 1 category.' => $this->__('You must select at least 1 category.'),
            'Rule with the same title already exists.' => $this->__('Rule with the same title already exists.'),
            'Compatibility Attribute' => $this->__('Compatibility Attribute'),
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