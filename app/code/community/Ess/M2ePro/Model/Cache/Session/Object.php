<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Cache_Session_Object
{
    private $cache = array();

    // ########################################

    public function getData($key)
    {
        $cacheKey = $this->getCacheKey($key);

        if (!isset($this->cache[$cacheKey])) {
            return NULL;
        }

        return $this->cache[$cacheKey];
    }

    public function setData($key,$value)
    {
        $this->cache[$this->getCacheKey($key)] = $value;

        return $value;
    }

    // ----------------------------------------

    public function clear()
    {
        $this->cache = array();
    }

    // ########################################

    private function getCacheKey($key)
    {
        return md5(json_encode($key));
    }

    // ########################################
}