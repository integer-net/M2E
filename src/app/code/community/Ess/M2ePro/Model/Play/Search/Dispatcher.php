<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Dispatcher
{
    // ########################################

    public function runManual(Ess_M2ePro_Model_Listing_Product $listingProduct, $query)
    {
        if (empty($query)) {
            return false;
        }

        try {
            return Mage::getModel('M2ePro/Play_Search_Manual')->process($listingProduct,(string)$query);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }
    }

    public function runAutomatic(array $listingsProducts)
    {
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $key => $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                unset($listingsProducts[$key]);
                continue;
            }

            if (!$this->checkSearchConditions($listingProduct)) {
                unset($listingsProducts[$key]);
                continue;
            }
        }

        if (count($listingsProducts) <= 0) {
            return false;
        }

        try {

            $automaticDispatcher = Mage::getModel('M2ePro/Play_Search_Automatic');

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            foreach ($listingsProducts as $listingProduct) {
                $automaticDispatcher->process($listingProduct);
            }

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return true;
    }

    // ########################################

    private function checkSearchConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return $listingProduct->isNotListed() &&
               !$listingProduct->getChildObject()->getGeneralId();
    }

    // ########################################
}