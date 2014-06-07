<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 */
class Ess_M2ePro_Model_Order_Item extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ->__('Product does not exist.');
    // ->__('Product is disabled.');
    // ->__('Order Import does not support product type: %type%.');

    // ########################################

    const STATE_NORMAL          = 0;
    const STATE_ACTION_REQUIRED = 1;

    // ########################################

    /** @var Ess_M2ePro_Model_Order */
    private $order = NULL;

    /** @var Ess_M2ePro_Model_Magento_Product */
    private $magentoProduct = NULL;

    private $proxy = NULL;

    private static $supportedProductTypes = array(
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
        Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
    );

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Item');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return $this->getChildObject()->isLocked();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->order = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    public function getState()
    {
        return (int)$this->getData('state');
    }

    public function getQtyReserved()
    {
        return (int)$this->getData('qty_reserved');
    }

    public function setAssociatedOptions(array $options)
    {
        $this->setSetting('product_details', 'associated_options', $options);
        return $this;
    }

    public function getAssociatedOptions()
    {
        return $this->getSetting('product_details', 'associated_options', array());
    }

    public function setAssociatedProducts(array $products)
    {
        $this->setSetting('product_details', 'associated_products', $products);
        return $this;
    }

    public function getAssociatedProducts()
    {
        return $this->getSetting('product_details', 'associated_products', array());
    }

    public function setReservedProducts(array $products)
    {
        $this->setSetting('product_details', 'reserved_products', $products);
        return $this;
    }

    public function getReservedProducts()
    {
        return $this->getSetting('product_details', 'reserved_products', array());
    }

    /**
     * Checks whether an order item has the data (variation info, sku etc), by which variations can be repaired
     *
     * @return bool
     */
    public function hasRepairInput()
    {
        $repairInput = $this->getChildObject()->getRepairInput();

        return count($repairInput) > 0;
    }

    // ########################################

    /**
     * Mark order item as one that requires user action
     *
     * @param $required
     * @return $this
     */
    public function setActionRequired($required)
    {
        $this->setData('state', $required ? self::STATE_ACTION_REQUIRED : self::STATE_NORMAL);
        return $this;
    }

    public function isActionRequired()
    {
        return $this->getState() == self::STATE_ACTION_REQUIRED;
    }

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            $this->order = Mage::helper('M2ePro/Component')
                ->getComponentObject($this->getComponentMode(), 'Order', $this->getOrderId());
        }

        return $this->order;
    }

    // ########################################

    public function setProduct($product)
    {
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $this->magentoProduct = null;
            return $this;
        }

        if (is_null($this->magentoProduct)) {
            $this->magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        }
        $this->magentoProduct->setProduct($product);

        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->getProductId())) {
            return NULL;
        }

        return $this->getMagentoProduct()->getProduct();
    }

    public function getMagentoProduct()
    {
        if (is_null($this->getProductId())) {
            return NULL;
        }

        if (is_null($this->magentoProduct)) {
            $this->magentoProduct = Mage::getModel('M2ePro/Magento_Product');
            $this->magentoProduct
                ->setStoreId($this->getOrder()->getStoreId())
                ->setProductId($this->getProductId());
        }

        return $this->magentoProduct;
    }

    // ########################################

    public function getProxy()
    {
        if (is_null($this->proxy)) {
            $this->proxy = $this->getChildObject()->getProxy();
        }

        return $this->proxy;
    }

    // ########################################

    public function getStoreId()
    {
        $channelItem = $this->getChildObject()->getChannelItem();

        if (is_null($channelItem)) {
            return $this->getOrder()->getStoreId();
        }

        $storeId = $channelItem->getStoreId();

        if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
            return $storeId;
        }

        if (is_null($this->getProductId())) {
            return Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        $storeIds = Mage::getModel('M2ePro/Magento_Product')
            ->setProductId($this->getProductId())
            ->getStoreIds();

        if (empty($storeIds)) {
            return Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        return array_shift($storeIds);
    }

    // ########################################

    /**
     * Associate order item with product in magento
     *
     * @throws Exception
     */
    public function associateWithProduct()
    {
        if (is_null($this->getProductId()) || !$this->getMagentoProduct()->exists()) {
            $this->assignProduct($this->getChildObject()->getAssociatedProductId());
        }

        if (!in_array($this->getMagentoProduct()->getTypeId(), self::$supportedProductTypes)) {
            $this->setActionRequired(true)->save();

            $message = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'Order Import does not support product type: %type%.', array(
                    'type' => $this->getMagentoProduct()->getTypeId()
                )
            );

            throw new Exception($message);
        }

        $this->associateVariationWithOptions();

        if (!$this->getMagentoProduct()->isStatusEnabled()) {
            $this->setActionRequired(true)->save();
            throw new Exception('Product is disabled.');
        }
    }

    // ########################################

    /**
     * Associate order item variation with options of magento product
     *
     * @throws LogicException
     * @throws Exception
     */
    private function associateVariationWithOptions()
    {
        $variation = $this->getChildObject()->getVariation();
        $magentoProduct = $this->getMagentoProduct();

        // do nothing for amazon & buy & play order item, if it is mapped to product with required options,
        // but there is no information available about sold variation
        if (empty($variation)
            && ($this->isComponentModeAmazon() || $this->isComponentModeBuy() || $this->isComponentModePlay())
            && ($magentoProduct->isStrictVariationProduct() || $magentoProduct->isProductWithVariations())
        ) {
            return;
        }

        $existOptions  = $this->getAssociatedOptions();
        $existProducts = $this->getAssociatedProducts();

        if (count($existProducts) == 1
            && ($magentoProduct->isGroupedType() || $magentoProduct->isConfigurableType())
        ) {
            // grouped and configurable products can have only one associated product mapped with sold variation
            // so if count($existProducts) == 1 - there is no need for further actions
            return;
        }

        /** @var $optionsFinder Ess_M2ePro_Model_Order_Item_OptionsFinder */
        $optionsFinder = Mage::getModel('M2ePro/Order_Item_OptionsFinder', $variation);
        $optionsFinder->setMagentoProduct($magentoProduct);

        try {
            $productDetails = $optionsFinder->getProductDetails();
        } catch (Exception $e) {
            $this->setActionRequired(true)->save();
            throw $e;
        }

        if (!isset($productDetails['associated_options'])) {
            return;
        }

        $existOptionsIds = array_keys($existOptions);
        $foundOptionsIds = array_keys($productDetails['associated_options']);

        if (count($existOptions) == 0 && count($existProducts) == 0) {
            // options mapping invoked for the first time, use found options
            $this->setAssociatedOptions($productDetails['associated_options']);

            if (isset($productDetails['associated_products'])) {
                $this->setAssociatedProducts($productDetails['associated_products']);
            }

            if ($optionsFinder->hasFailedOptions()) {
                $this->setActionRequired(true)->save();

                throw new LogicException(
                    sprintf('Product option(s) "%s" not found.', implode(', ', $optionsFinder->getFailedOptions()))
                );
            }

            $this->save();

            return;
        }

        if (count(array_diff($foundOptionsIds, $existOptionsIds)) > 0) {
            // options were already mapped, but not all of them
            $this->setActionRequired(true)->save();

            throw new LogicException('Selected options do not match the product options.');
        }
    }

    // ########################################

    public function assignProduct($productId)
    {
        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);

        $associatedProducts = array();
        $associatedOptions  = array();

        if (!$magentoProduct->exists()) {
            $this->setData('product_id', null);
            $this->setAssociatedProducts($associatedProducts);
            $this->setAssociatedOptions($associatedOptions);
            $this->setActionRequired(true);
            $this->save();

            throw new InvalidArgumentException('Product does not exist.');
        }

        $this->setData('product_id', (int)$productId);

        if ($this->hasRepairInput()) {
            $orderRepairHash = Ess_M2ePro_Model_Order_Repair::generateHash($this->getChildObject()->getRepairInput());

            /** @var $orderRepair Ess_M2ePro_Model_Order_Repair */
            $orderRepair = Mage::getModel('M2ePro/Order_Repair')
                ->getCollection()
                    ->addFieldToFilter('type', Ess_M2ePro_Model_Order_Repair::TYPE_VARIATION)
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('component', $this->getComponentMode())
                    ->addFieldToFilter('hash', $orderRepairHash)
                    ->getFirstItem();

            if ($orderRepair->getId()) {
                $productDetails = $orderRepair->getOutputData();

                $associatedOptions  = $productDetails['associated_options'];
                $associatedProducts = $productDetails['associated_products'];
            }
        }

        $this->setAssociatedProducts($associatedProducts);
        $this->setAssociatedOptions($associatedOptions);
        $this->save();
    }

    // ########################################

    public function assignProductDetails(array $associatedOptions, array $associatedProducts)
    {
        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($this->getProductId());

        if (!$magentoProduct->exists()) {
            throw new LogicException('Product does not exist.');
        }

        if (count($associatedProducts) == 0
            || (!$magentoProduct->isGroupedType() && count($associatedOptions) == 0)
        ) {
            throw new InvalidArgumentException('Required options were not selected.');
        }

        if ($magentoProduct->isGroupedType()) {
            $associatedOptions = array();
            $associatedProducts = reset($associatedProducts);
        }

        /** @var $optionsFinder Ess_M2ePro_Model_Order_Item_OptionsFinder */
        $optionsFinder = Mage::getModel('M2ePro/Order_Item_OptionsFinder');
        $optionsFinder->setMagentoProduct($this->getMagentoProduct());

        $associatedProducts = $optionsFinder->prepareAssociatedProducts($associatedProducts);

        $this->setAssociatedProducts($associatedProducts);
        $this->setAssociatedOptions($associatedOptions);
        $this->setActionRequired(false);
        $this->save();
    }

    // ########################################

    public function unassignProduct()
    {
        $this->setData('product_id', null);
        $this->setAssociatedProducts(array());
        $this->setAssociatedOptions(array());
        $this->save();
    }

    // ########################################
}
