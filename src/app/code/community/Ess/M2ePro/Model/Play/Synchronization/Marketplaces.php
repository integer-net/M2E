<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Play_Synchronization_Marketplaces
    extends Ess_M2ePro_Model_Play_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::MARKETPLACES;
    }

    protected function getNick()
    {
        return NULL;
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

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        $params = $this->getParams();

        if (empty($params['marketplace_id'])) {
            return false;
        }

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component')
                            ->getUnknownObject('Marketplace', (int)$params['marketplace_id']);

        if (!$marketplace->isComponentModePlay() || !$marketplace->isStatusEnabled()) {
            return false;
        }

        return true;
    }

    protected function configureLockItemBeforeStart()
    {
        parent::configureLockItemBeforeStart();

        $componentName = '';
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Play::TITLE.' ';
        }

        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Play')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualLockItem()->setTitle(Mage::helper('M2ePro')->__($componentName.$marketplace->getTitle()));
    }

    public function performActions()
    {
        $this->logSuccessfulOperation();
        return true;
    }

    //####################################

    protected function logSuccessfulOperation()
    {
        $params = $this->getParams();

        $marketplace = Mage::helper('M2ePro/Component_Play')->getCachedObject(
            'Marketplace', (int)$params['marketplace_id']
        );
        // M2ePro_TRANSLATIONS
        // The "Categories" action for Play Marketplace: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories" action for Play Marketplace: "%mrk%" has been successfully completed.',
            array('mrk' => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //####################################
}