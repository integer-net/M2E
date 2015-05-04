<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Indexes_Enable extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        /** @var $index Ess_M2ePro_Model_Magento_Product_Index */
        $index = Mage::getSingleton('M2ePro/Magento_Product_Index');

        if (!$index->isIndexManagementEnabled()) {
            return;
        }

        $enabledIndexes = array();

        foreach ($index->getIndexes() as $code) {
            if ($index->isDisabledIndex($code) && $index->enableReindex($code)) {
                $index->forgetDisabledIndex($code);
                $enabledIndexes[] = $code;
            }
        }

        $executedIndexes = array();

        foreach ($enabledIndexes as $code) {
            if ($index->requireReindex($code) && $index->executeReindex($code)) {
                $executedIndexes[] = $code;
            }
        }

        if (count($executedIndexes) <= 0) {
            return;
        }

        Mage::getModel('M2ePro/Synchronization_Log')->addMessage(
            Mage::helper('M2ePro')->__('Product reindex was executed.'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    //####################################
}