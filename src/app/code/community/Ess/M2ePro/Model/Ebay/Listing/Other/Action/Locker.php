<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Locker extends Ess_M2ePro_Model_Ebay_Listing_Action_Locker
{
    private $listingOtherId = NULL;

    // ########################################

    public function setListingOtherId($id)
    {
        $this->listingOtherId = (int)$id;
    }

    public function getListingOtherId()
    {
        return $this->listingOtherId;
    }

    // ########################################

    /**
     * @inheritdoc
     */
    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/LockItem');
            $this->lockItem->setNick(
                Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_other_'.$this->listingOtherId
            );
        }

        return $this->lockItem;
    }

    // ########################################
}