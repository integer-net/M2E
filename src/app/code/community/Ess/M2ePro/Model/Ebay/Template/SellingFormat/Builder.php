<?php

class Ess_M2ePro_Model_Ebay_Template_SellingFormat_Builder
    extends Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    // ########################################

    public function build(array $data)
    {
        if (empty($data)) {
            return NULL;
        }

        // validate input data
        //------------------------------
        $this->validate($data);
        //------------------------------

        // prepare input data
        //------------------------------
        $data = $this->prepareData($data);
        //------------------------------

        // create template
        //------------------------------
        $template = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_SellingFormat');

        if (isset($data['id'])) {
            $template->load($data['id']);
        }

        $template->addData($data);
        $template->save();
        //------------------------------

        return $template;
    }

    protected function prepareData(array &$data)
    {
        $prepared = parent::prepareData($data);

        //------------------------------
        $isSimpleMode = Mage::helper('M2ePro/View_Ebay')->isSimpleMode();

        $defaultData = $isSimpleMode ?
            Mage::getSingleton('M2ePro/Ebay_Template_SellingFormat')->getDefaultSettingsSimpleMode() :
            Mage::getSingleton('M2ePro/Ebay_Template_SellingFormat')->getDefaultSettingsAdvancedMode();

        $data = array_merge($defaultData, $data);
        //------------------------------

        if (isset($data['listing_type'])) {
            $prepared['listing_type'] = (int)$data['listing_type'];
        }

        if (isset($data['listing_is_private'])) {
            $prepared['listing_is_private'] = (int)(bool)$data['listing_is_private'];
        }

        if (isset($data['listing_type_attribute'])) {
            $prepared['listing_type_attribute'] = $data['listing_type_attribute'];
        }

        if (isset($data['duration_mode'])) {
            $prepared['duration_mode'] = (int)$data['duration_mode'];
        }

        if (isset($data['duration_attribute'])) {
            $prepared['duration_attribute'] = $data['duration_attribute'];
        }

        if (isset($data['qty_mode'])) {
            $prepared['qty_mode'] = (int)$data['qty_mode'];
        }

        if (isset($data['qty_custom_value'])) {
            $prepared['qty_custom_value'] = (int)$data['qty_custom_value'];
        }

        if (isset($data['qty_custom_attribute'])) {
            $prepared['qty_custom_attribute'] = $data['qty_custom_attribute'];
        }

        if (isset($data['qty_max_posted_value_mode'])) {
            $prepared['qty_max_posted_value_mode'] = (int)$data['qty_max_posted_value_mode'];
        }

        if (isset($data['qty_max_posted_value'])) {
            $prepared['qty_max_posted_value'] = (int)$data['qty_max_posted_value'];
        }

        if (isset($data['price_variation_mode'])) {
            $prepared['price_variation_mode'] = (int)$data['price_variation_mode'];
        }

        if (isset($data['start_price_mode'])) {
            $prepared['start_price_mode'] = (int)$data['start_price_mode'];
        }

        //------------------------------
        if (isset($data['start_price_coefficient']) && isset($data['start_price_coefficient_mode'])) {

            $prepared['start_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $data['start_price_coefficient'],
                $data['start_price_coefficient_mode']
            );
        }
        //------------------------------

        if (isset($data['start_price_custom_attribute'])) {
            $prepared['start_price_custom_attribute'] = $data['start_price_custom_attribute'];
        }

        if (isset($data['reserve_price_mode'])) {
            $prepared['reserve_price_mode'] = (int)$data['reserve_price_mode'];
        }

        //------------------------------
        if (isset($data['reserve_price_coefficient']) && isset($data['reserve_price_coefficient_mode'])) {

            $prepared['reserve_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $data['reserve_price_coefficient'],
                $data['reserve_price_coefficient_mode']
            );
        }
        //------------------------------

        if (isset($data['reserve_price_custom_attribute'])) {
            $prepared['reserve_price_custom_attribute'] = $data['reserve_price_custom_attribute'];
        }

        if (isset($data['buyitnow_price_mode'])) {
            $prepared['buyitnow_price_mode'] = (int)$data['buyitnow_price_mode'];
        }

        //------------------------------
        if (isset($data['buyitnow_price_coefficient']) && isset($data['buyitnow_price_coefficient_mode'])) {

            $prepared['buyitnow_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $data['buyitnow_price_coefficient'],
                $data['buyitnow_price_coefficient_mode']
            );
        }
        //------------------------------

        if (isset($data['buyitnow_price_custom_attribute'])) {
            $prepared['buyitnow_price_custom_attribute'] = $data['buyitnow_price_custom_attribute'];
        }

        if (isset($data['best_offer_mode'])) {
            $prepared['best_offer_mode'] = (int)$data['best_offer_mode'];
        }

        if (isset($data['best_offer_accept_mode'])) {
            $prepared['best_offer_accept_mode'] = (int)$data['best_offer_accept_mode'];
        }

        if (isset($data['best_offer_accept_value'])) {
            $prepared['best_offer_accept_value'] = $data['best_offer_accept_value'];
        }

        if (isset($data['best_offer_accept_attribute'])) {
            $prepared['best_offer_accept_attribute'] = $data['best_offer_accept_attribute'];
        }

        if (isset($data['best_offer_reject_mode'])) {
            $prepared['best_offer_reject_mode'] = (int)$data['best_offer_reject_mode'];
        }

        if (isset($data['best_offer_reject_value'])) {
            $prepared['best_offer_reject_value'] = $data['best_offer_reject_value'];
        }

        if (isset($data['best_offer_reject_attribute'])) {
            $prepared['best_offer_reject_attribute'] = $data['best_offer_reject_attribute'];
        }

        if (isset($data['charity_id'], $data['charity_name'], $data['charity_percentage'])) {
            $src = array(
                'id'            => $data['charity_id'],
                'name'          => $data['charity_name'],
                'percentage'    => (int)$data['charity_percentage'],
            );

            $prepared['charity'] = json_encode($src);
        }

        return $prepared;
    }

    // ########################################

    private function getFormattedPriceCoefficient($priceCoefficient, $priceCoefficientMode)
    {
        if ($priceCoefficientMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_NONE) {
            return '';
        }

        if ($priceCoefficientMode ==
                Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE ||
            $priceCoefficientMode ==
                Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE) {

            $priceCoefficient = '-'.$priceCoefficient;
        } else {
            $priceCoefficient = '+'.$priceCoefficient;
        }

        if ($priceCoefficientMode ==
                Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE ||
            $priceCoefficientMode ==
                Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE) {

            $priceCoefficient .= '%';
        }

        return $priceCoefficient;
    }

    // ########################################
}