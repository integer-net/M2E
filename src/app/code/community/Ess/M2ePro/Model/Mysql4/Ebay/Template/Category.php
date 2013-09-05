<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_Category
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_Category', 'id');
    }

    // ########################################

    public function getSameCategoriesData(array $templateCategoryIds)
    {
        $select = $this->getReadConnection()->select();
        $select->from(array('etc' => $this->getMainTable()));
        $select->where('id IN (?)', $templateCategoryIds);

        $templatesData = $select->query()->fetchAll(PDO::FETCH_ASSOC);

        $categoriesData = array();
        $sameMain = $sameSecondary = $sameStoreMain = $sameStoreSecondary = $sameTax = true;

        //------------------------------
        foreach ($templatesData as $templateData) {
            if (empty($categoriesData)) {
                $categoriesData = $templateData;
                continue;
            }

            if ($categoriesData['category_main_mode'] != $templateData['category_main_mode']) {
                $sameMain = false;
            }
            if ($categoriesData['category_secondary_mode'] != $templateData['category_secondary_mode']) {
                $sameSecondary = false;
            }
            if ($categoriesData['store_category_main_mode'] != $templateData['store_category_main_mode']) {
                $sameStoreMain = false;
            }
            if ($categoriesData['store_category_secondary_mode'] != $templateData['store_category_secondary_mode']) {
                $sameStoreSecondary = false;
            }

            if ($sameMain) {
                switch ($templateData['category_main_mode']) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                        $key = 'category_main_id';
                        $sameMain = $categoriesData[$key] == $templateData[$key];
                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                        $key = 'category_main_attribute';
                        $sameMain = $categoriesData[$key] == $templateData[$key];
                        break;
                }
            }

            if ($sameSecondary) {
                switch ($templateData['category_secondary_mode']) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                        $key = 'category_secondary_id';
                        $sameSecondary = $categoriesData[$key] == $templateData[$key];
                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                        $key = 'category_secondary_attribute';
                        $sameSecondary = $categoriesData[$key] == $templateData[$key];
                        break;
                }
            }

            if ($sameStoreMain) {
                switch ($templateData['store_category_main_mode']) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY:
                        $key = 'store_category_main_id';
                        $sameStoreMain = $categoriesData[$key] == $templateData[$key];
                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_ATTRIBUTE:
                        $key = 'store_category_main_attribute';
                        $sameStoreMain = $categoriesData[$key] == $templateData[$key];
                        break;
                }
            }

            if ($sameStoreSecondary) {
                switch ($templateData['category_main_mode']) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY:
                        $key = 'store_category_secondary_id';
                        $sameStoreSecondary = $categoriesData[$key] == $templateData[$key];
                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_ATTRIBUTE:
                        $key = 'store_category_secondary_attribute';
                        $sameStoreSecondary = $categoriesData[$key] == $templateData[$key];
                        break;
                }
            }

            if ($sameTax) {
                switch ($templateData['tax_category_mode']) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_VALUE:
                        $key = 'tax_category_value';
                        $sameTax = $categoriesData[$key] == $templateData[$key];
                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_ATTRIBUTE:
                        $key = 'tax_category_attribute';
                        $sameTax = $categoriesData[$key] == $templateData[$key];
                        break;
                }
            }
        }
        //------------------------------

        if (!$sameMain || array_search(null, $templateCategoryIds) !== false) {
            $categoriesData['category_main_mode'] = 0;
            $categoriesData['category_main_id'] = NULL;
            $categoriesData['category_main_attribute'] = '';
            $categoriesData['category_main_path'] = '';

            if (!$sameMain) {
                $categoriesData['category_main_message'] = 'Please, specify a value suitable for all chosen products.';
            }
        }
        if (!$sameSecondary || array_search(null, $templateCategoryIds) !== false) {
            $categoriesData['category_secondary_mode'] = 0;
            $categoriesData['category_secondary_id'] = NULL;
            $categoriesData['category_secondary_attribute'] = '';
            $categoriesData['category_secondary_path'] = '';

            if (!$sameSecondary) {
                $categoriesData['category_secondary_message'] = 'Please, specify a value suitable for all chosen products.';
            }
        }
        if (!$sameStoreMain || array_search(null, $templateCategoryIds) !== false) {
            $categoriesData['store_category_main_mode'] = 0;
            $categoriesData['store_category_main_id'] = NULL;
            $categoriesData['store_category_main_attribute'] = '';
            $categoriesData['store_category_main_path'] = '';

            if (!$sameStoreMain) {
                $categoriesData['store_category_main_message'] = 'Please, specify a value suitable for all chosen products.';
            }
        }
        if (!$sameStoreSecondary || array_search(null, $templateCategoryIds) !== false) {
            $categoriesData['store_category_secondary_mode'] = 0;
            $categoriesData['store_category_secondary_id'] = NULL;
            $categoriesData['store_category_secondary_attribute'] = '';
            $categoriesData['store_category_secondary_path'] = '';

            if (!$sameStoreSecondary) {
                $categoriesData['store_category_secondary_message'] = 'Please, specify a value suitable for all chosen products.';
            }
        }
        if (!$sameTax || array_search(null, $templateCategoryIds) !== false) {
            $categoriesData['tax_category_mode'] = 0;
            $categoriesData['tax_category_value'] = '';
            $categoriesData['tax_category_attribute'] = '';
        }

        return $categoriesData;
    }

    // ########################################

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            'id', 'title',
            'create_date', 'update_date'
        );

        $dataConversions = array(
            array('field' => 'store_category_main_id', 'type' => 'float'),
            array('field' => 'store_category_secondary_id', 'type' => 'float'),
        );

        foreach ($dataConversions as $data) {
            $type = $data['type'] . 'val';

            array_key_exists($data['field'],$newData) && $newData[$data['field']] = $type($newData[$data['field']]);
            array_key_exists($data['field'],$oldData) && $oldData[$data['field']] = $type($oldData[$data['field']]);
        }

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        !isset($newData['specifics']) && $newData['specifics'] = array();
        !isset($oldData['specifics']) && $oldData['specifics'] = array();

        foreach ($newData['specifics'] as $key => $newSpecific) {
            unset($newData['specifics'][$key]['id'], $newData['specifics'][$key]['template_category_id']);
        }
        foreach ($oldData['specifics'] as $key => $oldSpecific) {
            unset($oldData['specifics'][$key]['id'], $oldData['specifics'][$key]['template_category_id']);
        }

        ksort($newData);
        ksort($oldData);
        array_walk($newData['specifics'],'ksort');
        array_walk($oldData['specifics'],'ksort');

        return md5(json_encode($newData)) !== md5(json_encode($oldData));
    }

    // ########################################
}