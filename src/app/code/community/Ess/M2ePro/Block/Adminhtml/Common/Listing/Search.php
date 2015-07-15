<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Search extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingSearch');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_search';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------
    }

    // ########################################

    protected function _toHtml()
    {

        $tabsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search_tabs');
        $hideChannels = '';

        $tabsIds = $tabsBlock->getTabsIds();

        if (count($tabsIds) <= 1) {
            $hideChannels = ' style="display: none"';
        }

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search_help');

        return $helpBlock->toHtml() . <<<HTML
<div class="content-header" {$hideChannels}>
    <table cellspacing="0">
        <tr>
            <td>{$tabsBlock->toHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>
<div id="search_tabs_container"></div>
HTML;

    }

    // ########################################
}