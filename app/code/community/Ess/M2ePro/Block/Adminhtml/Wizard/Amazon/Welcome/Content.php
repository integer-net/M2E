<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Amazon_Welcome_Content extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardAmazonWelcomeContent');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/amazon/welcome/content.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $step = $this->helper('M2ePro/Wizard')->getWizard($this->getNick())->getFirstStep();
        $status = Ess_M2ePro_Helper_Wizard::STATUS_ACTIVE;
        $callback = 'function() { setLocation(\''.$this->getUrl('*/adminhtml_wizard_'.$this->getNick()).'\'); }';
        $callback = 'function() { WizardHandlerObj.setStep(\''.$step.'\', '.$callback.'); }';

        $confirmMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__(
'It is strongly recommended to watch 5 min. video tutorial before starting configuration.
Would you like to watch the video?'
            )
        );

        $onClick = <<<JS
if (!isTutorialFinished && confirm('{$confirmMessage}')) {
    return $('tutorial_image_container').simulate('click');
}
WizardHandlerObj.setStatus('{$status}', {$callback})
JS;

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Start Configuration'),
                'onclick' => $onClick,
                'class' => 'start_installation_button'
            ) );
        //------------------------------

        //------------------------------
        $this->setChild('start_wizard_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}