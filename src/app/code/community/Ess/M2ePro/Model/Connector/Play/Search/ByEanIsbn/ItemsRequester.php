<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Play_Search_ByEanIsbn_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Play_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byEanIsbn');
    }

    // ########################################

    abstract protected function getQuery();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'query' => $this->getQuery(),
        );
    }

    // ########################################
}