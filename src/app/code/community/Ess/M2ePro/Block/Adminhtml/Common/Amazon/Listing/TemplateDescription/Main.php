<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_TemplateDescription_Main
    extends Mage_Adminhtml_Block_Widget_Container
{

    protected $newAsin = false;
    protected $messages = array();
    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return boolean
     */
    public function isNewAsin()
    {
        return $this->newAsin;
    }

    /**
     * @param boolean $newAsin
     */
    public function setNewAsin($newAsin)
    {
        $this->newAsin = $newAsin;
    }

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/template_description/main.phtml');
    }

    public function getWarnings()
    {
        $warnings = '';
        foreach ($this->getMessages() as $message) {
            $warnings .= <<<HTML
<ul class="messages">
    <li class="{$message['type']}-msg">
        <ul>
            <li>{$message['text']}</li>
        </ul>
    </li>
</ul>
HTML;
        }
        return $warnings;
    }

    public function getReasonMessage()
    {
        if ($this->isNewAsin()) {
            return Mage::helper('M2ePro')->__(
                'For all Products the Description Policies can be <i class="underline">Assigned</i> from the list below.
                 <br/><br/><b>Note:</b> List of Description Policies available for assigning depends on the
                 combination of the chosen Products.');
        }
        return Mage::helper('M2ePro')->__('Please select Description Policy.');
    }
}