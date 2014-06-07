<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Support extends Mage_Adminhtml_Block_Widget_Form_Container
{
    private $referrer = NULL;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('supportContainer');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'support';
        $this->referrer = $this->getRequest()->getParam('referrer');
        //------------------------------

        // Set header text
        //------------------------------
        $m2eProVersion = '(M2E Pro ver. ' . Mage::helper('M2ePro/Module')->getVersion() . ')';
        $m2eProVersion = '<span style="color: #777; font-size: small; font-weight: normal">' .
                            $m2eProVersion .
                         '</span>';
        $this->_headerText = Mage::helper('M2ePro')->__("Support {$m2eProVersion}");
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
        $url = Mage::helper('M2ePro/View_Development')->getPageUrl();
        $this->_addButton('goto_development', array(
            'label'     => 'Control Panel',
            'onclick'   => 'window.location = \''.$url.'\'',
            'class'     => 'button_link development',
            'style'     => 'display: none;'
        ));
        //------------------------------

        //------------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $migrationTable = Mage::getSingleton('core/resource')->getTableName('m2epro_migration_v6');

        $html = $connRead->select()->from($migrationTable,'data')
            ->where('`component` = \'*\'')->where('`group` = \'notes\'')
            ->query()->fetchColumn();

        if (!empty($html)) {
            $url = $this->getUrl('*/adminhtml_support/migrationNotes');
            $this->_addButton('migration_notes', array(
                'label'     => Mage::helper('M2ePro')->__('Migration Notes'),
                'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
            ));
        }
        //------------------------------

        //------------------------------
        if (is_null($this->referrer)) {
            $this->_addButton('goto_docs', array(
                'label' => Mage::helper('M2ePro')->__('Documentation'),
                'class' => 'button_link drop_down button_documentation'
            ));

            //------------------------------

            $this->_addButton('goto_video_tutorials', array(
                'label' => Mage::helper('M2ePro')->__('Video Tutorials'),
                'class' => 'button_link drop_down button_video_tutorial'
            ));
        } else {
            $url = ($this->referrer == Ess_M2ePro_Helper_View_Ebay::NICK)
                        ? Mage::helper('M2ePro/View_Ebay')->getDocumentationUrl()
                            : Mage::helper('M2ePro/View_Common')->getDocumentationUrl();

            $this->_addButton('goto_docs', array(
                'label'     => Mage::helper('M2ePro')->__('Documentation'),
                'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                'class'     => 'button_link'
            ));

            //------------------------------

            $url = ($this->referrer == Ess_M2ePro_Helper_View_Ebay::NICK)
                        ? Mage::helper('M2ePro/View_Ebay')->getVideoTutorialsUrl()
                            : Mage::helper('M2ePro/View_Common')->getVideoTutorialsUrl();

            $this->_addButton('goto_video_tutorials', array(
                'label'     => Mage::helper('M2ePro')->__('Video Tutorials'),
                'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                'class'     => 'button_link'
            ));
        }
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    // ----------------------------------------

    public function getHeaderHtml()
    {
        if (is_null($this->referrer)) {
            $data = array(
                'target_css_class' => 'button_documentation',
                'style' => 'max-height: 120px; overflow: auto; width: 150px;',
                'items' => $this->getDocumentationDropDownItems()
            );

            $dropDownBlockDocumentation = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown', '', $data);

            $data = array(
                'target_css_class' => 'button_video_tutorial',
                'style' => 'max-height: 120px; overflow: auto; width: 150px;',
                'items' => $this->getVideoTutorialDropDownItems()
            );

            $dropDownBlockVideoTutorial = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown', '', $data);

            return parent::getHeaderHtml()
            . $dropDownBlockDocumentation->toHtml()
            . $dropDownBlockVideoTutorial->toHtml();
        }

        return parent::getHeaderHtml();
    }

    // ----------------------------------------

    private function getVideoTutorialDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = Mage::helper('M2ePro/View_Ebay')->getVideoTutorialsUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/View_Common')->getVideoTutorialsUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel(),
            'target' =>'_blank'
        );
        //------------------------------

        return $items;
    }

    // ----------------------------------------

    private function getDocumentationDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = Mage::helper('M2ePro/View_Ebay')->getDocumentationUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/View_Common')->getDocumentationUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel(),
            'target' =>'_blank'
        );
        //------------------------------

        return $items;
    }

    // ########################################
}