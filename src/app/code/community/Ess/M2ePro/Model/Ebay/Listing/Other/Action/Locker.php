<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Locker extends Ess_M2ePro_Model_Ebay_Listing_Action_Locker
{
    // ########################################

    /**
     * @inheritdoc
     */
    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/Listing_Other_LockItem', array(
                'component' => Ess_M2ePro_Helper_Component_Ebay::NICK
            ));
        }

        return $this->lockItem;
    }

    // ########################################
}