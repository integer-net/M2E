<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Component_Grid_Container extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    abstract protected function getEbayNewUrl();

    abstract protected function getAmazonNewUrl();

    abstract protected function getBuyNewUrl();

    abstract protected function getPlayNewUrl();

    // ########################################

    protected function getAddButtonOnClickAction()
    {
        $components = Mage::helper('M2ePro/Component')->getActiveComponents();
        $action = '';

        if (count($components) == 1) {
            $component = reset($components);
            $action = 'setLocation(\''.$this->getNewUrl($component).'\');';
        }

        return $action;
    }

    // ########################################

    public function _toHtml()
    {
        return $this->getAddButtonJavascript() . parent::_toHtml();
    }

    // ----------------------------------------

    protected function getAddButtonJavascript()
    {
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) < 2) {
            return '';
        }

        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getAddButtonDropDownHtml());

        return <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        $$('.add-button-drop-down')[0].innerHTML += '{$tempDropDownHtml}';
        DropDownObj = new DropDown();
        DropDownObj.prepare($$('.add-button-drop-down')[0]);
    });

</script>
JAVASCRIPT;
    }

    protected function getAddButtonDropDownHtml()
    {
        $activeComponents = Mage::helper('M2ePro/Component')->getActiveComponents();

        $html = '<ul style="display: none;">';
        foreach ($activeComponents as $component) {
            $url = $this->getNewUrl($component);
            $componentHelper = 'Ess_M2ePro_Helper_Component_'.ucfirst($component);

            $html .= '<li href="'.$url.'">'.constant($componentHelper.'::TITLE').'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    // ########################################

    protected function getNewUrl($component)
    {
        $component = ucfirst(strtolower($component));
        $method = "get{$component}NewUrl";

        if (!method_exists($this, $method)) {
            throw new Exception('Method of adding a new entity is not defined.');
        }

        return $this->$method();
    }

    // ########################################
}