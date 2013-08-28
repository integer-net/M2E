<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_VirtualRequester
   extends Ess_M2ePro_Model_Connector_Server_Amazon_Requester
{
    private $cache = array();

    // ########################################

    protected function getCommand()
    {
        if (isset($this->cache['command'])) {
            return $this->cache['command'];
        }

        $this->cache['command'] = $this->params['__command__'];
        unset($this->params['__command__']);

        return $this->cache['command'];
    }

    // ########################################

    protected function getResponserModel()
    {
        if (isset($this->cache['responser_model'])) {
            return $this->cache['responser_model'];
        }

        $this->cache['responser_model'] = $this->params['__responser_model__'];
        unset($this->params['__responser_model__']);

        return $this->cache['responser_model'];
    }

    protected function getResponserParams()
    {
        if (isset($this->cache['responser_params'])) {
            return $this->cache['responser_params'];
        }

        $this->cache['responser_params'] = $this->params['__responser_params__'];
        unset($this->params['__responser_params__']);

        return $this->cache['responser_params'];
    }

    // ########################################

    protected function setLocks($hash) {}

    // ########################################

    protected function getRequestData()
    {
        if (isset($this->cache['request_data'])) {
            return $this->cache['request_data'];
        }

        $this->cache['request_data'] = $this->params['__request_data__'];
        unset($this->params['__request_data__']);

        return $this->cache['request_data'];
    }

    // ########################################
}