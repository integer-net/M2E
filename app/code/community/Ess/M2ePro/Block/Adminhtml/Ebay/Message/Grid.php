<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Message_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMessageGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Ebay_Message')->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('ma' => Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                '(`ma`.`id` = `main_table`.`account_id`)',
                array('account_mode'=>'mode')
            )
            ->columns(array('have_responses' => new Zend_Db_Expr('if(`main_table`.`message_responses` = "", 0, 1)')));

        if ($accountId = $this->getRequest()->getParam('ebayAccount')) {
            $collection->addFieldToFilter('account_id', $accountId);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    if ($field == 'have_responses') {
                        if ((int)$cond['eq'] == 0) {
                            $this->getCollection()->getSelect()->where('`main_table`.`message_responses` = \'\'');
                        } else if ((int)$cond['eq'] == 1) {
                            $this->getCollection()->getSelect()->where('`main_table`.`message_responses` != \'\'');
                        }
                    } else {
                        $this->getCollection()->addFieldToFilter($field, $cond);
                    }
                }
            }
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sender_name', array(
            'header' => Mage::helper('M2ePro')->__('Sender'),
            'align'  => 'left',
            'type'   => 'text',
            'width'  => '100px',
            'index'  => 'sender_name',
            'frame_callback' => array($this, 'callbackColumnSenderName')
        ));

        $this->addColumn('ebay_item_id', array(
            'header' => Mage::helper('M2ePro')->__('Item ID'),
            'align'  => 'left',
            'type'   => 'text',
            'width'  => '90px',
            'index'  => 'ebay_item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('ebay_item_title', array(
            'header' => Mage::helper('M2ePro')->__('Item Title'),
            'align'  => 'left',
            'type'   => 'text',
            'width'  => '185px',
            'index'  => 'ebay_item_title'
        ));

        $this->addColumn('message', array(
            'header'   => Mage::helper('M2ePro')->__('Message'),
            'align'    => 'left',
            'type'     => 'text',
            'sortable' => false,
            'width'    => '320px',
            'frame_callback' => array($this, 'callbackColumnMessage')
        ));

        $this->addColumn('message_date', array(
            'header' => Mage::helper('M2ePro')->__('Sender Message Date'),
            'align'  => 'left',
            'type'   => 'datetime',
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'width'  => '155px',
            'index'  => 'message_date'
        ));

        $this->addColumn('message_responses', array(
            'header'       => Mage::helper('M2ePro')->__('My Responses'),
            'align'        => 'left',
            'type'         => 'options',
            'filter_index' => 'have_responses',
            'sortable'     => false,
            'options'      => array(
                0 => Mage::helper('M2ePro')->__('Unanswered Messages'),
                1 => Mage::helper('M2ePro')->__('Answered Messages')
            ),
            'frame_callback' => array($this, 'callbackColumnResponses')
        ));
    }

    // ####################################

    public function callbackColumnSenderName($value, $row, $column, $isExport)
    {
        $url = Mage::helper('M2ePro/Component_Ebay')->getMemberUrl($value,$row->getData('account_mode'));
        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('product_id'))) {
            $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl($value,$row->getData('account_mode'));
            return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
        } else {
            $url = $this->getUrl('*/adminhtml_listing/goToEbay/', array('item_id' => $row->getData('product_id')));
            return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
        }
    }

    public function callbackColumnMessage($value, $row, $column, $isExport)
    {
        $messageHtml = '<div><label><b>Subject: </b>'.$row->getData('message_subject').'</label></div>';

        $messageText = $row->getData('message_text');
        if (strlen($messageText) > 135) {
            $messageText = substr($messageText, 0, 135);
            $messageText .= ' <a href="javascript:void(0);" onclick="EbayMessageHandlerObj.showMessageText(\''
                            .$row->getId().'\')">'.Mage::helper('M2ePro')->__('Read More').'</a>';
        }

        $messageHtml .= '<div><label><b>'.Mage::helper('M2ePro')->__('Text').': </b>'.$messageText.'</label></div>';

        return $messageHtml;
    }

    public function callbackColumnResponses($value, $row, $column, $isExport)
    {
        $responses = (array)json_decode($row->getData('message_responses'), true);

        $responseHtml = '';
        foreach ($responses as $key => $response) {
            $responseHtml .= '<b>'.($key+1).': </b>';
            if (strlen($response) > 60) {
                $responseHtml .= substr($response, 0, 60);
                $responseHtml .= ' <a href="javascript:void(0);" onclick="EbayMessageHandlerObj.showMessageResponse(\''
                                 .$row->getId().'\',\''.$key.'\')">'.Mage::helper('M2ePro')->__('Read More').'</a>';
            } else {
                $responseHtml .= $response;
            }
            $responseHtml .= '<hr />';
        }

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Response'),
                                'onclick' => 'EbayMessageHandlerObj.openMessage(this,\''.$row->getData('id')
                                             .'\',\''
                                             .Mage::helper('M2ePro')->escapeJs($row->getData('message_subject')).'\');',
                                'class' => 'send_response'
                            ) );

        $responseHtml = '<div>'.$responseHtml.$buttonBlock->toHtml().'</div>';

        return $responseHtml;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/messageGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}