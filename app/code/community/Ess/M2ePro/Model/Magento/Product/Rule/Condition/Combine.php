<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    // ####################################

    static protected $_conditionModels = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Magento_Product_Rule_Condition_Combine');
    }

    // ####################################

    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        foreach ($this->getConditions() as $condition) {
            $condition->setStoreId($storeId);
        }

        return $this;
    }

    // ####################################

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('M2ePro/Magento_Product_Rule_Condition_Product');
        $productAttributes = $productCondition->loadAttributeOptionsByCriteria(
            $this->getRule()->getAttributeCriteria(), $this->getRule()->getAttributeSets()
        )->getAttributeOption();

        $attributes = array();
        foreach ($productAttributes as $code => $label) {
            $attributes[] = array(
                'value' => 'M2ePro/Magento_Product_Rule_Condition_Product|'.$code,
                'label' => $label
            );
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array(
                'value' => 'M2ePro/Magento_Product_Rule_Condition_Combine',
                'label' => Mage::helper('M2ePro')->__('Conditions Combination')
            ),
            array('label' => Mage::helper('M2ePro')->__('Product Attribute'), 'value' => $attributes),
        ));

        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function loadArray($arr, $key='conditions')
    {
        $this->setAggregator(isset($arr['aggregator']) ? $arr['aggregator']
            : (isset($arr['attribute']) ? $arr['attribute'] : null))
            ->setValue(isset($arr['value']) ? $arr['value']
                : (isset($arr['operator']) ? $arr['operator'] : null));

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $condArr) {
                try {
                    $cond = $this->_getNewConditionModelInstance($condArr['type']);
                    if ($cond) {

                        if ($cond instanceof Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine) {
                            $cond->setData($this->getPrefix(), array());
                        }

                        $this->addCondition($cond);
                        $cond->loadArray($condArr, $key);
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
    }

    protected function _getNewConditionModelInstance($modelClass)
    {
        if (empty($modelClass)) {
            return false;
        }

        if (!array_key_exists($modelClass, self::$_conditionModels)) {
            $model = Mage::getModel($modelClass);
            self::$_conditionModels[$modelClass] = $model;
        } else {
            $model = self::$_conditionModels[$modelClass];
        }

        if (!$model) {
            return false;
        }

        $newModel = clone $model;
        return $newModel;
    }

    // ####################################
}