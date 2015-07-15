<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Amazon_Vocabulary extends Mage_Core_Helper_Abstract
{
    const VOCABULARY_TYPE_ATTRIBUTE = 'attribute';
    const VOCABULARY_TYPE_OPTION    = 'option';

    // ########################################

    public function addAttributes($marketplaceId, $magentoAttr, $channelAttr)
    {
        $vocabularyData = $this->getVocabularyData($marketplaceId);

        if (!empty($vocabularyData[$channelAttr]) && in_array($magentoAttr, $vocabularyData[$channelAttr]['names'])) {
            return;
        }

        try {

            /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product','add','vocabulary',
                array(
                    'type'     => self::VOCABULARY_TYPE_ATTRIBUTE,
                    'original' => $channelAttr,
                    'value'    => $magentoAttr
                )
            );

            $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ########################################

    public function addOptions($marketplaceId,
                               $productOption,
                               $channelOption,
                               $channelAttr = NULL)
    {

        $vocabularyData = $this->getVocabularyData($marketplaceId);

        if (!empty($vocabularyData[$channelAttr]) &&
            in_array($productOption, $vocabularyData[$channelAttr]['options'])) {
            return;
        }

        try {

            /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product','add','vocabulary',
                array(
                    'type'      => self::VOCABULARY_TYPE_OPTION,
                    'attribute' => $channelAttr,
                    'original'  => $channelOption,
                    'value'     => $productOption
                )
            );

            $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ########################################

    private function getVocabularyData($marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table    = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');

        $data = $connRead->select()
            ->from($table)
            ->where('marketplace_id = ?', (int)$marketplaceId)
            ->query()
            ->fetch();

        if ($data === false) {
            throw new Exception('Marketplace not found or not synchronized');
        }

        return json_decode($data['vocabulary'], true);
    }
}
