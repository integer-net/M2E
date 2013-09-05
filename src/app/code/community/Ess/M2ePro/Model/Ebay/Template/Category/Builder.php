<?php

class Ess_M2ePro_Model_Ebay_Template_Category_Builder
{
    // ########################################

    public function build(array $data)
    {
        //------------------------------
        $categoryTemplateData = $this->prepareCategoryTemplateData($data);
        $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category')->setData($categoryTemplateData);
        $categoryTemplate->save();
        //------------------------------

        // save specifics
        //------------------------------
        $specificsData = $this->prepareSpecificsData($data);
        $transaction = Mage::getModel('core/resource_transaction');

        foreach ($specificsData as $specificData) {
            $specificData['template_category_id'] = $categoryTemplate->getId();

            $specific = Mage::getModel('M2ePro/Ebay_Template_Category_Specific');
            $specific->setData($specificData);

            $transaction->addObject($specific);
        }

        $transaction->save();
        //------------------------------

        return $categoryTemplate;
    }

    // ########################################

    private function prepareCategoryTemplateData(array $data)
    {
        $prepared = array();

        $categoryPrefixes = array(
            'category_main_' ,
            'category_secondary_',
            'store_category_main_',
            'store_category_secondary_'
        );

        foreach ($categoryPrefixes as $prefix) {
            $prepared[$prefix.'mode']       = (int)$data[$prefix.'mode'];
            $prepared[$prefix.'id']         = $data[$prefix.'id'];
            $prepared[$prefix.'attribute']  = $data[$prefix.'attribute'];

            if (!empty($data[$prefix.'path'])) {
                $prepared[$prefix.'path'] = $data[$prefix.'path'];
            }
        }

        //------------------------------
        $prepared['tax_category_mode'] = (int)$data['tax_category_mode'];
        $prepared['tax_category_value'] = $data['tax_category_value'];
        $prepared['tax_category_attribute'] = $data['tax_category_attribute'];
        //------------------------------

        $prepared['variation_enabled'] = (int)$data['variation_enabled'];

        if (!empty($data['motors_specifics_attribute'])) {
            $prepared['motors_specifics_attribute'] = $data['motors_specifics_attribute'];
        }

        return $prepared;
    }

    private function prepareSpecificsData(array $data)
    {
        $prepared = array();

        foreach ($data['specifics'] as $specific) {
            $prepared[] = array(
                'mode' => (int)$specific['mode'],
                'mode_relation_id' => (int)$specific['mode_relation_id'],
                'attribute_id' => $specific['attribute_id'],
                'attribute_title' => $specific['attribute_title'],
                'value_mode' => (int)$specific['value_mode'],
                'value_ebay_recommended' => $specific['value_ebay_recommended'],
                'value_custom_value' => $specific['value_custom_value'],
                'value_custom_attribute' => $specific['value_custom_attribute']
            );
        }

        return $prepared;
    }

    // ########################################
}