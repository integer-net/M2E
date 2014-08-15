<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Search_ByAsin_Items
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byAsin');
    }

    // ########################################

    abstract protected function getQueryItem();

    abstract protected function getOnlyRealTime();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'item' => $this->getQueryItem(),
            'only_realtime' => $this->getOnlyRealTime()
        );
    }

    // ########################################
}