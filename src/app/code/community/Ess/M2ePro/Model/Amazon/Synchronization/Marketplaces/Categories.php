<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Categories
    extends Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/categories/';
    }

    protected function getTitle()
    {
        return 'Categories';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        $partNumber = 1;
        $params     = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Marketplace', (int)$params['marketplace_id']
        );

        $this->deleteAllCategories($marketplace);

        $this->getActualOperationHistory()->addText('Starting Marketplace "'.$marketplace->getTitle().'"');

        for ($i = 0; $i < 100; $i++) {
            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                'Get Categories from Amazon, part № ' . $partNumber);
            $response = $this->receiveFromAmazon($marketplace, $partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing Categories data ('.(int)$partNumber.'/'.(int)$response['total_parts'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),
                'Save Categories to DB');
            $this->saveCategoriesToDb($marketplace, $response['data']);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->getActualLockItem()->setPercents($this->getPercentsEnd());
            $this->getActualLockItem()->activate();

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }

        $this->logSuccessfulOperation($marketplace);
    }

    //####################################

    protected function receiveFromAmazon(Ess_M2ePro_Model_Marketplace $marketplace, $partNumber)
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('marketplace','get','categories',
                                                               array('part_number' => $partNumber,
                                                                     'marketplace' => $marketplace->getNativeId()),
                                                               NULL,NULL,NULL);

        $response = $dispatcherObject->process($connectorObj);

        if (is_null($response) || empty($response['data'])) {
            $response = array();
        }

        $dataCount = isset($response['data']) ? count($response['data']) : 0;
        $this->getActualOperationHistory()->addText("Total received Categories from Amazon: {$dataCount}");
        return $response;
    }

    protected function saveCategoriesToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $categories)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        if (!count($categories)) {
            return;
        }

        $iteration            = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep   = ($this->getPercentsInterval()/2) / (count($categories)/$iterationsForOneStep);
        $categoriesCount      = count($categories);
        $insertData           = array();

        for ($i = 0; $i < $categoriesCount; $i++) {
            $data = $categories[$i];

            $insertData[] = array(
                'category_id'         => $data['id'],
                'marketplace_id'      => $marketplace->getId(),
                'parent_category_id'  => $data['parent_id'],
                'title'               => $data['title'],
                'path'                => $data['path'],
                'is_listable'         => $data['is_listable'],
                'product_data_nick'   => ($data['is_listable'] ? $data['product_data']['nick'] : NULL),
                'browsenode_id'       => ($data['is_listable'] ? $data['browsenode_id'] : NULL),
                'keywords'            => ($data['is_listable'] ? json_encode($data['keywords']) : NULL),
                'required_attributes' => ($data['is_listable'] ? json_encode($data['required_attributes']) : NULL)
            );

            if (count($insertData) >= 100 || $i >= ($categoriesCount - 1)) {
                $connWrite->insertMultiple($tableCategories, $insertData);
                $insertData = array();
            }

            if (++$iteration % $iterationsForOneStep == 0) {
                $percentsShift = ($iteration/$iterationsForOneStep) * $percentsForOneStep;
                $this->getActualLockItem()->setPercents(
                    $this->getPercentsStart() + $this->getPercentsInterval()/2 + $percentsShift
                );
            }
        }
    }

    protected function deleteAllCategories(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $marketplace->getId()));
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // M2ePro_TRANSLATIONS
        // The "Categories" Action for %amazon% Marketplace: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories" Action for %amazon% Marketplace: "%mrk%" has been successfully completed.',
            array('!amazon' => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
                  'mrk'    => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}