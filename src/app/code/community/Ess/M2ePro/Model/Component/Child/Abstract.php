<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Component_Child_Abstract extends Ess_M2ePro_Model_Component_Abstract
{
    protected $parentObject = NULL;

    // ########################################

    public function setParentObject(Ess_M2ePro_Model_Component_Parent_Abstract $object)
    {
        if (is_null($object->getId())) {
            return;
        }

        $this->parentObject = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Component_Parent_Abstract
     * @throws LogicException
     */
    public function getParentObject()
    {
        if (is_null($this->getId())) {
             throw new LogicException('Method require loaded instance first');
        }

        if (!is_null($this->parentObject)) {
            return $this->parentObject;
        }

        $tempMode = $this->getComponentMode();

        if (is_null($tempMode)) {
            throw new LogicException('Set Component Mode first');
        }

        $modelName = str_replace('M2ePro/'.ucwords($tempMode).'_','',$this->_resourceName);
        $this->parentObject = Mage::helper('M2ePro')->getModel($modelName);

        $this->parentObject->setChildMode($tempMode);
        $this->parentObject->loadInstance($this->getId());
        $this->parentObject->setChildObject($this);

        return $this->parentObject;
    }

    // ########################################

    /**
     * @param string $modelName
     * @param string $fieldName
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array
     * @throws LogicException
     */
    protected function getRelatedComponentItems($modelName, $fieldName, $asObjects = false,
                                                array $filters = array(), array $sort = array())
    {
        if (is_null($this->getId())) {
             throw new LogicException('Method require loaded instance first');
        }

        $tempMode = $this->getComponentMode();

        if (is_null($tempMode)) {
             throw new LogicException('Set Component Mode first');
        }

        $tempModel = Mage::helper('M2ePro/Component')->getComponentModel($tempMode,$modelName);

        if (is_null($tempModel) || !($tempModel instanceof Ess_M2ePro_Model_Abstract)) {
            return array();
        }

        return $this->getRelatedItems($tempModel,$fieldName,$asObjects,$filters,$sort);
    }

    // ########################################

    protected function getComponentMode()
    {
        return NULL;
    }

    // ########################################
}