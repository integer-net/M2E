<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_SimpleController
{
    //#############################################

    public function isMarketplaceEnabledAndSynchronizedAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $marketplaceObj = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',(int)$marketplaceId);

        $this->loadLayout();
        $this->getResponse()->setBody(json_encode(
            $marketplaceObj->isStatusEnabled() && $this->isMarketplaceSynchronized($marketplaceId)
        ));
    }

    private function isMarketplaceSynchronized($marketplaceId)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplace,'COUNT(*)')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId);

        return ($connRead->fetchOne($dbSelect) == 1);
    }

    //#############################################
}