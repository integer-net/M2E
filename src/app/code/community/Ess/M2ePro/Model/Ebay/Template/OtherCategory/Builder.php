<?php

class Ess_M2ePro_Model_Ebay_Template_OtherCategory_Builder
{
    // ########################################

    public function build(array $data)
    {
        //------------------------------
        $otherCategoryTemplateData = array();

        $categoryPrefixes = array(
            'category_secondary_',
            'store_category_main_',
            'store_category_secondary_'
        );

        foreach ($categoryPrefixes as $prefix) {

            $otherCategoryTemplateData[$prefix.'mode']       = (int)$data[$prefix.'mode'];
            $otherCategoryTemplateData[$prefix.'id']         = (float)$data[$prefix.'id'];
            $otherCategoryTemplateData[$prefix.'attribute']  = (string)$data[$prefix.'attribute'];

            if (!empty($data[$prefix.'path'])) {
                $otherCategoryTemplateData[$prefix.'path'] = $data[$prefix.'path'];
            }
        }

        $otherCategoryTemplateData['marketplace_id'] = (int)$data['marketplace_id'];
        $otherCategoryTemplateData['account_id'] = (int)$data['account_id'];

        $collection = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getCollection();
        foreach ($otherCategoryTemplateData as $field => $filter) {
            is_null($filter) && $filter = array('null' => true);
            $collection->addFieldToFilter($field,$filter);
        }

        if ($collection->getFirstItem()->getId()) {
            return $collection->getFirstItem();
        }

        $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->setData($otherCategoryTemplateData);
        $categoryTemplate->save();
        //------------------------------

        return $categoryTemplate;
    }

    // ########################################
}