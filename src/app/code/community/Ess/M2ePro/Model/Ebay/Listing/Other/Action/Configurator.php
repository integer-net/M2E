<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator
    extends Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
{
    // ########################################

    public function isAllPermitted()
    {
        return $this->isGeneral() &&
               $this->isQty() && $this->isPrice() &&
               $this->isTitle() && $this->isSubtitle() &&
               $this->isDescription();
    }

    // ########################################
}