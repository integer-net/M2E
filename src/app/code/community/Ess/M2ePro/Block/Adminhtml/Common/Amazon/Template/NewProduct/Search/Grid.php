<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_NewProduct_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateNewProductSearchGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $data = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $results = new Varien_Data_Collection();
        foreach ($data as $index => $item) {
            $temp = array(
                'id'        => $item['category_id'],
                'title'     => $item['title'],
                'path'      => $item['path'],
                'xsd_hash'  => $item['xsd_hash'],
                'node_hash' => $item['node_hash'],
                'item_types' => json_decode($item['item_types'],true),
                'browsenode_id' => $item['browsenode_id'],
            );

            $results->addItem(new Varien_Object($temp));
        }

        $this->setCollection($results);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Category'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('actions', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'center',
            'type'         => 'text',
            'width'        => '80px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));

    }

    // ####################################

    public function callbackColumnTitle($title, $row, $column, $isExport)
    {
        $categoryInfo = json_encode($row->getData());
        $categoryInfo = Mage::helper('M2ePro')->escapeHtml($categoryInfo);
        $categoryInfo = Mage::helper('M2ePro')->escapeJs($categoryInfo);

        if (strlen($title) > 60) {
            $title = substr($title, 0, 60) . '...';
        }

        $title   = Mage::helper('M2ePro')->escapeHtml($title);
        $path    = str_replace('->',' > ',$row->getData('path'));
        $path    = Mage::helper('M2ePro')->escapeHtml($path);
        $foundIn = Mage::helper('M2ePro')->__('Found In: ');

        $fullPath = $path;
        if (strlen($path) > 135) {
            $path = substr($path, 0, 135) . '...';
        }

        $html = <<<HTML
<div style="margin-left: 3px">
    <a href="javascript:;" onclick="AmazonTemplateNewProductHandlerObj.confirmSearchClick($categoryInfo)">$title</a>
    <br>
    <span style="font-weight: bold;">$foundIn</span>
    &nbsp;
    <span title="$fullPath">$path</span><br>
</div>
HTML;

        return $html;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $categoryInfo = json_encode($row->getData());
        $categoryInfo = Mage::helper('M2ePro')->escapeHtml($categoryInfo);
        $categoryInfo = Mage::helper('M2ePro')->escapeJs($categoryInfo);

        $select = Mage::helper('M2ePro')->__('Select');
        $html = <<<HTML
<a href="javascript:;" onclick="AmazonTemplateNewProductHandlerObj.confirmSearchClick($categoryInfo)">$select</a>
HTML;

        return $html;
    }

    // ####################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#amazonTemplateNewProductSearchGrid div.grid th').each(function(el){
        el.style.padding = '2px 2px';
    });

    $$('#amazonTemplateNewProductSearchGrid div.grid td').each(function(el){
        el.style.padding = '2px 2px';
    });

</script>
JAVASCRIPT;

        return parent::_toHtml() . $javascriptsMain;
    }

    // ####################################

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}