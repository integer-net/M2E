<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_EbayOtherListingController extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getNick()
    {
        return 'ebayOtherListing';
    }

    //#############################################

    public function congratulationAction()
    {
        $this->_redirect(
            '*/adminhtml_listingOther',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY)
        );
    }

    //#############################################

    public function resetAction()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $mainOtherListingsTable = Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable();
        $connWrite->delete($mainOtherListingsTable,array('component_mode = ?'=>Ess_M2ePro_Helper_Component_Ebay::NICK));

        $secondOtherListingsTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Other')->getMainTable();
        $connWrite->delete($secondOtherListingsTable);

        $accounts = Mage::getModel('M2ePro/Ebay_Account')->getCollection();
        foreach ($accounts as $account) {
            /** @var $account Ess_M2ePro_Model_Ebay_Account */
            $account->setData('other_listings_last_synchronization',NULL)->save();
        }

        exit();
    }

    //#############################################
}