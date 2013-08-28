<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_ExternalTransaction extends Mage_Core_Model_Abstract
{
    const NOT_PAYPAL_TRANSACTION = 'SIS';

    // ########################################

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Order_ExternalTransaction');
    }

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            $this->order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', $this->getData('order_id'));
        }
        return $this->order;
    }

    // ########################################

    public function isPaypal()
    {
        return $this->getData('transaction_id') != self::NOT_PAYPAL_TRANSACTION;
    }

    public function getPaypalUrl()
    {
        if (!$this->isPaypal()) {
            return '';
        }

        $params = array(
            'cmd' => '_view-a-trans',
            'id'  => $this->getData('transaction_id')
        );

        $modePrefix = $this->getOrder()->getAccount()->getChildObject()->isModeSandbox() ? 'sandbox.' : '';
        $baseUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/other/paypal/', 'url');

        return 'https://www.' . $modePrefix . $baseUrl . '?' . http_build_query($params, '', '&');
    }

    // ########################################
}