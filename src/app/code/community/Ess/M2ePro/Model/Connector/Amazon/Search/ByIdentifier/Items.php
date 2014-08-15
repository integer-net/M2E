<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Search_ByIdentifier_Items
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byIdentifier');
    }

    // ########################################

    abstract protected function getQueryItem();

    abstract protected function getIdType();

    abstract protected function getOnlyRealTime();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'item' => $this->getQueryItem(),
            'id_type' => $this->getIdType(),
            'only_realtime' => $this->getOnlyRealTime()
        );
    }

    // ########################################
}