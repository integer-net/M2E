<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_StockItem
{
    //####################################

    public function catalogInventoryStockItemSaveAfter(Varien_Event_Observer $observer)
    {
        try {

             // Get product id
             $productId = $observer->getData('item')->getData('product_id');

             // skip qty changes when it was reserved
             $reservationTempKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_order_reservation';
             $reservationTempValue = $observer->getData('item')->getData($reservationTempKey);
             if (!is_null($reservationTempValue)) {
                 return;
             }

             // Get listings, other listings where is product
             $listingsProductsArray = Mage::getResourceModel('M2ePro/Listing_Product')->getItemsByProductId($productId);
             $otherListingsArray = Mage::getResourceModel('M2ePro/Listing_Other')->getItemsByProductId($productId);

             if (count($listingsProductsArray) > 0 || count($otherListingsArray) > 0) {

                    // Save global changes
                    //--------------------
                    Mage::getModel('M2ePro/ProductChange')
                                ->addUpdateAction( $productId,
                                                    Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );
                    //--------------------

                    // Save changes for qty
                    //--------------------
                    $qtyOld = (int)$observer->getData('item')->getOrigData('qty');
                    $qtyNew = (int)$observer->getData('item')->getData('qty');

                    $rez = Mage::getModel('M2ePro/ProductChange')
                                 ->updateAttribute($productId, 'qty',
                                                   $qtyOld, $qtyNew,
                                                   Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

                    if ($rez !== false && $qtyOld != $qtyNew) {

                          foreach ($listingsProductsArray as $listingProductArray) {

                                 $tempLog = Mage::getModel('M2ePro/Listing_Log');
                                 $tempLog->setComponentMode($listingProductArray['component_mode']);
                                 $tempLog->addProductMessage(
                                    $listingProductArray['object']->getListingId(),
                                    $productId,
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
                    $stockAvailabilityOld = (bool)$observer->getData('item')->getOrigData('is_in_stock');
                    $stockAvailabilityNew = (bool)$observer->getData('item')->getData('is_in_stock');

                    $rez = Mage::getModel('M2ePro/ProductChange')
                                     ->updateAttribute($productId, 'stock_availability',
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
                                    $productId,
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

    public function disableAutomaticReindex(Varien_Event_Observer $observer)
    {
        /** @var $index Ess_M2ePro_Model_Magento_Product_Index */
        $index = Mage::getSingleton('M2ePro/Magento_Product_Index');

        if (!$index->isIndexManagementEnabled()) {
            return;
        }

        foreach ($index->getIndexes() as $code) {
            if ($index->disableReindex($code)) {
                $index->rememberDisabledIndex($code);
            }
        }
    }

    public function enableAutomaticReindex(Varien_Event_Observer $observer)
    {
        /** @var $index Ess_M2ePro_Model_Magento_Product_Index */
        $index = Mage::getSingleton('M2ePro/Magento_Product_Index');

        if (!$index->isIndexManagementEnabled()) {
            return;
        }

        $enabledIndexes = array();

        foreach ($index->getIndexes() as $code) {
            if ($index->isDisabledIndex($code) && $index->enableReindex($code)) {
                $index->forgetDisabledIndex($code);
                $enabledIndexes[] = $code;
            }
        }

        $executedIndexes = array();

        foreach ($enabledIndexes as $code) {
            if ($index->requireReindex($code) && $index->executeReindex($code)) {
                $executedIndexes[] = $code;
            }
        }

        if (count($executedIndexes) <= 0) {
            return;
        }

        Mage::getModel('M2ePro/Synchronization_Log')->addMessage(
            Mage::helper('M2ePro')->__('Product reindex was executed.'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    //####################################
}