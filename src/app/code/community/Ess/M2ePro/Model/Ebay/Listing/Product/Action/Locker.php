<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Locker extends Ess_M2ePro_Model_Ebay_Listing_Action_Locker
{
    private $listingProductId = NULL;

    // ########################################

    public function setListingProductId($id)
    {
        $this->listingProductId = (int)$id;
    }

    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    // ########################################

    /**
     * @inheritdoc
     */
    protected function getLockItem()
    {
        if (is_null($this->listingProductId)) {
            throw new Exception('Listing product ID must be specified.');
        }

        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/LockItem');
            $this->lockItem->setNick(
                Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProductId
            );
        }

        return $this->lockItem;
    }

    // ########################################
}