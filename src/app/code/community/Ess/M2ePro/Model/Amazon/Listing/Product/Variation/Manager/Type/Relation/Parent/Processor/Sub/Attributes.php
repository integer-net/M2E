<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Attributes
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    // ##########################################################

    protected function check()
    {
        if (!$this->getProcessor()->getTypeModel()->isActualProductAttributes()) {
            $this->getProcessor()->getTypeModel()->resetProductAttributes(false);
        }

        if (!$this->getProcessor()->isGeneralIdSet()) {
            $this->getProcessor()->getTypeModel()->setChannelVariations(array(), false);
            $this->getProcessor()->getTypeModel()->setChannelAttributesSets(array(), false);
        }
    }

    protected function execute() {}

    // ##########################################################
}