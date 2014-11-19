<?php

    /*
    * @copyright  Copyright (c) 2013 by  ESS-UA.
    */

class Ess_M2ePro_Block_Adminhtml_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    public function getJavaScript()
    {
        $javascript = parent::getJavaScript();

        return $javascript . <<<JAVASCRIPT
window['{$this->getJsObjectName()}'] = {$this->getJsObjectName()};
JAVASCRIPT;

    }
}