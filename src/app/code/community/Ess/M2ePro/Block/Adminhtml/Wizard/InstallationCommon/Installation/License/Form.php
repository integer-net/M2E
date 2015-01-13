<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation_License_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('createLicenseForm');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/license/form/createLicense.phtml');
    }

    // ########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_license/confirmKey'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        //-------------------------------

        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        $countries = Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray();
        $countries[0] = array(
            'value' => '',
            'label' => '',
        );
        $this->setData(
             'countries',
                 $countries
        );

        $this->setData(
             'country',
                 Mage::getStoreConfig('general/country/default',$defaultStoreId)
        );

        //-------------------------------

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
                    ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY
                    : 'shipping/origin/city';
        $user['city'] = Mage::getStoreConfig(
                            $tempPath, $defaultStoreId
        );

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
                    ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE
                    : 'shipping/origin/postcode';
        $user['postal_code'] = Mage::getStoreConfig(
                                   $tempPath, $defaultStoreId
        );

        $this->addData($user);

        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                        'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                        'onclick' => '',
                                        'id' => 'form_confirm_button'
                                     ) );
        $this->setChild('form_confirm_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    public function getCountryLabelByCode($code)
    {
        $countryLabel = '';

        foreach (Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray() as $country) {
            if ($country['value'] == $code) {
                $countryLabel = $country['label'];
                break;
            }
        }

        return $countryLabel;
    }

    // ########################################
}