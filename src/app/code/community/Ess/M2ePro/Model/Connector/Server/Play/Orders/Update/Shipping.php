<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Play_Orders_Update_Shipping
    extends Ess_M2ePro_Model_Connector_Server_Play_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','update','dispatch');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Play_Orders_Update_ShippingResponser';
    }

    protected function getResponserParams()
    {
        $params = array();

        foreach ($this->params as $updateData) {
            $params[$updateData['order_id']] = array(
                'order_id'        => $updateData['order_id'],
                'carrier_name'    => $updateData['carrier_name'],
                'tracking_number' => $updateData['tracking_number']
            );
        }

        return $params;
    }

    // ########################################

    protected function setLocks($hash) {}

    // ########################################

    protected function getRequestData()
    {
        $items = array();

        foreach ($this->params as $updateData) {
            $items[] = array(
                'order_id'        => $updateData['play_order_id'],
                'carrier_name'    => $updateData['carrier_name'],
                'tracking_number' => $updateData['tracking_number'],
            );
        }

        return array('items' => $items);
    }

    // ########################################
}