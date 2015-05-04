<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Payment_Builder
    extends Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    // ########################################

    public function build(array $data)
    {
        if (empty($data)) {
            return NULL;
        }

        // validate input data
        //------------------------------
        $this->validate($data);
        //------------------------------

        // prepare input data
        //------------------------------
        $data = $this->prepareData($data);
        //------------------------------

        //------------------------------
        $marketplace = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            'Marketplace',
            $data['marketplace_id']
        );
        //------------------------------

        // create template
        //------------------------------
        $template = Mage::getModel('M2ePro/Ebay_Template_Payment');

        if (isset($data['id'])) {
            $template->load($data['id']);
        }

        $template->addData($data);
        $template->save();
        $template->setMarketplace($marketplace);
        //------------------------------

        // remove old services
        //------------------------------
        $services = $template->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }
        //------------------------------

        if (empty($data['services']) || !is_array($data['services'])) {
            return $template;
        }

        // create services
        //------------------------------
        foreach ($data['services'] as $codeName) {
            $this->createService($template->getId(), $codeName);
        }
        //------------------------------

        return $template;
    }

    // ########################################

    protected function validate(array $data)
    {
        //------------------------------
        if (empty($data['marketplace_id'])) {
            throw new LogicException('Marketplace ID is empty.');
        }
        //------------------------------

        parent::validate($data);
    }

    protected function prepareData(array &$data)
    {
        $prepared = parent::prepareData($data);

        //------------------------------
        $prepared['marketplace_id'] = (int)$data['marketplace_id'];
        //------------------------------

        //------------------------------
        if (isset($data['pay_pal_mode'])) {
            $prepared['pay_pal_mode'] = (int)(bool)$data['pay_pal_mode'];
        } else {
            $prepared['pay_pal_mode'] = 0;
        }

        if (isset($data['pay_pal_email_address'])) {
            $prepared['pay_pal_email_address'] = $data['pay_pal_email_address'];
        }

        $prepared['pay_pal_immediate_payment'] = 0;
        if (isset($data['pay_pal_immediate_payment'])) {
            $prepared['pay_pal_immediate_payment'] = (int)(bool)$data['pay_pal_immediate_payment'];
        }

        if (isset($data['services']) && is_array($data['services'])) {
            $prepared['services'] = $data['services'];
        }
        //------------------------------

        return $prepared;
    }

    // ########################################

    private function createService($templatePaymentId, $codeName)
    {
        $data = array(
            'template_payment_id' => $templatePaymentId,
            'code_name' => $codeName
        );

        $model = Mage::getModel('M2ePro/Ebay_Template_Payment_Service');
        $model->addData($data);
        $model->save();

        return $model;
    }

    // ########################################
}