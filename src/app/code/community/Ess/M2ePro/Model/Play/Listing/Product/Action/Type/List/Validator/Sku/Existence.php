<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_List_Validator_Sku_Existence
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Validator
{
    // ########################################

    private $existenceResult = array();

    // ########################################

    public function setExistenceResult(array $result)
    {
        $this->existenceResult = $result;
        return $this;
    }

    // ########################################

    public function validate()
    {
        if (empty($this->existenceResult['general_id'])) {
            return true;
        }

        if ($this->getPlayListingProduct()->getGeneralId() &&
            $this->getPlayListingProduct()->getGeneralId() != $this->existenceResult['general_id']
        ) {
            $this->addMessage('Product with the same Reference Code is found on Play.com but the
                               Identifier is different in Magento and on Rakuten.com');
            return false;
        }

        $this->link(
            $this->existenceResult['general_id'],
            $this->existenceResult['general_id_type'],
            $this->data['sku']
        );

        return false;
    }

    // ########################################

    private function link($generalId, $generalIdType, $sku)
    {
        /** @var Ess_M2ePro_Model_Play_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Play_Listing_Product_Action_Type_List_Linking');
        $linkingObject->setListingProduct($this->getListingProduct());
        $linkingObject->setGeneralId($generalId);
        $linkingObject->setGeneralIdType($generalIdType);
        $linkingObject->setSku($sku);

        $linkingObject->link();

        $this->addMessage(
            'Product was successfully found in Play.com Inventory by
             Reference Code and linked to your Magento Product.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
        );
    }

    // ########################################
}