<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Template_SellingFormat_Messages
    extends Ess_M2ePro_Block_Adminhtml_Template_SellingFormat_Messages
{
    const TYPE_CURRENCY_CONVERSION_EUR = 'currency_conversion_eur';
    const TYPE_CURRENCY_CONVERSION_GBP = 'currency_conversion_gbp';

    // ########################################

    public function getCurrencyConversionMessage($marketplaceCurrency = null)
    {
        if (is_null($marketplaceCurrency)) {
            return NULL;
        }

        return parent::getCurrencyConversionMessage($marketplaceCurrency);
    }

    // ########################################

    public function getMessages()
    {
        $messages = array();

        //------------------------------
        if (!is_null($message = $this->getCurrencyConversionMessage(Ess_M2ePro_Helper_Component_Play::CURRENCY_EUR))) {
            $messages[self::TYPE_CURRENCY_CONVERSION_EUR] = $message;
        }
        //------------------------------

        //------------------------------
        if (!is_null($message = $this->getCurrencyConversionMessage(Ess_M2ePro_Helper_Component_Play::CURRENCY_GBP))) {
            $messages[self::TYPE_CURRENCY_CONVERSION_GBP] = $message;
        }
        //------------------------------

        $messages = array_merge($messages, parent::getMessages());

        return $messages;
    }

    // ########################################
}