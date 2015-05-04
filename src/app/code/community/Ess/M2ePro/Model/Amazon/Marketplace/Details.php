<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Marketplace_Details
{
    // ##########################################################

    private $marketplaceId = null;

    private $productData = array();

    private $vocabularyData = array();

    // ##########################################################

    public function setMarketplaceId($marketplaceId)
    {
        if ($this->marketplaceId === $marketplaceId) {
            return $this;
        }

        $this->marketplaceId = $marketplaceId;
        $this->load();

        return $this;
    }

    // ##########################################################

    public function getVocabularyAttributeNames($attribute)
    {
        if (empty($this->vocabularyData[$attribute])) {
            return array();
        }

        return $this->vocabularyData[$attribute]['names'];
    }

    public function getVocabularyOptionNames($attribute, $option)
    {
        if (empty($this->vocabularyData[$attribute]['options'])) {
            return array();
        }

        $resultNames = array();

        foreach ($this->vocabularyData[$attribute]['options'] as $optionNames) {
            $preparedOption      = strtolower($option);
            $preparedOptionNames = array_map('strtolower', $optionNames);

            if (!in_array($preparedOption, $preparedOptionNames)) {
                continue;
            }

            $resultNames = array_merge($resultNames, $optionNames);
        }

        return $resultNames;
    }

    // ##########################################################

    public function getVariationThemes($productDataNick)
    {
        if (!isset($this->productData[$productDataNick])) {
            return array();
        }

        return (array)$this->productData[$productDataNick]['variation_themes'];
    }

    public function getVariationThemeAttributes($productDataNick, $theme)
    {
        $themes = $this->getVariationThemes($productDataNick);
        return !empty($themes[$theme]['attributes']) ? $themes[$theme]['attributes'] : array();
    }

    // ##########################################################

    private function load()
    {
        if (is_null($this->marketplaceId)) {
            throw new Exception('Marketplace was not set.');
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table    = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');

        $data = $connRead->select()
            ->from($table)
            ->where('marketplace_id = ?', (int)$this->marketplaceId)
            ->query()
            ->fetch();

        if ($data === false) {
            throw new Exception('Marketplace not found or not synchronized');
        }

        $this->productData    = json_decode($data['product_data'], true);
        $this->vocabularyData = json_decode($data['vocabulary'], true);
    }

    // ##########################################################
}