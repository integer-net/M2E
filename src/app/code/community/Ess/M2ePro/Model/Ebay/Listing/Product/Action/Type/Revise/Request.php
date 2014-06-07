<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    // ########################################

    public function getActionData()
    {
        $data = array_merge(
            array(
                'item_id' => $this->getEbayListingProduct()->getEbayItemIdReal()
            ),
            $this->getRequestVariations()->getData()
        );

        if ($this->getConfigurator()->isGeneral()) {

            $data['sku'] = $this->getEbayListingProduct()->getSku();

            $data = array_merge(

                $data,

                $this->getRequestCategories()->getData(),

                $this->getRequestPayment()->getData(),
                $this->getRequestReturn()->getData(),
                $this->getRequestShipping()->getData()
            );
        }

        return array_merge(
            $data,
            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    protected function prepareFinalData(array $data)
    {
        $data = $this->processingReplacedAction($data);

        $data = $this->insertHasSaleFlagToVariations($data);
        $data = $this->removeImagesIfThereAreNoChanges($data);
        $data = $this->removeNodesIfItemHasTheSaleOrBid($data);
        $data = $this->removeDurationByBestOfferMode($data);

        return parent::prepareFinalData($data);
    }

    // ########################################

    private function processingReplacedAction($data)
    {
        $params = $this->getParams();

        if (!isset($params['replaced_action'])) {
            return $data;
        }

        $this->insertReplacedActionMessage($params['replaced_action']);
        $data = $this->modifyQtyByReplacedAction($params['replaced_action'], $data);

        return $data;
    }

    private function insertReplacedActionMessage($replacedAction)
    {
        switch ($replacedAction) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:

                $this->addWarningMessage(
                    'Revise was executed instead of Relist because \'Out Of Stock Control\' Option is enabled '.
                    'in the \'Price, Quantity and Format\' Policy'
                );

            break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:

                $this->addWarningMessage(
                    'Revise was executed instead of Stop because \'Out Of Stock Control\' Option is enabled '.
                    'in the \'Price, Quantity and Format\' Policy'
                );

            break;
        }

        return;
    }

    private function modifyQtyByReplacedAction($replacedAction, array $data)
    {
        if ($replacedAction != Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
            return $data;
        }

        $data['out_of_stock_control'] = $this->getEbayListingProduct()
                                             ->getEbaySellingFormatTemplate()->getOutOfStockControl();

        if (!$this->getIsVariationItem()) {
            $data['qty'] = 0;
            return $data;
        }

        if (!isset($data['variation']) || !is_array($data['variation'])) {
            return $data;
        }

        foreach ($data['variation'] as &$variation) {
            $variation['not_real_qty'] = true;
            $variation['qty'] = 0;
        }

        return $data;
    }

    // ----------------------------------------

    private function insertHasSaleFlagToVariations(array $data)
    {
        if (!isset($data['variation']) || !is_array($data['variation'])) {
            return $data;
        }

        foreach ($data['variation'] as &$variation) {
            if (!isset($variation['not_real_qty']) && isset($variation['qty']) && (int)$variation['qty'] <= 0) {
                $variation['_instance_']->getChildObject()->hasSales() &&
                    $variation['has_sales'] = true;
            }
        }

        return $data;
    }

    private function removeImagesIfThereAreNoChanges(array $data)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        $key = 'ebay_product_images_hash';
        if (!empty($additionalData[$key]) && isset($data['images']['images']) &&
            $additionalData[$key] == sha1(json_encode($data['images']['images']))) {
            unset($data['images']['images']);
        }

        $key = 'ebay_product_variation_images_hash';
        if (!empty($additionalData[$key]) && isset($data['variation_image']) &&
            $additionalData[$key] == sha1(json_encode($data['variation_image']))) {
            unset($data['variation_image']);
        }

        return $data;
    }

    private function removeNodesIfItemHasTheSaleOrBid(array $data)
    {
        if (!isset($data['title']) && !isset($data['subtitle']) &&
            !isset($data['duration']) && !isset($data['is_private'])) {
            return $data;
        }

        $deleteFlag = (is_null($this->getEbayListingProduct()->getOnlineQtySold())
                           ? false
                           : $this->getEbayListingProduct()->getOnlineQtySold() > 0)
                      ||
                      ($this->getEbayListingProduct()->isListingTypeAuction()
                           && $this->getEbayListingProduct()->getOnlineBids() > 0);

        $warningMessageReasons = array();

        if (isset($data['title']) && $deleteFlag) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Title');
            unset($data['title']);
        }
        if (isset($data['subtitle']) && $deleteFlag) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Subtitle');
            unset($data['subtitle']);
        }
        if (isset($data['duration']) && $deleteFlag) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Duration');
            unset($data['duration']);
        }
        if (isset($data['is_private']) && $deleteFlag) {
            $warningMessageReasons[] = Mage::helper('M2ePro')->__('Private Listing');
            unset($data['is_private']);
        }

        if (!empty($warningMessageReasons)) {

            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    '%s field(s) were ignored because eBay doesn\'t allow revise the item if it has sales,
                    bids for auction type or less than 12 hours remain before the item end.',
                    implode(', ', $warningMessageReasons)
                )
            );
        }

        return $data;
    }

    private function removeDurationByBestOfferMode(array $data)
    {
        if (isset($data['bestoffer_mode']) && $data['bestoffer_mode']) {

            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'Duration field(s) was ignored because
                    eBay doesn\'t allow revise the item if Best Offer is enabled.'
                )
            );
            unset($data['duration']);
        }

        return $data;
    }

    // ########################################
}