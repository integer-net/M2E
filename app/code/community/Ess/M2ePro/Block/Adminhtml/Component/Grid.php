<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Component_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ########################################

    public function getComponentRowUrl($row, $controller, $action, array $params = array())
    {
        $mode = strtolower($row->getData('component_mode'));
        $action = strtolower($action);

        return $this->getUrl("*/adminhtml_{$mode}_{$controller}/$action", $params);
    }

    protected function getComponentModeFilterOptions()
    {
        $options = array();

        foreach (Mage::helper('M2ePro/Component')->getActiveComponents() as $component) {
            $upperCasedComponent = ucfirst($component);
            $componentLabel = @constant("Ess_M2ePro_Helper_Component_{$upperCasedComponent}::TITLE");

            if (!is_null($componentLabel)) {
                $options[$component] = $componentLabel;
            }
        }

        return $options;
    }

    protected function getFilterOptionsByModel(
        $modelName,
        $idField = 'id',
        $labelField = 'title',
        array $filters = array()
    ) {
        /** @var $helper Ess_M2ePro_Helper_Component */
        $helper = Mage::helper('M2ePro/Component');
        $collection = Mage::getModel('M2ePro/' . $modelName)->getCollection()->setOrder('title', 'ASC');

        // --------------
        $collection->addFieldToFilter('component_mode', array('in' => $helper->getActiveComponents()));

        foreach ($filters as $field => $filter) {
            $collection->addFieldToFilter('`'.$field.'`', $filter);
        }
        // --------------

        // Prepare options and groups
        // --------------
        $optionGroups = $tempOptions = array();

        $options = array();
        foreach ($collection as $item) {
            $options[$item->getData($idField)] = $item->getData($labelField);

            if (count($helper->getActiveComponents()) > 1) {
                $tempOption = array(
                    'value' => $item->getData($idField),
                    'label' => $item->getData($labelField)
                );

                $tempOptions[$item->getComponentMode()][] = $tempOption;
            }
        }

        if (count($helper->getActiveComponents()) > 1) {
            $optionGroups = $this->getComponentFilterGroups($tempOptions);
        }
        // --------------

        return array(
            'options'       => $options,
            'option_groups' => $optionGroups
        );
    }

    protected function getComponentFilterGroups(array $componentOptions = array())
    {
        $groups = array();

        foreach ($componentOptions as $component => $options) {
            $upperCasedComponent = ucfirst($component);
            $componentLabel = @constant("Ess_M2ePro_Helper_Component_{$upperCasedComponent}::TITLE");

            if (!is_null($componentLabel)) {
                $groups[$component] = array(
                    'label' => $componentLabel,
                    'value' => $options
                );
            }
        }

        return $groups;
    }

    // ########################################
}