<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Order_Helper
{
    const PLAY_STATUS_PENDING  = 'Sale Pending';
    const PLAY_STATUS_SOLD     = 'Sold';
    const PLAY_STATUS_POSTED   = 'Posted';
    const PLAY_STATUS_CANCELED = 'Cancelled';
    const PLAY_STATUS_REFUNDED = 'Refunded';

    public function getStatus($playStatus)
    {
        switch ($playStatus) {
            case self::PLAY_STATUS_SOLD:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_SOLD;
                break;
            case self::PLAY_STATUS_POSTED:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_POSTED;
                break;
            case self::PLAY_STATUS_CANCELED:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_CANCELED;
                break;
            case self::PLAY_STATUS_REFUNDED:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_REFUNDED;
                break;
            case self::PLAY_STATUS_PENDING:
            default:
                $status = Ess_M2ePro_Model_Play_Order::STATUS_PENDING;
                break;
        }

        return $status;
    }
}