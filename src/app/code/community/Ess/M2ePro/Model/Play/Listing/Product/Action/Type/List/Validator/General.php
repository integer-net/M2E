<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_List_Validator_General
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Validator
{
    const CONDITION_NOTE_MAX_LENGTH = 1000;

    public function validate()
    {
        if (!$this->getListingProduct()->isListable()) {

            // M2ePro_TRANSLATIONS
            // Item is already on Play.com, or not available.
            $this->addMessage('Item is already on Play.com, or not available.');

            return false;
        }

        if (!$this->validateGeneralId()) {
            return false;
        }

        if ($this->getVariationManager()->isVariationProduct() && !$this->validateVariationProductMatching()) {
            return false;
        }

        if (!$this->validateCondition()) {
            return false;
        }

        if (!$this->validateDispatch()) {
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }

    private function validateGeneralId()
    {
        $generalId = $this->getPlayListingProduct()->getGeneralId();
        if (empty($generalId)) {
            $generalId = $this->getPlayListingProduct()->getListingSource()->getSearchGeneralId();
        }

        if (empty($generalId)) {
            $this->addMessage('Product cannot be Listed because Identifier is not specified.');
            return false;
        }
        $this->data['general_id'] = $generalId;

        $generalIdType = $this->getPlayListingProduct()->getGeneralIdType();
        if (empty($generalIdType)) {
            $generalIdType = $this->getPlayListing()->getGeneralIdMode();
        }

        if (empty($generalIdType)) {
            $this->addMessage('The Identifier format type is not set.');
            return false;
        }
        $this->data['general_id_type'] = $generalIdType;

        return true;
    }

    private function validateCondition()
    {
        $condition = $this->getPlayListingProduct()->getCondition();
        if (empty($condition)) {
            $condition = $this->getPlayListingProduct()->getListingSource()->getCondition();
        }

        $validConditions = $this->getPlayListing()->getConditionValues();

        if (empty($condition) || !in_array($condition, $validConditions)) {

            // M2ePro_TRANSLATIONS
            // Condition is invalid or missed. Please, check Listing Channel and Product Settings.
            $this->addMessage(
                'Condition is invalid or missed. Please, check Listing Channel and Product Settings.'
            );

            return false;
        }

        $conditionNote = $this->getPlayListingProduct()->getConditionNote();
        if (is_null($conditionNote)) {
            $conditionNote = $this->getPlayListingProduct()->getListingSource()->getConditionNote();
        }

        if (is_null($conditionNote)) {

            // M2ePro_TRANSLATIONS
            // Comment is invalid or missed. Please, check Listing Channel and Product Settings.
            $this->addMessage(
                'Comment is invalid or missed. Please, check Listing Channel and Product Settings.'
            );

            return false;
        }

        if (!empty($conditionNote) && strlen($conditionNote) > self::CONDITION_NOTE_MAX_LENGTH) {

            // M2ePro_TRANSLATIONS
            // The length of comment must be less than 1000 characters.
            $this->addMessage('The length of comment must be less than 1000 characters.');

            return false;
        }

        return true;
    }

    private function validateDispatch()
    {
        $dispatchTo = $this->getDispatchTo();
        if (Mage::helper('M2ePro/Component_Play')->isValidDispatchTo($dispatchTo)) {

            // M2ePro_TRANSLATIONS
            // Delivery Region is invalid or missed. Please, check Listing Channel and Product Settings.
            $this->addMessage(
                'Delivery Region is invalid or missed. Please, check Listing Channel and Product Settings.'
            );

            return false;
        }
        $this->data['dispatch_to'] = $dispatchTo;

        $dispatchFrom = $this->getDispatchFrom();
        if (empty($dispatchFrom)) {

            // M2ePro_TRANSLATIONS
            // Dispatch Country is invalid or missed. Please, check Listing Channel and Product Settings.
            $this->addMessage(
                'Dispatch Country is invalid or missed. Please, check Listing Channel and Product Settings.'
            );

            return false;
        }
        $this->data['dispatch_from'] = $dispatchFrom;

        return true;
    }

}