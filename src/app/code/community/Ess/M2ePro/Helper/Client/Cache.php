<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Client_Cache extends Ess_M2ePro_Helper_Magento_Abstract
{
    // ################################

    const BACKEND_TYPE_APC       = 'apc';
    const BACKEND_TYPE_MEMCACHED = 'memcached';
    const BACKEND_TYPE_REDIS     = 'cm_cache_backend_redis';

    // ################################

    public function isApcAvailable()
    {
        return extension_loaded('apc') && ini_get('apc.enabled');
    }

    public function isMemchachedAvailable()
    {
        return (extension_loaded('memcache') || extension_loaded('memcached')) &&
               (class_exists('Memcache') || class_exists('Memcached'));
    }

    public function isRedisAvailable()
    {
        return extension_loaded('redis') && class_exists('Redis');
    }

    // ################################

    public function getBackend()
    {
        return strtolower((string)Mage::getConfig()->getNode('global/cache/backend'));
    }

    //---------------------------------

    public function isApcEnabled()
    {
        return $this->getBackend() == self::BACKEND_TYPE_APC;
    }

    public function isMemchachedEnabled()
    {
        return $this->getBackend() == self::BACKEND_TYPE_MEMCACHED;
    }

    public function isRedisEnabled()
    {
        return $this->getBackend() == self::BACKEND_TYPE_REDIS;
    }

    // ################################
}