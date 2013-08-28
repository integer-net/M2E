<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Other');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Source
     */
    public function getSourceModel()
    {
        return Mage::getSingleton('M2ePro/Ebay_Listing_Other_Source');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization
     */
    public function getSynchronizationModel()
    {
        return Mage::getSingleton('M2ePro/Ebay_Listing_Other_Synchronization');
    }

    // ########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getItemId()
    {
        return (double)$this->getData('item_id');
    }

    //-----------------------------------------

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    //-----------------------------------------

    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    public function getOnlineQtySold()
    {
        return (int)$this->getData('online_qty_sold');
    }

    public function getOnlineBids()
    {
        return (int)$this->getData('online_bids');
    }

    //-----------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    // ########################################

    public function getMappedPrice()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getMagentoProduct()->isProductWithVariations() ||
            $this->getSourceModel()->isPriceSourceNone()) {
            return NULL;
        }

        $price = 0;

        if ($this->getSourceModel()->isPriceSourceProduct()) {
            $price = $this->getMagentoProduct()->getPrice();
        }

        if ($this->getSourceModel()->isPriceSourceFinal()) {
            $customerGroupId = $this->getSourceModel()->getCustomerGroupId();
            $price = $this->getMagentoProduct()->getFinalPrice($customerGroupId);
        }

        if ($this->getSourceModel()->isPriceSourceSpecial()) {
            $price = $this->getMagentoProduct()->getSpecialPrice();
            $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
        }

        if ($this->getSourceModel()->isPriceSourceAttribute()) {
            $attribute = $this->getSourceModel()->getPriceAttribute();
            $price = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        $price < 0 && $price = 0;

        return $price;
    }

    public function getMappedQty()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getMagentoProduct()->isProductWithVariations() ||
            $this->getSourceModel()->isQtySourceNone()) {
            return NULL;
        }

        $qty = 0;

        if ($this->getSourceModel()->isQtySourceProduct()) {
            $qty = $this->getMagentoProduct()->getQty();
        }

        if ($this->getSourceModel()->isQtySourceAttribute()) {
            $attribute = $this->getSourceModel()->getQtyAttribute();
            $qty = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return (int)floor($qty);
    }

    //-----------------------------------------

    public function getMappedTitle()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getSourceModel()->isTitleSourceNone()) {
            return NULL;
        }

        $title = '';

        if ($this->getSourceModel()->isTitleSourceProduct()) {
            $title = $this->getMagentoProduct()->getName();
        }

        if ($this->getSourceModel()->isTitleSourceAttribute()) {
            $attribute = $this->getSourceModel()->getTitleAttribute();
            $title = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return $title;
    }

    public function getMappedSubTitle()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getSourceModel()->isSubTitleSourceNone()) {
            return NULL;
        }

        $subTitle = '';

        if ($this->getSourceModel()->isSubTitleSourceAttribute()) {
            $attribute = $this->getSourceModel()->getSubTitleAttribute();
            $subTitle = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return $subTitle;
    }

    public function getMappedDescription()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getSourceModel()->isDescriptionSourceNone()) {
            return NULL;
        }

        $description = '';
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        if ($this->getSourceModel()->isDescriptionSourceProductMain()) {
            $description = $this->getMagentoProduct()->getProduct()->getDescription();
            $description = $templateProcessor->filter($description);
        }

        if ($this->getSourceModel()->isDescriptionSourceProductShort()) {
            $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
            $description = $templateProcessor->filter($description);
        }

        if ($this->getSourceModel()->isDescriptionSourceAttribute()) {
            $attribute = $this->getSourceModel()->getDescriptionAttribute();
            $description = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    // ########################################

    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId($this->getParentObject()->getMarketplaceId());
    }

    // ########################################

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,$params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,$params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP,$params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $dispatcher = Mage::getModel('M2ePro/Connector_Server_Ebay_OtherItem_Dispatcher');

        return $dispatcher->process($action, $this->getId(), $params);
    }

    // ########################################

    public function afterMapProduct()
    {
        $dataForAdd = array(
            'item_id' => $this->getItemId(),
            'product_id' => $this->getParentObject()->getProductId(),
            'store_id' => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable(),
                    array(
                        '`item_id` = ?' => $this->getItemId(),
                        '`product_id` = ?' => $this->getParentObject()->getProductId()
                    ));
    }

    // ########################################
}