<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Locker extends Ess_M2ePro_Model_Ebay_Listing_Action_Locker
{
    private $listingId = NULL;

    // ########################################

    public function setListingId($id)
    {
        $this->listingId = (int)$id;
    }

    public function getListingId()
    {
        return $this->listingId;
    }

    // ########################################

    /**
     * @inheritdoc
     */
    protected function getLockItem()
    {
        if (is_null($this->listingId)) {
            throw new Exception('Listing ID must be specified.');
        }

        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/Listing_LockItem', array(
                'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'id' => $this->listingId
            ));
        }

        return $this->lockItem;
    }

    // ########################################
}