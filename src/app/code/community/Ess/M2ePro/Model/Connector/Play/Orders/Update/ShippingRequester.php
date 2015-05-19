<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Orders_Update_ShippingRequester
    extends Ess_M2ePro_Model_Connector_Play_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','update','dispatch');
    }

    // ########################################

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

    public function eventBeforeExecuting()
    {
        parent::eventBeforeExecuting();
        $this->deleteProcessedChanges();
    }

    // ----------------------------------------

    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        if (!isset($this->params['items']) || !is_array($this->params['items'])) {
            return;
        }

        $ordersIds = array();

        foreach ($this->params['items'] as $updateData) {
            if (!isset($updateData['order_id'])) {
                throw new LogicException('Order ID is not defined.');
            }

            $ordersIds[] = (int)$updateData['order_id'];
        }

        /** @var Ess_M2ePro_Model_Order[] $orders */
        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Play::NICK)
            ->addFieldToFilter('id', array('in' => $ordersIds))
            ->getItems();

        foreach ($orders as $order) {
            $order->addObjectLock('update_shipping_status', $processingRequest->getHash());
        }
    }

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