<?php

class Ess_M2ePro_Model_Ebay_Template_Category_Builder
{
    // ########################################

    public function build(array $data)
    {
        //------------------------------
        $categoryTemplateData = array();

        $categoryTemplateData['category_main_mode']      = (int)$data['category_main_mode'];
        $categoryTemplateData['category_main_id']        = $data['category_main_id'];
        $categoryTemplateData['category_main_attribute'] = $data['category_main_attribute'];
        $categoryTemplateData['marketplace_id']          = (int)$data['marketplace_id'];

        if (!empty($data['category_main_path'])) {
            $categoryTemplateData['category_main_path'] = $data['category_main_path'];
        }

        $existingTemplates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()
            ->getItemsByPrimaryCategories(
                array($categoryTemplateData)
            );

        /* @var $existingCategoryTemplate Ess_M2ePro_Model_Ebay_Template_Category */
        foreach ($existingTemplates as $existingCategoryTemplate) {
            $specifics = $existingCategoryTemplate->getSpecifics();

            foreach ($specifics as &$specific) {
                unset($specific['id'], $specific['template_category_id']);
            }
            unset($specific);

            foreach ($data['specifics'] as &$specific) {
                unset($specific['id'], $specific['template_category_id']);
            }
            unset($specific);

            if ($specifics == $data['specifics']) {
                return $existingCategoryTemplate;
            }
        }

        $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category')->setData($categoryTemplateData);
        $categoryTemplate->save();
        //------------------------------

        // save specifics
        //------------------------------
        $transaction = Mage::getModel('core/resource_transaction');

        foreach ($data['specifics'] as $specific) {

            $specificData = array(
                'mode' => (int)$specific['mode'],
                'mode_relation_id' => (int)$specific['mode_relation_id'],
                'attribute_id' => $specific['attribute_id'],
                'attribute_title' => $specific['attribute_title'],
                'value_mode' => (int)$specific['value_mode'],
                'value_ebay_recommended' => $specific['value_ebay_recommended'],
                'value_custom_value' => $specific['value_custom_value'],
                'value_custom_attribute' => $specific['value_custom_attribute']
            );

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
}