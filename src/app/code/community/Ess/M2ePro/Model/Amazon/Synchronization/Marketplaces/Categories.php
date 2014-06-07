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
        return 25;
    }

    protected function getPercentsEnd()
    {
        return 75;
    }

    //####################################

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Marketplace', (int)$params['marketplace_id']
        );

        $this->getActualOperationHistory()->addText('Starting marketplace "'.$marketplace->getTitle().'"');

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get categories from Amazon');
        $categories = $this->receiveFromAmazon($marketplace);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->getActualLockItem()->activate();

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save categories to DB');
        $this->saveCategoriesToDb($marketplace,$categories);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        $this->logSuccessfulOperation($marketplace);
    }

    //####################################

    protected function receiveFromAmazon(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $categories = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher')
                            ->processVirtual('marketplace','get','info',
                                             array('include_categories' => true,
                                                   'marketplace' => $marketplace->getNativeId()),
                                             'info',NULL,NULL);

        $categories = is_null($categories) ? array() : $categories['categories'];

        $this->getActualOperationHistory()->addText('Total received categories from Amazon: '.count($categories));

        return $categories;
    }

    protected function saveCategoriesToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $categories)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $marketplace->getId()));

        if (!count($categories)) {
            return;
        }

        $iteration = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep = ($this->getPercentsInterval()/2) / (count($categories)/$iterationsForOneStep);

        foreach ($categories as $data) {

            $insertData = array(
                'category_id'        => $data['id'],
                'marketplace_id'     => $marketplace->getId(),
                'parent_category_id' => $data['parent_id'],
                'node_hash'          => $data['node_hash'],
                'xsd_hash'           => $data['xsd_hash'],
                'title'              => $data['title'],
                'path'               => $data['path'],
                'item_types'         => $data['item_types'],
                'browsenode_id'      => $data['browsenode_id'],
                'is_listable'        => $data['is_listable'],
                'sorder'             => $data['sorder']
            );
            $connWrite->insert($tableCategories, $insertData);

            if (++$iteration % $iterationsForOneStep == 0) {
                $percentsShift = ($iteration/$iterationsForOneStep) * $percentsForOneStep;
                $this->getActualLockItem()->setPercents(
                    $this->getPercentsStart() + $this->getPercentsInterval()/2 + $percentsShift
                );
            }
        }
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // ->__('The "Categories" action for Amazon Marketplace: "%mrk%" has been successfully completed.');

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories" action for Amazon Marketplace: "%mrk%" has been successfully completed.',
            array('mrk' => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}