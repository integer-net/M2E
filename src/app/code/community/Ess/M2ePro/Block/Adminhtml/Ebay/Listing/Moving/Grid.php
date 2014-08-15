<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Moving_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Moving_Grid
{
    // ####################################

    protected function _toHtml()
    {
        $emptyGrid = json_encode(false);

        $warning = '';
        if ($this->getCollection()->getSize() < 1) {
            $warning = Mage::helper('M2ePro')->__(
                'Listings were not found.'
            );
            $emptyGrid = json_encode(true);
            $warning = <<<HTML
<div class="warning-msg" id="empty_grid_warning">
    <div style="margin: 10px 0 10px 35px; font-weight: bold;">$warning</div>
</div>
HTML;
            $warning = Mage::helper('M2ePro')->escapeJS($warning);
        }

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    var warning_msg_block = $('empty_grid_warning');
    warning_msg_block && warning_msg_block.remove();

    if ({$emptyGrid}) {
        $('{$this->getId()}').insert({
            before: '{$warning}'
        });
    }

    $$('#listingMovingGrid div.grid th').each(function(el){
        el.style.padding = '2px 4px';
    });

    $$('#listingMovingGrid div.grid td').each(function(el){
        el.style.padding = '2px 4px';
    });

</script>
JAVASCRIPT;

        return parent::_toHtml() . $javascriptsMain;
    }

    // ####################################

    protected function addAttributeSetFilter($collection)
    {
        return;
    }

    // ####################################
}