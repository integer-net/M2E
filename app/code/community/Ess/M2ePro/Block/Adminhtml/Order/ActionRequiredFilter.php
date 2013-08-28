<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Order_ActionRequiredFilter extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/action_required_filter.phtml');
    }

    public function getParamName()
    {
        $component = $this->getData('component_mode');

        if (!$component) {
            throw new LogicException('Component is not set.');
        }

        return "{$component}State";
    }

    public function getFilterUrl()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = array();
        } else {
            $params = $this->getRequest()->getParams();
        }

        $params['tab'] = Ess_M2ePro_Block_Adminhtml_Component_Abstract::getTabIdByComponent(
            $this->getData('component_mode')
        );

        if ($this->isChecked()) {
            unset($params[$this->getParamName()]);
        } else {
            $params[$this->getParamName()] = Ess_M2ePro_Model_Order_Item::STATE_ACTION_REQUIRED;
        }

        return $this->getUrl('*/'.$this->getData('controller').'/*', $params);
    }

    public function isChecked()
    {
        return $this->getRequest()->getParam($this->getParamName());
    }
}