<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Order_Item_Proxy
{
    /** @var Ess_M2ePro_Model_Ebay_Order_Item|Ess_M2ePro_Model_Amazon_Order_Item|
     * Ess_M2ePro_Model_Buy_Order_Item|Ess_M2ePro_Model_Play_Order_Item */
    protected $item = NULL;

    protected $qty = NULL;

    protected $subtotal = NULL;

    protected $additionalData = array();

    // ########################################

    public function __construct(Ess_M2ePro_Model_Component_Child_Abstract $item)
    {
        $this->item = $item;
        $this->subtotal = $this->getOriginalPrice() * $this->getOriginalQty();
    }

    // ########################################

    public function getProxyOrder()
    {
        return $this->item->getParentObject()->getOrder()->getProxy();
    }

    // ########################################

    public function equals(Ess_M2ePro_Model_Order_Item_Proxy $that)
    {
        if (is_null($this->getProductId()) || is_null($that->getProductId())) {
            return false;
        }

        if ($this->getProductId() != $that->getProductId()) {
            return false;
        }

        $thisOptions = $this->getOptions();
        $thatOptions = $that->getOptions();

        $thisOptionsKeys = array_keys($thisOptions);
        $thatOptionsKeys = array_keys($thatOptions);

        $thisOptionsValues = array_values($thisOptions);
        $thatOptionsValues = array_values($thatOptions);

        if (count($thisOptions) != count($thatOptions)
            || count(array_diff($thisOptionsKeys, $thatOptionsKeys)) > 0
            || count(array_diff($thisOptionsValues, $thatOptionsValues)) > 0
        ) {
            return false;
        }

        // grouped products have no options, that's why we have to compare associated products
        $thisAssociatedProducts = $this->getAssociatedProducts();
        $thatAssociatedProducts = $that->getAssociatedProducts();

        if (count($thisAssociatedProducts) != count($thatAssociatedProducts)
            || count(array_diff($thisAssociatedProducts, $thatAssociatedProducts)) > 0
        ) {
            return false;
        }

        return true;
    }

    public function merge(Ess_M2ePro_Model_Order_Item_Proxy $that)
    {
        // --------
        $this->setQty($this->getQty() + $that->getOriginalQty());
        $this->subtotal += $that->getOriginalPrice() * $that->getOriginalQty();
        // --------

        // merge additional data
        // --------
        $thisAdditionalData = $this->getAdditionalData();
        $thatAdditionalData = $that->getAdditionalData();

        $identifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        $thisAdditionalData[$identifier]['items'][] = $thatAdditionalData[$identifier]['items'][0];

        $this->additionalData = $thisAdditionalData;
        // --------
    }

    // ########################################

    /**
     * Return product associated with order item
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->item->getParentObject()->getProduct();
    }

    public function getProductId()
    {
        return $this->item->getParentObject()->getProductId();
    }

    public function getMagentoProduct()
    {
        return $this->item->getParentObject()->getMagentoProduct();
    }

    // ########################################

    public function getOptions()
    {
        return $this->item->getParentObject()->getAssociatedOptions();
    }

    public function getAssociatedProducts()
    {
        return $this->item->getParentObject()->getAssociatedProducts();
    }

    // ########################################

    /**
     * Return price converted to the base store currency
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->getProxyOrder()->convertPriceToBase($this->getPrice());
    }

    /**
     * Return price in channel currency
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->subtotal / $this->getQty();
    }

    /**
     * Return item purchase price
     *
     * @abstract
     * @return float
     */
    abstract public function getOriginalPrice();

    /**
     * Return item purchase qty
     *
     * @abstract
     * @return int
     */
    abstract public function getOriginalQty();

    public function setQty($qty)
    {
        if ((int)$qty <= 0) {
            throw new InvalidArgumentException('Qty cannot be less than zero.');
        }

        $this->qty = (int)$qty;

        return $this;
    }

    public function getQty()
    {
        if (!is_null($this->qty)) {
            return $this->qty;
        }
        return $this->getOriginalQty();
    }

    /**
     * Return item tax rate
     *
     * @return float
     */
    public function getTaxRate()
    {
        return $this->item->getParentObject()->getOrder()->getProxy()->getTaxRate();
    }

    /**
     * Check whether item has Tax
     *
     * @return bool
     */
    public function hasTax()
    {
        return $this->getProxyOrder()->hasTax();
    }

    /**
     * Check whether item has VAT (value added tax)
     *
     * @return bool
     */
    public function hasVat()
    {
        return $this->getProxyOrder()->hasVat();
    }

    // ########################################

    public function getGiftMessage()
    {
        return null;
    }

    // ########################################

    abstract public function getAdditionalData();

    // ########################################
}