<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_Requester
{
    const STATUS_ERROR    = 1;
    const STATUS_WARNING  = 2;
    const STATUS_SUCCESS  = 3;

    // ########################################

    public static function getMainStatus($statuses)
    {
        foreach (array(self::STATUS_ERROR,self::STATUS_WARNING) as $status) {
            if (in_array($status, $statuses)) {
                return $status;
            }
        }

        return self::STATUS_SUCCESS;
    }

    // ########################################
}