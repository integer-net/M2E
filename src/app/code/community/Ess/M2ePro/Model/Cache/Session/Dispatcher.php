<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Cache_Session_Dispatcher
{
    private static $cache = array();

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Cache_Session_Object
     * @param array $tags
     **/
    public function getCache(array $tags = array())
    {
        foreach (self::$cache as $cacheData) {
            if ($cacheData['tags'] == $tags) {
                return $cacheData['object'];
            }
        }

        self::$cache[] = $cacheData = array(
            'tags' => $tags,
            'object' => Mage::getModel('M2ePro/Cache_Session_Object'),
        );

        return $cacheData['object'];
    }

    public function clearCache(array $tags = array(), $strict = false)
    {
        foreach (self::$cache as $cacheData) {

            if ($strict) {

                if ($cacheData['tags'] == $tags) {
                    $cacheData['object']->clear();
                }

            } else {

                foreach ($tags as $nick => $tag) {

                    $cacheTagNick = array_search($tag,$cacheData['tags']);

                    if ($cacheTagNick === false) {
                        continue;
                    }

                    if ($cacheTagNick != $nick) {
                        continue;
                    }

                    $cacheData['object']->clear();
                }

            }
        }

        return $this;
    }

    // ########################################
}