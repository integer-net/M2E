<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Search_ByQuery_Items
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byQuery');
    }

    // ########################################

    abstract protected function getQuery();

    abstract protected function getOnlyRealTime();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'query' => $this->getQuery(),
            'only_realtime' => $this->getOnlyRealTime()
        );
    }

    // ########################################
}