<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_Edit_ShippingAddress_Form extends Mage_Adminhtml_Block_Widget_Form
{
    private $order;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrderEditShippingAddressForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/order/edit/shipping_address.phtml');
        $this->order = Mage::helper('M2ePro')->getGlobalValue('temp_data');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

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

        $buyerEmail = $this->order->getData('buyer_email');
        stripos($buyerEmail, 'Invalid Request') !== false && $buyerEmail = '';
        $this->setData('buyer_email', $buyerEmail);

        $this->setData('buyer_name', $this->order->getData('buyer_name'));
        $this->setData('address', $this->order->getShippingAddress()->getData());
        $this->setData('region_code', $this->order->getShippingAddress()->getRegionCode());

        return parent::_beforeToHtml();
    }
}