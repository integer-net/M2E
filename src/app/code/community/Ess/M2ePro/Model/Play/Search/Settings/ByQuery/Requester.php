<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Settings_ByQuery_Requester
    extends Ess_M2ePro_Model_Connector_Play_Search_ByQuery_ItemsRequester
{
    // ########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    // ########################################
}