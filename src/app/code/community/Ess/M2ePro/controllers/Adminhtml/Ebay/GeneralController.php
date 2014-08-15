<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_SimpleController
{
    //#############################################

    public function isMarketplaceSynchronizedAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplace,'COUNT(*)')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId);

        $this->loadLayout();
        $this->getResponse()->setBody(json_encode(
            $connRead->fetchOne($dbSelect) == 1
        ));
    }

    //#############################################
}