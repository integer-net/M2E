<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    // ########################################

    public function getActionData()
    {
        return array_merge(

            array(
                'sku' => $this->getEbayListingProduct()->getSku()
            ),

            $this->getRequestVariations()->getData(),
            $this->getRequestCategories()->getData(),

            $this->getRequestPayment()->getData(),
            $this->getRequestReturn()->getData(),
            $this->getRequestShipping()->getData(),

            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    // ########################################

    public function clearVariations()
    {
        $variations = $this->getListingProduct()->getVariations(true);

        foreach ($variations as $variation) {
           /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
           $variation->deleteInstance();
        }
    }

    public function getTheSameProductAlreadyListed()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/ebay/connector/listing/', 'check_the_same_product_already_listed');

        if (!is_null($config) && $config != 1) {
            return NULL;
        }

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');

        $listingProductCollection
            ->getSelect()
            ->join(array('l'=>$listingTable),'`main_table`.`listing_id` = `l`.`id`',array());

        $listingProductCollection
            ->addFieldToFilter('status',array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED))
            ->addFieldToFilter('product_id',$this->getListingProduct()->getProductId())
            ->addFieldToFilter('account_id',$this->getAccount()->getId())
            ->addFieldToFilter('marketplace_id',$this->getMarketplace()->getId());

        $theSameListingProduct = $listingProductCollection->getFirstItem();

        if (!$theSameListingProduct->getId()) {
            return NULL;
        }

        return $theSameListingProduct;
    }

    // ########################################

    protected function getIsEpsImagesMode()
    {
        return NULL;
    }

    // ########################################
}