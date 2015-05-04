<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Variation_Cache extends Ess_M2ePro_Model_Magento_Product_Variation
{
    // ##########################################################

    public function getVariationsTypeStandard()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getVariationsTypeRaw()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getTitlesVariationSet()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    // ##########################################################

    protected function getMethodData($methodName, $params = null)
    {
        if (is_null($this->getMagentoProduct())) {
            throw new Exception('Magento Product was not set.');
        }

        $cacheKey = array(
            __CLASS__,
            $methodName,
        );

        if (!is_null($params)) {
            $cacheKey[] = $params;
        }

        $cacheResult = $this->getMagentoProduct()->getCacheValue($cacheKey);

        if ($this->getMagentoProduct()->isCacheEnabled() && !is_null($cacheResult)) {
            return $cacheResult;
        }

        if (!is_null($params)) {
            $data = call_user_func_array(array('parent', $methodName), $params);
        } else {
            $data = call_user_func(array('parent', $methodName));
        }

        if (!$this->getMagentoProduct()->isCacheEnabled()) {
            return $data;
        }

        return $this->getMagentoProduct()->setCacheValue($cacheKey, $data);
    }

    // ##########################################################
}