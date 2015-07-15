<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Categories
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
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
        return 100;
    }

    //####################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(
            Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::CACHE_TAG
        );
    }

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualOperationHistory()->addText('Starting Marketplace "'.$marketplace->getTitle().'"');

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                                                         'Get Categories from eBay');
        $categories = $this->receiveFromEbay($marketplace);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->getActualLockItem()->activate();

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),
                                                         'Save Categories to DB');
        $this->saveCategoriesToDb($marketplace,$categories);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        $this->logSuccessfulOperation($marketplace);
    }

    //####################################

    protected function receiveFromEbay(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','info',
                                                            array('include_categories' => 1),'info',
                                                            $marketplace->getId(),NULL,NULL);

        $categories = $dispatcherObj->process($connectorObj);

        if (is_null($categories)) {
            $categories = array();
        } else {
            $categories = $categories['categories'];
        }

        $this->getActualOperationHistory()->addText('Total received Categories from eBay: '.count($categories));

        return $categories;
    }

    protected function saveCategoriesToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $categories)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $marketplace->getId()));

        $iteration = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep = ($this->getPercentsInterval()/2) / (count($categories)/$iterationsForOneStep);

        foreach ($categories as $data) {

            $insertData = array(
                'marketplace_id' => $marketplace->getId(),
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'parent_category_id' => $data['parent_id'],
                'level' => $data['level'],
                'is_leaf' => $data['is_leaf'],
                'features' => json_encode($data['features'])
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
        // M2ePro_TRANSLATIONS
        // The "Categories" Action for eBay Site: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories" Action for eBay Site: "%mrk%" has been successfully completed.',
            array('mrk'=>$marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}