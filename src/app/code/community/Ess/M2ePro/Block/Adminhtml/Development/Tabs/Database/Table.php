<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabaseTable');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_development_tabs_database_table';
        //------------------------------

        // Set header text
        //------------------------------
        $table = Mage::helper('M2ePro/Data_Global')->getValue('data_table');
        $this->_headerText = Mage::helper('M2ePro')->__('Manage Table "%s"', $table);
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

        //------------------------------
        $url = Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl();
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'back'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/*/truncateTables',
            array(
                'tables' => Mage::helper('M2ePro/Data_Global')->getValue('data_table')
            )
        );
        $this->_addButton('delete_all', array(
            'label'     => Mage::helper('M2ePro')->__('Truncate Table'),
            'onclick'   => 'deleteConfirm(\'Are you sure?\', \''.$url.'\')',
            'class'     => 'delete_all'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    protected function _toHtml()
    {
        $gridJsObj = 'developmentTable'.Mage::helper('M2ePro/Data_Global')->getValue('data_model').'GridJsObject';

        $javascript = <<<JAVASCRIPT
<script type="text/javascript">

    //-----------------------------

    function deleteTableRow(url)
    {
        if (!confirm('Are you sure?')) {
            return;
        }

        new Ajax.Request(url ,
        {
            method:'get',
            onSuccess: function(transport)
            {
                {$gridJsObj}.reload();
            }
        });
    }

    //-----------------------------

    function mouseOverCell(cell_id)
    {
        if ($(cell_id + '_save_link').getStyle('display') != 'none') {
            return;
        }

        $(cell_id + '_edit_link').show();
        $(cell_id + '_view_link').hide();
        $(cell_id + '_save_link').hide();
    }

    function mouseOutCell(cell_id)
    {
        if ($(cell_id + '_save_link').getStyle('display') != 'none') {
            return;
        }

        $(cell_id + '_edit_link').hide();
        $(cell_id + '_view_link').hide();
        $(cell_id + '_save_link').hide();
    }

    //-----------------------------

    function saveCell(cell_id,url)
    {
        new Ajax.Request( url ,
        {
            method: 'post',
            asynchronous : false,
            parameters : {
                value : $(cell_id + '_edit_input').value
            },
            onSuccess: function(transport)
            {
            console.log({$gridJsObj});
                switchCellToView(cell_id);
                {$gridJsObj}.reload();
            }
        });
    }

    function switchCellToView(cell_id)
    {
        $(cell_id + '_edit_link').show();
        $(cell_id + '_view_link').hide();
        $(cell_id + '_save_link').hide();

        $(cell_id + '_edit_container').hide();
        $(cell_id + '_view_container').show();
    }

    function switchCellToEdit(cell_id)
    {
        $(cell_id + '_edit_link').hide();
        $(cell_id + '_view_link').show();
        $(cell_id + '_save_link').show();

        $(cell_id + '_edit_container').show();
        $(cell_id + '_view_container').hide();
    }

    //-----------------------------

</script>
JAVASCRIPT;

        return $javascript.parent::_toHtml();
    }
}