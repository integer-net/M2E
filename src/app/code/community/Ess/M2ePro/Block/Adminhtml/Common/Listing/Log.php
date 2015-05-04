<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Log extends Mage_Adminhtml_Block_Widget_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_log';
        //------------------------------

        //------------------------------
        $this->setTemplate('M2ePro/common/log/log.phtml');
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
        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_common_listing/index').'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        if (isset($listingData['id'])) {
            //------------------------------
            $url = Mage::helper('M2ePro/View')->getUrl(
                new Varien_Object($listingData), 'listing', 'edit', array('id' => $listingData['id'])
            );
            $this->_addButton('goto_listing_settings', array(
                'label'     => Mage::helper('M2ePro')->__('Listing Settings'),
                'onclick'   => 'setLocation(\'' .$url.'\')',
                'class'     => 'button_link'
            ));
            //------------------------------

            //------------------------------
            $url = Mage::helper('M2ePro/View')->getUrl(
                new Varien_Object($listingData), 'listing', 'view', array('id' => $listingData['id'])
            );
            $this->_addButton('goto_listing_items', array(
                'label'     => Mage::helper('M2ePro')->__('Listing Items'),
                'onclick'   => 'setLocation(\'' .$url.'\')',
                'class'     => 'button_link'
            ));
            //------------------------------
        }

        if (isset($listingData['id'])) {
            //------------------------------
            $url = $this->getUrl('*/*/*');
            $this->_addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' . $url .'\')',
                'class'     => 'show_general_log'
            ));
            //------------------------------
        }
    }

    // ########################################

    public function getListingId()
    {
        return $this->getRequest()->getParam('id', false);
    }

    // ----------------------------------------

    /** @var Ess_M2ePro_Model_Listing $listing */
    protected $listing = NULL;

    /**
     * @return Ess_M2ePro_Model_Listing|null
     */
    public function getListing()
    {
        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component')
                ->getCachedUnknownObject('Listing', $this->getListingId());
        }

        return $this->listing;
    }

    // ########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam('listing_product_id', false);
    }

    // ----------------------------------------

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct = NULL;

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    // ########################################

    protected function _beforeToHtml()
    {
        // Set header text
        //------------------------------
        $this->_headerText = '';

        if ($this->getListingId()) {

            $listing = $this->getListing();

            if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
                $component =  Mage::helper('M2ePro/Component')->getComponentTitle($listing->getComponentMode());
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For %component_name% Listing "%listing_title%"',
                    $component, $this->escapeHtml($listing->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For Listing "%listing_title%"',
                    $this->escapeHtml($listing->getTitle())
                );
            }

        } else if ($this->getListingProductId()) {

            $listingProduct = $this->getListingProduct();
            $listing = $listingProduct->getListing();

            $onlineTitle = $listingProduct->getOnlineTitle();
            if (empty($onlineTitle)) {
                $onlineTitle = $listingProduct->getMagentoProduct()->getName();
            }

            if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
                $component =  Mage::helper('M2ePro/Component')->getComponentTitle($listing->getComponentMode());
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For Product "%product_name%" (ID:%product_id%) Of %component_name% Listing "%listing_title%"',
                    $this->escapeHtml($onlineTitle),
                    $listingProduct->getProductId(),
                    $component,
                    $this->escapeHtml($listing->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Log For Product "%product_name%" (ID:%product_id%) Of Listing "%listing_title%"',
                    $this->escapeHtml($onlineTitle),
                    $listingProduct->getProductId(),
                    $this->escapeHtml($listing->getTitle())
                );
            }

        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Listings Log');
        }
        //------------------------------
    }

    // ########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_log_help')->toHtml();

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_log_tabs', '',
            array(
                'channel' => $this->getRequest()->getParam('channel'),
                'log_type' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::LOG_TYPE_ID_LISTING
            )
        )->toHtml();

        $translations = json_encode(array(
            'Description' => Mage::helper('M2ePro')->__('Description')
        ));

        $hideTabs = '';
        if ($this->getListingId() || $this->getListingProductId()) {
            $hideTabs = '$("commonLogTabs").hide();';
        }

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        LogHandlerObj = new LogHandler();
        {$hideTabs}
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml() . $helpBlock . $logBlock;
    }

    // ########################################
}