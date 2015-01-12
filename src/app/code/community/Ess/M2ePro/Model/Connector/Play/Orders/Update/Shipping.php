<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Orders_Update_Shipping
    extends Ess_M2ePro_Model_Connector_Play_Requester
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

        foreach ($this->params['items'] as $updateData) {
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

        foreach ($this->params['items'] as $updateData) {
            $items[] = array(
                'order_id'        => $updateData['play_order_id'],
                'carrier_name'    => $updateData['carrier_name'],
                'tracking_number' => $updateData['tracking_number'],
            );
        }

        return array('items' => $items);
    }

    // ########################################

    public function process()
    {
        parent::process();

        $this->deleteProcessedChanges();
    }

    // ########################################

    private function deleteProcessedChanges()
    {
        // collect ids of processed order changes
        //------------------------------
        $changeIds = array();

        foreach ($this->params['items'] as $updateData) {
            if (!is_array($updateData)) {
                continue;
            }

            $changeIds[] = $updateData['change_id'];
        }
        //------------------------------

        Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($changeIds);
    }

    // ########################################
}