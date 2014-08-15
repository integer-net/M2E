<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Order
{
    //####################################

    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $magentoOrder */
        $magentoOrder = $observer->getEvent()->getOrder();

        /** @var Ess_M2ePro_Model_Order $order */
        $order = $magentoOrder->getData(Ess_M2ePro_Model_Order::ADDITIONAL_DATA_KEY_IN_ORDER);
        if (empty($order)) {
            return;
        }

        if ($order->getData('magento_order_id') == $magentoOrder->getId()) {
            return;
        }

        $order->setData('magento_order_id', $magentoOrder->getId());
        $order->save();

        $order->afterCreateMagentoOrder();
    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer)
    {
        try {

            /* @var $quoteItem Mage_Sales_Model_Quote_Item */
            $quoteItem = $observer->getEvent()->getItem();

            // skip qty changes when it was reserved
            $reservationTempKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_order_reservation';
            $reservationTempValue = $quoteItem->getData($reservationTempKey);
            if (!is_null($reservationTempValue)) {
                return;
            }

            /* @var $product Mage_Catalog_Model_Product */
            $product = $quoteItem->getProduct();

            if (!($product instanceof Mage_Catalog_Model_Product) ||
                (int)$product->getId() <= 0) {
                return;
            }

            // Get listings products, other listings where is product
            $listingsProductsArray = Mage::getResourceModel('M2ePro/Listing_Product')
                                                ->getItemsByProductId($product->getId());
            $otherListingsArray = Mage::getResourceModel('M2ePro/Listing_Other')
                                                ->getItemsByProductId($product->getId());

            if (count($listingsProductsArray) > 0 || count($otherListingsArray) > 0) {

                $qtyOld = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();

                if ($qtyOld <= 0) {
                    return;
                }

                // Save global changes
                //--------------------
                Mage::getModel('M2ePro/ProductChange')
                                ->addUpdateAction( $product->getId(),
                                                    Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );
                //--------------------

                // Save changes for qty
                //--------------------
                $qtyNew = $qtyOld - (int)$quoteItem->getTotalQty();

                $rez = Mage::getModel('M2ePro/ProductChange')
                         ->updateAttribute($product->getId(), 'qty',
                                           $qtyOld, $qtyNew,
                                           Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

                if ($rez !== false && $qtyNew != $qtyOld) {

                      foreach ($listingsProductsArray as $listingProductArray) {

                             $tempLog = Mage::getModel('M2ePro/Listing_Log');
                             $tempLog->setComponentMode($listingProductArray['component_mode']);
                             $tempLog->addProductMessage(
                                $listingProductArray['object']->getListingId(),
                                $product->getId(),
                                $listingProductArray['id'],
                                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                NULL,
                                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                                // M2ePro_TRANSLATIONS
                                // From [%from%] to [%to%]
                                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                    'From [%from%] to [%to%]',array('!from'=>$qtyOld,'!to'=>$qtyNew)
                                ),
                                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                             );
                      }

                      foreach ($otherListingsArray as $otherListingTemp) {

                             $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
                             $tempLog->setComponentMode($otherListingTemp['component_mode']);
                             $tempLog->addProductMessage(
                                $otherListingTemp['id'],
                                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                NULL,
                                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_QTY,
                                // M2ePro_TRANSLATIONS
                                // From [%from%] to [%to%]
                                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                    'From [%from%] to [%to%]',array('!from'=>$qtyOld,'!to'=>$qtyNew)
                                ),
                                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                             );
                      }
                }
                //--------------------

                // Save changes for stock Availability
                //--------------------
                $stockAvailabilityOld = (bool)Mage::getModel('cataloginventory/stock_item')
                                                    ->loadByProduct($product)->getIsInStock();
                $stockAvailabilityNew = !($qtyNew <= (int)Mage::getModel('cataloginventory/stock_item')
                                                    ->getMinQty());

                $rez = Mage::getModel('M2ePro/ProductChange')
                                 ->updateAttribute($product->getId(), 'stock_availability',
                                                   (int)$stockAvailabilityOld, (int)$stockAvailabilityNew,
                                                   Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

                if ($rez !== false && $stockAvailabilityOld != $stockAvailabilityNew) {

                      $stockAvailabilityOld = $stockAvailabilityOld ? 'IN Stock' : 'OUT of Stock';
                      $stockAvailabilityNew = $stockAvailabilityNew ? 'IN Stock' : 'OUT of Stock';

                      foreach ($listingsProductsArray as $listingProductArray) {

                             $tempLog = Mage::getModel('M2ePro/Listing_Log');
                             $tempLog->setComponentMode($listingProductArray['component_mode']);
                             $tempLog->addProductMessage(
                                $listingProductArray['object']->getListingId(),
                                $product->getId(),
                                $listingProductArray['id'],
                                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                NULL,
                                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                // M2ePro_TRANSLATIONS
                                // From [%from%] to [%to%]
                                // IN Stock
                                // OUT of Stock
                                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                    'From [%from%] to [%to%]',
                                    array('from'=>$stockAvailabilityOld,'to'=>$stockAvailabilityNew)
                                ),
                                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                             );
                      }

                      foreach ($otherListingsArray as $otherListingTemp) {

                             $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
                             $tempLog->setComponentMode($otherListingTemp['component_mode']);
                             $tempLog->addProductMessage(
                                $otherListingTemp['id'],
                                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                NULL,
                                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                // M2ePro_TRANSLATIONS
                                // From [%from%] to [%to%]
                                // IN Stock
                                // OUT of Stock
                                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                    'From [%from%] to [%to%]',
                                    array('from'=>$stockAvailabilityOld,'to'=>$stockAvailabilityNew)
                                ),
                                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                             );
                      }
                }
                //--------------------
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################
}