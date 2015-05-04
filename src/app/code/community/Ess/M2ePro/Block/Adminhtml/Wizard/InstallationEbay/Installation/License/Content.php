<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_License_Content
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationLicense');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationEbay/installation/license.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        //-------------------------------
        $countries = Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray();
        $countries[0] = array(
            'value' => '',
            'label' => '',
        );
        $this->setData('available_countries', $countries);
        //-------------------------------

        //-------------------------------
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY : 'shipping/origin/city';
        $user['city'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE : 'shipping/origin/postcode';
        $user['postal_code'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $user['country'] = Mage::getStoreConfig('general/country/default', $defaultStoreId);
        //-------------------------------

        //-------------------------------
        $earlierFormData = Mage::getModel('M2ePro/Registry')->load('wizard_license_form_data', 'key')
                                                            ->getData('value');

        if ($earlierFormData) {
            $earlierFormData = (array)json_decode($earlierFormData, true);
            $user = array_merge($user, $earlierFormData);
        }

        $this->addData($user);
        $this->setData('isLicenseStepFinished', $earlierFormData && Mage::helper('M2ePro/Module_License')->getKey());
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => '',
                'id' => 'license_popup_confirm_button'
            ) );
        $this->setChild('license_popup_confirm_button',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Sign into eBay'),
                'onclick' => '',
                'id' => 'sign_into_ebay_button'
            ) );
        $this->setChild('sign_into_ebay_button',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $url = 'https://scgi.ebay.com/ws/eBayISAPI.dll?RegisterEnterInfo&bizflow=2';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Register for eBay'),
                'onclick' => 'window.open(\''.$url.'\',\'_blank\')',
                'id' => 'register_on_ebay_button'
            ) );
        $this->setChild('register_on_ebay_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    public function getCountryLabelByCode($code)
    {
        foreach (Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray() as $country) {
            if ($country['value'] == $code) {
                return $country['label'];
            }
        }

        if (!empty($code)) {
            return $code;
        }

        return '';
    }

    // ########################################
}