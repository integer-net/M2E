<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CUSTOM_IDENTIFIER = 'm2epro_extension';

    // ########################################

    /**
     * @param $modelName
     * @param array $params
     * @return Ess_M2ePro_Model_Abstract
     */
    public function getModel($modelName, $params = array())
    {
        return Mage::getModel('M2ePro/'.$modelName,$params);
    }

    public function getHelper($helperName = NULL)
    {
        is_string($helperName) && $helperName = '/'.$helperName;
        return Mage::helper('M2ePro'.(string)$helperName);
    }

    // ########################################

    public function getCurrentGmtDate($returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->gmtTimestamp();
        }
        return Mage::getModel('core/date')->gmtDate($format);
    }

    public function getCurrentTimezoneDate($returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->timestamp();
        }
        return Mage::getModel('core/date')->date($format);
    }

    //-----------------------------------------

    public function getDate($date, $returnTimestamp = false, $format = NULL)
    {
        if (is_numeric($date)) {
            $result = (int)$date;
        } else {
            $result = strtotime($date);
        }

        if (is_null($format)) {
            $format = 'Y-m-d H:i:s';
        }

        $result = date($format, $result);

        if ($returnTimestamp) {
            return strtotime($result);
        }

        return $result;
    }

    //-----------------------------------------

    public function gmtDateToTimezone($dateGmt, $returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->timestamp($dateGmt);
        }
        return Mage::getModel('core/date')->date($format,$dateGmt);
    }

    public function timezoneDateToGmt($dateTimezone, $returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->gmtTimestamp($dateTimezone);
        }
        return Mage::getModel('core/date')->gmtDate($format,$dateTimezone);
    }

    // ########################################

    public function escapeJs($string)
    {
        return str_replace(array("\\"  , "\n"  , "\r" , "\""  , "'"),
                           array("\\\\", "\\n" , "\\r", "\\\"", "\\'"),
                           $string);
    }

    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    // ########################################

    public function getClassConstantAsJson($class)
    {
        $class = 'Ess_M2ePro_'.$class;

        $reflectionClass = new ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        $constants = array();
        foreach ($tempConstants as $key => $value) {
            $constants[] = array(strtoupper($key), $value);
        }

        return json_encode($constants);
    }

    public function convertStringToSku($title)
    {
        $skuVal = strtolower($title);
        $skuVal = str_replace(array(" ", ":", ",", ".", "?", "*", "+", "(", ")", "&", "%", "$", "#", "@",
                                    "!", '"', "'", ";", "\\", "|", "/", "<", ">"), "-", $skuVal);

        return $skuVal;
    }

    public function stripInvisibleTags($text)
    {
        $text = preg_replace(
            array(
                // Remove invisible content
                '/<head[^>]*?>.*?<\/head>/siu',
                '/<style[^>]*?>.*?<\/style>/siu',
                '/<script[^>]*?.*?<\/script>/siu',
                '/<object[^>]*?.*?<\/object>/siu',
                '/<embed[^>]*?.*?<\/embed>/siu',
                '/<applet[^>]*?.*?<\/applet>/siu',
                '/<noframes[^>]*?.*?<\/noframes>/siu',
                '/<noscript[^>]*?.*?<\/noscript>/siu',
                '/<noembed[^>]*?.*?<\/noembed>/siu',

                // Add line breaks before & after blocks
                '/<((br)|(hr))/iu',
                '/<\/?((address)|(blockquote)|(center)|(del))/iu',
                '/<\/?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))/iu',
                '/<\/?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))/iu',
                '/<\/?((table)|(th)|(td)|(caption))/iu',
                '/<\/?((form)|(button)|(fieldset)|(legend)|(input))/iu',
                '/<\/?((label)|(select)|(optgroup)|(option)|(textarea))/iu',
                '/<\/?((frameset)|(frame)|(iframe))/iu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0",
            ),
            $text);

        return $text;
    }

    public static function arrayReplaceRecursive($base, $replacements)
    {
        foreach (array_slice(func_get_args(), 1) as $replacements) {

            $bref_stack = array(&$base);
            $head_stack = array($replacements);

            do {
                end($bref_stack);

                $bref = &$bref_stack[key($bref_stack)];
                $head = array_pop($head_stack);

                unset($bref_stack[key($bref_stack)]);

                foreach (array_keys($head) as $key) {

                    if (isset($key, $bref, $bref[$key]) && is_array($bref[$key]) && is_array($head[$key])) {
                        $bref_stack[] = &$bref[$key];
                        $head_stack[] = $head[$key];
                    } else {
                        $bref[$key] = $head[$key];
                    }

                }
            } while(count($head_stack));
        }

        return $base;
    }

    // ########################################

    public function makeBackUrlParam($backIdOrRoute, array $backParams = array())
    {
        $paramsString = count($backParams) > 0 ? '|'.http_build_query($backParams,'','&') : '';
        return base64_encode($backIdOrRoute.$paramsString);
    }

    public function getBackUrlParam($defaultBackIdOrRoute = 'index',
                                    array $defaultBackParams = array())
    {
        $requestParams = Mage::helper('M2ePro')->getGlobalValue('request_params');
        return isset($requestParams['back'])
            ? $requestParams['back'] : $this->makeBackUrlParam($defaultBackIdOrRoute,$defaultBackParams);
    }

    //------------------------------------------

    public function getBackUrl($defaultBackIdOrRoute = 'index',
                               array $defaultBackParams = array(),
                               array $extendedRoutersParams = array())
    {
        $back = base64_decode($this->getBackUrlParam($defaultBackIdOrRoute,$defaultBackParams));

        $route = '';
        $params = array();

        if (strpos($back,'|') !== false) {
            $route = substr($back,0,strpos($back,'|'));
            parse_str(substr($back,strpos($back,'|')+1),$params);
        } else {
            $route = $back;
        }

        $extendedRoutersParamsTemp = array();
        foreach ($extendedRoutersParams as $extRouteName => $extParams) {
            if ($route == $extRouteName) {
                $params = array_merge($params,$extParams);
            } else {
                $extendedRoutersParamsTemp[$route] = $params;
            }
        }
        $extendedRoutersParams = $extendedRoutersParamsTemp;

        $route == 'index' && $route = '*/*/index';
        $route == 'list' && $route = '*/*/index';
        $route == 'edit' && $route = '*/*/edit';
        $route == 'view' && $route = '*/*/view';

        foreach ($extendedRoutersParams as $extRouteName => $extParams) {
            if ($route == $extRouteName) {
                $params = array_merge($params,$extParams);
            }
        }

        return Mage::helper('M2ePro/Magento')->getUrl($route,$params);
    }

    // ########################################

    public function getCacheValue($key)
    {
        $cacheKey = self::CUSTOM_IDENTIFIER.'_'.$key;
        $value = Mage::app()->getCache()->load($cacheKey);
        $value !== false && $value = unserialize($value);
        return $value;
    }

    public function setCacheValue($key, $value, array $tags = array(), $lifeTime = NULL)
    {
        if (is_null($lifeTime) || (int)$lifeTime <= 0) {
            $lifeTime = 60*60*24*365*5;
        }

        $cacheKey = self::CUSTOM_IDENTIFIER.'_'.$key;

        $preparedTags = array(self::CUSTOM_IDENTIFIER.'_main');
        foreach ($tags as $tag) {
            $preparedTags[] = self::CUSTOM_IDENTIFIER.'_'.$tag;
        }

        Mage::app()->getCache()->save(serialize($value), $cacheKey, $preparedTags, (int)$lifeTime);
    }

    //-----------------------------------------

    public function removeCacheValue($key)
    {
        $cacheKey = self::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::app()->getCache()->remove($cacheKey);
    }

    public function removeTagCacheValues($tag)
    {
        $mode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
        $tags = array(self::CUSTOM_IDENTIFIER.'_'.$tag);
        Mage::app()->getCache()->clean($mode,$tags);
    }

    public function removeAllCacheValues()
    {
        $this->removeTagCacheValues('main');
    }

    // ########################################

    public function getSessionValue($key, $clear = false)
    {
        return Mage::getSingleton('adminhtml/session')->getData(self::CUSTOM_IDENTIFIER.'_'.$key, $clear);
    }

    public function setSessionValue($key, $value)
    {
        Mage::getSingleton('adminhtml/session')->setData(self::CUSTOM_IDENTIFIER.'_'.$key, $value);
    }

    public function getAllSessionValues()
    {
        $return = array();
        $session = Mage::getSingleton('adminhtml/session')->getData();
        foreach ($session as $key => $value) {
            if (substr($key, 0, strlen(self::CUSTOM_IDENTIFIER)) == self::CUSTOM_IDENTIFIER) {
                $tempReturnedKey = substr($key, strlen(self::CUSTOM_IDENTIFIER)+1);
                $return[$tempReturnedKey] = Mage::getSingleton('adminhtml/session')->getData($key);
            }
        }
        return $return;
    }

    //-----------------------------------------

    public function removeSessionValue($key)
    {
        Mage::getSingleton('adminhtml/session')->getData(self::CUSTOM_IDENTIFIER.'_'.$key, true);
    }

    public function removeAllSessionValues()
    {
        $session = Mage::getSingleton('adminhtml/session')->getData();
        foreach ($session as $key => $value) {
            if (substr($key, 0, strlen(self::CUSTOM_IDENTIFIER)) == self::CUSTOM_IDENTIFIER) {
                Mage::getSingleton('adminhtml/session')->getData($key, true);
            }
        }
    }

    // ########################################

    public function getGlobalValue($key)
    {
        $globalKey = self::CUSTOM_IDENTIFIER.'_'.$key;
        return Mage::registry($globalKey);
    }

    public function setGlobalValue($key, $value)
    {
        $globalKey = self::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::register($globalKey,$value,!Mage::helper('M2ePro/Server')->isDeveloper());
    }

    public function unsetGlobalValue($key)
    {
        $globalKey = self::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::unregister($globalKey);
    }

    // ########################################
}