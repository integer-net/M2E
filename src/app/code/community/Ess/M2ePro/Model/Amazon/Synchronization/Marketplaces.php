<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces
    extends Ess_M2ePro_Model_Amazon_Synchronization_Abstract
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

        if (!$marketplace->isComponentModeAmazon() || !$marketplace->isStatusEnabled()) {
            return false;
        }

        return true;
    }

    protected function configureLockItemBeforeStart()
    {
        parent::configureLockItemBeforeStart();

        $componentName = '';
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        }

        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualLockItem()->setTitle(Mage::helper('M2ePro')->__($componentName.$marketplace->getTitle()));
    }

    public function performActions()
    {
        $result = true;

        $result = !$this->processTask('Marketplaces_Details') ? false : $result;
        $result = !$this->processTask('Marketplaces_Categories') ? false : $result;
        $result = !$this->processTask('Marketplaces_Specifics') ? false : $result;

        Mage::helper('M2ePro/Data_Cache')->removeTagValues('marketplace');

        return $result;
    }

    //####################################
}