<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @property Ess_M2ePro_Model_Amazon_Order_Item $item
 */
class Ess_M2ePro_Model_Amazon_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    // ########################################

    public function getGiftMessage()
    {
        if ($this->item->getGiftMessage() == '') {
            return parent::getGiftMessage();
        }

        return array(
            'sender'    => '', //$this->item->getAmazonOrder()->getBuyerName(),
            'recipient' => '', //$this->item->getAmazonOrder()->getShippingAddress()->getData('recipient_name'),
            'message'   => $this->item->getGiftMessage()
        );
    }

    // ########################################

    public function getOriginalPrice()
    {
        $price = $this->item->getPrice() + $this->item->getGiftPrice() - $this->item->getDiscountAmount();

        if ($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) {
            $taxAmount = Mage::getSingleton('tax/calculation')
                ->calcTaxAmount($price, $this->getTaxRate(), false, false);

            $price += $taxAmount;
        }

        return $price;
    }

    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    public function getAdditionalData()
    {
        if (count($this->additionalData) == 0) {
            $this->additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'order_item_id' => $this->item->getAmazonOrderItemId()
            );
        }
        return $this->additionalData;
    }

    // ########################################
}