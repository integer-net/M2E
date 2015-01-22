<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Defaults_UpdateListingsProducts
    extends Ess_M2ePro_Model_Ebay_Synchronization_Defaults_Abstract
{
    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    private $sinceTime = NULL;
    private $toTime = NULL;

    private $listingLogActionId = NULL;

    //####################################

    protected function getNick()
    {
        return '/update_listings_products/';
    }

    protected function getTitle()
    {
        return 'Update Listings Products';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 20;
    }

    protected function getPercentsEnd()
    {
        return 80;
    }

    //####################################

    protected function performActions()
    {
        $this->initSinceTime();

        $accounts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update Listings Products" action for eBay account: "%account_title%" is started. Please wait...
            $status = 'The "Update Listings Products" action for eBay account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process account '.$account->getTitle()
            );

            $this->processAccount($account);

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

            // M2ePro_TRANSLATIONS
            // The "Update Listings Products" action for eBay account: "%account_title%" is finished. Please wait...
            $status = 'The "Update Listings Products" action for eBay account: "%account_title%" is finished.'.
                ' Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }

        $this->updateSinceTime();
    }

    // ----------------------------------

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        foreach ($this->getChangesByAccount($account) as $change) {

            /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getListingProductByEbayItem(
                $change['id'], $account->getId()
            );

            if (is_null($listingProduct)) {
                continue;
            }

            // Listing product isn't listed
            if ($listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                continue;
            }

            $this->processListingProduct($listingProduct,$change);

            if (empty($change['variations'])) {
                continue;
            }

            $variations = $listingProduct->getVariations(true);

            if (count($variations) <= 0) {
                continue;
            }

            $variationsSnapshot = $this->getVariationsSnapshot($variations);

            if (count($variationsSnapshot) <= 0) {
                return;
            }

            $this->processListingProductVariation($variationsSnapshot,$change['variations']);
        }
    }

    private function processListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $updateData = array_merge(
            $this->getProductPriceChanges($listingProduct, $change),
            $this->getProductQtyChanges($listingProduct, $change),
            $this->getProductDatesChanges($listingProduct, $change),
            $this->getProductStatusChanges($listingProduct, $change)
        );

        $listingProduct->addData($updateData)->save();

        foreach ($listingProduct->getVariations(true) as $variation) {

            if ($listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
                $listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED) {
                $variation->setData('status',$listingProduct->getStatus())->save();
            }

            if ($listingProduct->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED &&
                $listingProduct->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $variation->setData('status',Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED)->save();
            }
        }
    }

    private function processListingProductVariation(array $variationsSnapshot, array $changeVariations)
    {
        foreach ($changeVariations as $changeVariation) {
            foreach ($variationsSnapshot as $variationSnapshot) {

                if (!$this->isVariationEqualWithChange($changeVariation,$variationSnapshot)) {
                    continue;
                }

                $updateData = array(
                    'online_price' => (float)$changeVariation['price'] < 0 ? 0 : (float)$changeVariation['price'],
                    'online_qty' => (int)$changeVariation['quantity'] < 0 ? 0 : (int)$changeVariation['quantity'],
                    'online_qty_sold' => (int)$changeVariation['quantitySold'] < 0 ?
                                                                0 : (int)$changeVariation['quantitySold']
                );

                if ($updateData['online_qty'] <= $updateData['online_qty_sold']) {
                    $updateData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
                }
                if ($updateData['online_qty'] <= 0) {
                    $updateData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
                }

                $variationSnapshot['variation']->addData($updateData)->save();

                break;
            }
        }
    }

    //####################################

    private function getChangesByAccount(Ess_M2ePro_Model_Account $account)
    {
        $nextSinceTime = new DateTime($this->getSinceTime(), new DateTimeZone('UTC'));

        // from stored value
        $response = $this->receiveFromEbay($account, array('since_time' => $nextSinceTime->format('Y-m-d H:i:s')));

        if ($response) {
            $this->toTime = (string)$response['to_time'];
            return (array)$response['items'];
        }

        $previousSinceTime = $nextSinceTime;

        $nextSinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $nextSinceTime->modify("-1 day");

        if ($previousSinceTime->format('U') < $nextSinceTime->format('U')) {

            // from day behind now
            $response = $this->receiveFromEbay($account, array('since_time' => $nextSinceTime->format('Y-m-d H:i:s')));

            if ($response) {
                $this->toTime = (string)$response['to_time'];
                return (array)$response['items'];
            }

            $previousSinceTime = $nextSinceTime;
        }

        $nextSinceTime = new DateTime('now', new DateTimeZone('UTC'));

        if ($previousSinceTime->format('U') < $nextSinceTime->format('U')) {

            // from now
            $response = $this->receiveFromEbay($account, array('since_time' => $nextSinceTime->format('Y-m-d H:i:s')));

            if ($response) {
                $this->toTime = (string)$response['to_time'];
                return (array)$response['items'];
            }
        }

        return array();
    }

    private function receiveFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                                    ->processVirtual('item','get','changes',
                                                     $paramsConnector,NULL,
                                                     NULL,$account->getId(),NULL);

        if (!isset($response['items']) || !isset($response['to_time'])) {
            return NULL;
        }

        return $response;
    }

    //####################################

    private function getProductPriceChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $listingType = $this->getActualListingType($listingProduct, $change);

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED) {

            $data['online_buyitnow_price'] = (float)$change['currentPrice'] < 0 ? 0 : (float)$change['currentPrice'];

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            if ($ebayListingProduct->getOnlineBuyItNowPrice() != $data['online_buyitnow_price']) {
                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
                );
            }
        }

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION) {
            $data['online_start_price'] = (float)$change['currentPrice'] < 0 ? 0 : (float)$change['currentPrice'];
        }

        return $data;
    }

    private function getProductQtyChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $data['online_qty'] = (int)$change['quantity'] < 0 ? 0 : (int)$change['quantity'];
        $data['online_qty_sold'] = (int)$change['quantitySold'] < 0 ? 0 : (int)$change['quantitySold'];

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $listingType = $this->getActualListingType($listingProduct, $change);

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION) {
            $data['online_qty'] = 1;
            $data['online_bids'] = (int)$change['bidCount'] < 0 ? 0 : (int)$change['bidCount'];
        }

        if ($ebayListingProduct->getOnlineQty() != $data['online_qty'] ||
            $ebayListingProduct->getOnlineQtySold() != $data['online_qty_sold']) {
            Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
            );
        }

        return $data;
    }

    private function getProductDatesChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        return array(
            'start_date' => Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($change['startTime']),
            'end_date' => Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($change['endTime'])
        );
    }

    // -----------------------------------

    private function getProductStatusChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $qty = (int)$change['quantity'] < 0 ? 0 : (int)$change['quantity'];
        $qtySold = (int)$change['quantitySold'] < 0 ? 0 : (int)$change['quantitySold'];

        if (($change['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
             $change['listingStatus'] == self::EBAY_STATUS_ENDED) &&
             $qty == $qtySold) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_ENDED) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
        }

        if ($listingProduct->getStatus() == $data['status']) {
            return $data;
        }

        Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
            $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
        );

        $data['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
        $this->logChangeOfStatus($listingProduct, $data['status']);

        return $data;
    }

    private function logChangeOfStatus(Ess_M2ePro_Model_Listing_Product $listingProduct, $status)
    {
        $message = '';

        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                // M2ePro_TRANSLATIONS
                // Item status was successfully changed to "Listed".
                $message = 'Item status was successfully changed to "Listed".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                // M2ePro_TRANSLATIONS
                // Item status was successfully changed to "Sold".
                $message = 'Item status was successfully changed to "Sold".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                // M2ePro_TRANSLATIONS
                // Item status was successfully changed to "Stopped".
                $message = 'Item status was successfully changed to "Stopped".';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                // M2ePro_TRANSLATIONS
                // Item status was successfully changed to "Finished".
                $message = 'Item status was successfully changed to "Finished".';
                break;
        }

        $log = Mage::getModel('M2ePro/Listing_Log');
        $log->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $log->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $this->getListingLogActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
            $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    //####################################

    private function getVariationsSnapshot(array $variations)
    {
        $snapshot = array();

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            $options = $variation->getOptions(true);

            if (count($options) <= 0) {
                continue;
            }

            $snapshot[] = array(
                'variation' => $variation,
                'options' => $options
            );
        }

        return $snapshot;
    }

    private function isVariationEqualWithChange(array $changeVariation, array $variationSnapshot)
    {
        if (count($variationSnapshot['options']) != count($changeVariation['specifics'])) {
            return false;
        }

        foreach ($variationSnapshot['options'] as $variationSnapshotOption) {

            $haveOption = false;

            foreach ($changeVariation['specifics'] as $changeVariationOption=>$changeVariationValue) {

                if ($variationSnapshotOption->getData('attribute') == $changeVariationOption &&
                    $variationSnapshotOption->getData('option') == $changeVariationValue) {
                    $haveOption = true;
                    break;
                }
            }

            if ($haveOption === false) {
                return false;
            }
        }

        return true;
    }

    //####################################

    private function initSinceTime()
    {
        $sinceTime = $this->getSinceTime();

        $minTime = new DateTime('now', new DateTimeZone('UTC'));
        $minTime->modify("-1 month");

        if (empty($sinceTime) || strtotime($sinceTime) < (int)$minTime->format('U')) {

            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime->modify("-10 days");
            $sinceTime = $sinceTime->format('Y-m-d H:i:s');

            $this->setSinceTime($sinceTime);
        }

        $this->sinceTime = $sinceTime;
        $this->toTime = $sinceTime;
    }

    private function updateSinceTime()
    {
        $this->setSinceTime($this->toTime);
    }

    // ----------------------------------

    private function getSinceTime()
    {
        return $this->getConfigValue($this->getFullSettingsPath(), 'since_time');
    }

    private function setSinceTime($time)
    {
        $this->setConfigValue($this->getFullSettingsPath(), 'since_time', $time);
    }

    //####################################

    private function getListingLogActionId()
    {
        if (is_null($this->listingLogActionId)) {
            $this->listingLogActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        }
        return $this->listingLogActionId;
    }

    private function getActualListingType(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $validEbayValues = array(
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_AUCTION,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_FIXED
        );

        if (isset($change['listingType']) && in_array($change['listingType'],$validEbayValues)) {

            switch ($change['listingType']) {
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_AUCTION:
                    $result = Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
                    break;
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_FIXED:
                    $result = Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
                    break;
            }

        } else {
            $result = $listingProduct->getChildObject()->getListingType();
        }

        return $result;
    }

    //####################################
}