<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Log extends Mage_Core_Model_Abstract
{
    const TYPE_SUCCESS = 0;
    const TYPE_NOTICE  = 1;
    const TYPE_ERROR   = 2;
    const TYPE_WARNING = 3;

    const INITIATOR_UNKNOWN   = 0;
    const INITIATOR_USER      = 1;
    const INITIATOR_EXTENSION = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Log');
    }

    // ########################################

    public function add($componentMode, $orderId, $message, $type, $initiator = self::INITIATOR_UNKNOWN)
    {
        if (!in_array($type, array(self::TYPE_SUCCESS, self::TYPE_NOTICE, self::TYPE_ERROR, self::TYPE_WARNING))) {
            throw new InvalidArgumentException('Invalid order log type.');
        }

        if (!in_array($initiator, array(self::INITIATOR_UNKNOWN, self::INITIATOR_USER, self::INITIATOR_EXTENSION))) {
            throw new InvalidArgumentException('Invalid order log initiator.');
        }

        $log = array(
            'component_mode' => $componentMode,
            'order_id'       => $orderId,
            'message'        => $message,
            'type'           => (int)$type,
            'initiator'      => (int)$initiator
        );

        $this->setId(null)
             ->setData($log)
             ->save();
    }

    // ########################################

    public function deleteInstance()
    {
        return parent::delete();
    }

    // ########################################
}