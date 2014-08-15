<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Abstract
{
    // ####################################

    static protected $_conditionModels = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Magento_Product_Rule_Condition_Combine')
            ->setAggregator('all')
            ->setValue(true)
            ->setConditions(array())
            ->setActions(array());

        $this->loadAggregatorOptions();
        if ($options = $this->getAggregatorOptions()) {
            foreach ($options as $aggregator=>$dummy) { $this->setAggregator($aggregator); break; }
        }
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
        $productAttributes = Mage::getModel('M2ePro/Magento_Product_Rule_Condition_Product')->getAttributeOption();

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

    // ####################################

    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption(array(
            'all' => Mage::helper('rule')->__('ALL'),
            'any' => Mage::helper('rule')->__('ANY'),
        ));
        return $this;
    }

    public function getAggregatorSelectOptions()
    {
        $opt = array();
        foreach ($this->getAggregatorOption() as $k=>$v) {
            $opt[] = array('value'=>$k, 'label'=>$v);
        }
        return $opt;
    }

    public function getAggregatorName()
    {
        return $this->getAggregatorOption($this->getAggregator());
    }

    public function getAggregatorElement()
    {
        if (is_null($this->getAggregator())) {
            foreach ($this->getAggregatorOption() as $k=>$v) {
                $this->setAggregator($k);
                break;
            }
        }
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__aggregator', 'select', array(
            'name'=>'rule['.$this->getPrefix().']['.$this->getId().'][aggregator]',
            'values'=>$this->getAggregatorSelectOptions(),
            'value'=>$this->getAggregator(),
            'value_name'=>$this->getAggregatorName(),
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    // ####################################

    public function loadValueOptions()
    {
        $this->setValueOption(array(
            1 => Mage::helper('rule')->__('TRUE'),
            0 => Mage::helper('rule')->__('FALSE'),
        ));
        return $this;
    }

    public function addCondition($condition)
    {
        $condition->setRule($this->getRule());
        $condition->setObject($this->getObject());
        $condition->setPrefix($this->getPrefix());

        $conditions = $this->getConditions();
        $conditions[] = $condition;

        if (!$condition->getId()) {
            $condition->setId($this->getId().'--'.sizeof($conditions));
        }

        $this->setData($this->getPrefix(), $conditions);
        return $this;
    }

    public function getValueElementType()
    {
        return 'select';
    }

    // ####################################

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

    public function loadXml($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $arr = parent::loadXml($xml);
        foreach ($xml->conditions->children() as $condition) {
            $arr['conditions'] = parent::loadXml($condition);
        }
        $this->loadArray($arr);
        return $this;
    }

    // ####################################

    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml = "<aggregator>".$this->getAggregator()."</aggregator>"
            ."<value>".$this->getValue()."</value>"
            ."<$containerKey>";
        foreach ($this->getConditions() as $condition) {
            $xml .= "<$itemKey>".$condition->asXml()."</$itemKey>";
        }
        $xml .= "</$containerKey>";
        return $xml;
    }

    public function asArray(array $arrAttributes = array())
    {
        $out = parent::asArray();
        $out['aggregator'] = $this->getAggregator();

        foreach ($this->getConditions() as $condition) {
            $out['conditions'][] = $condition->asArray();
        }

        return $out;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().
            Mage::helper('M2ePro')->__(
                'If %rule% of these conditions are %value%:',
                $this->getAggregatorElement()->getHtml(),
                $this->getValueElement()->getHtml()
            );

        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml().
                '<ul id="'.$this->getPrefix().'__'.$this->getId().'__children" class="rule-param-children">';
        foreach ($this->getConditions() as $cond) {
            $html .= '<li>'.$cond->asHtmlRecursive().'</li>';
        }
        $html .= '<li>'.$this->getNewChildElement()->getHtml().'</li></ul>';
        return $html;
    }

    public function asString($format='')
    {
        $str = Mage::helper('M2ePro')->__("If %rule% of these conditions are %value%:",
                                          $this->getAggregatorName(), $this->getValueName());
        return $str;
    }

    public function asStringRecursive($level=0)
    {
        $str = parent::asStringRecursive($level);
        foreach ($this->getConditions() as $cond) {
            $str .= "\n".$cond->asStringRecursive($level+1);
        }
        return $str;
    }

    // ####################################

    public function getNewChildElement()
    {
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__new_child', 'select', array(
            'name'=>'rule['.$this->getPrefix().']['.$this->getId().'][new_child]',
            'values'=>$this->getNewChildSelectOptions(),
            'value_name'=>$this->getNewChildName(),
        ))->setRenderer(Mage::getBlockSingleton('rule/newchild'));
    }

    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all    = $this->getAggregator() === 'all';
        $true   = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validate($object);

            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getConditions() as $condition) {
            $condition->setJsFormObject($form);
        }
        return $this;
    }

    public function getConditions()
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';
        return $this->getData($key);
    }

    public function setConditions($conditions)
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';
        return $this->setData($key, $conditions);
    }

    // ####################################

    protected function _getRecursiveChildSelectOption()
    {
        return array('value' => $this->getType(), 'label' => Mage::helper('rule')->__('Conditions Combination'));
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
