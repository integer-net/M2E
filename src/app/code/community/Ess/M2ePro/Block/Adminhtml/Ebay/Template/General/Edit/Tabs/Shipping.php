<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_General_Edit_Tabs_Shipping extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateGeneralEditTabsShipping');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/general/shipping.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $unsortedCountries = Mage::getModel('directory/country_api')->items();

        $unsortedCountriesNames = array();
        foreach($unsortedCountries as $country) {
            $unsortedCountriesNames[] = $country['name'];
        }
        sort($unsortedCountriesNames,SORT_STRING);

        $sortedCountries = array();
        foreach($unsortedCountriesNames as $name) {
            foreach($unsortedCountries as $country) {
                if ($country['name'] == $name) {
                    $sortedCountries[] = $country;
                    break;
                }
            }
        }

        $this->setData('countries', $sortedCountries);
        //------------------------------

        //------------------------------
        $accountsShippingDiscountProfiles = array();
        $accounts = Mage::getModel('M2ePro/Ebay_Account')
            ->getCollection()
            ->addFieldToFilter('ebay_shipping_discount_profiles', array('notnull' => true))
            ->getItems();

        foreach ($accounts as $account) {
            $accountProfiles = json_decode($account->getData('ebay_shipping_discount_profiles'), true);

            if (!is_array($accountProfiles)) {
                continue;
            }

            foreach ($accountProfiles as $marketplaceId => $profiles) {
                if (!isset($accountProfiles[$marketplaceId]['profiles'])) {
                    continue;
                }

                $profiles = array();
                foreach ($accountProfiles[$marketplaceId]['profiles'] as $profile) {
                    $profiles[] = array(
                        'marketplace_id' => $marketplaceId,
                        'type' => Mage::helper('M2ePro')->escapeHtml($profile['type']),
                        'profile_id' => Mage::helper('M2ePro')->escapeHtml($profile['profile_id']),
                        'profile_name' => Mage::helper('M2ePro')->escapeHtml($profile['profile_name'])
                    );
                }

                $accountsShippingDiscountProfiles[] = array(
                    'id' => $account->getId(),
                    'profiles' => $profiles
                );
            }
        }

        $this->setData('accounts_shipping_discount_profiles', json_encode($accountsShippingDiscountProfiles));
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add Method'),
                                'onclick' => 'EbayTemplateGeneralShippingHandlerObj.addRow(\'local\');',
                                'class' => 'add add_local_shipping_method_button'
                            ) );
        $this->setChild('add_local_shipping_method_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add Method'),
                                'onclick' => 'EbayTemplateGeneralShippingHandlerObj.addRow(\'international\');',
                                'class' => 'add add_international_shipping_method_button'
                            ) );
        $this->setChild('add_international_shipping_method_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Remove'),
                                'onclick' => 'EbayTemplateGeneralShippingHandlerObj.removeRow(this, \'%type%\');',
                                'class' => 'delete icon-btn remove_shipping_method_button'
                            ) );
        $this->setChild('remove_shipping_method_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh Profiles'),
                                'onclick' => 'EbayTemplateGeneralShippingHandlerObj.updateShippingDiscountProfiles();',
                                'class' => 'update_shipping_discount_profiles'
                            ) );
        $this->setChild('update_shipping_discount_profiles',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################
}