<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Module_EbayController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Reset 3rd Party"
     * @description "Clear all eBay 3rd party items for all Accounts"
     */
    public function resetOtherListingsAction()
    {
        $listingOther = Mage::getModel('M2ePro/Listing_Other');
        $ebayListingOther = Mage::getModel('M2ePro/Ebay_Listing_Other');

        $stmt = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other')->getSelect()->query();

        foreach ($stmt as $row) {
            $listingOther->setData($row);
            $ebayListingOther->setData($row);

            $listingOther->setChildObject($ebayListingOther);
            $ebayListingOther->setParentObject($listingOther);

            $listingOther->deleteInstance();
        }

        foreach (Mage::helper('M2ePro/Component_Ebay')->getCollection('Account') as $account) {
            $account->setData('other_listings_last_synchronization',NULL)->save();
        }

        $this->_getSession()->addSuccess('Successfully removed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //#############################################
}